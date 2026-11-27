<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pengajuan;
use App\Models\RiwayatApproval;
use App\Services\DigitalSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    // Tahap 1: Verifikasi kelengkapan berkas oleh Verifikator (role: verifikator).
    public function verifikasi(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('operator'), 403, 'Hanya Operator yang berwenang memverifikasi berkas.');
        abort_unless($pengajuan->status === 'menunggu_verifikasi', 400, 'Status berkas tidak dalam antrean verifikasi.');

        $data = $request->validate([
            'keputusan' => ['required', 'in:teruskan,revisi'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['keputusan'] === 'revisi' && blank($data['catatan'])) {
            return back()->withErrors(['catatan' => 'Catatan revisi wajib diisi apabila mengembalikan berkas kepada pegawai.']);
        }

        DB::transaction(function () use ($pengajuan, $data, $request) {
            $lanjut = $data['keputusan'] === 'teruskan';
            $pengajuan->update([
                'status' => $lanjut ? 'menunggu_approval_tier1' : 'perlu_revisi',
                'catatan' => $data['catatan'] ?? $pengajuan->catatan,
            ]);

            RiwayatApproval::create([
                'pengajuan_id' => $pengajuan->id,
                'pejabat_id' => $request->user()->id,
                'tahap' => 'verifikasi',
                'keputusan' => $data['keputusan'],
                'catatan' => $data['catatan'] ?? null,
                'waktu' => now(),
            ]);

            AuditLog::catat(
                $request->user()->id,
                'verifikasi_pengajuan',
                'pengajuan',
                $pengajuan->id,
                "Verifikasi {$pengajuan->nomor_ajuan}: keputusan {$data['keputusan']}"
            );
        });

        return back()->with('success', $data['keputusan'] === 'teruskan' 
            ? 'Berkas berhasil diverifikasi dan diteruskan ke Kasi/Kasubag.' 
            : 'Berkas berhasil dikembalikan kepada pegawai untuk diperbaiki.');
    }

    // Tahap 2: Persetujuan tingkat pertama — Kasi / Kasubag (role: kasi_kasubag).
    public function tier1(Request $request, Pengajuan $pengajuan)
    {
        return $this->prosesApproval(
            $request, 
            $pengajuan, 
            'verifikator_pimpinan',
            'menunggu_approval_tier1', 
            'approval_tier1', 
            'menunggu_approval_final', 
            'ditolak_tier1',
            'Persetujuan Tingkat Pertama (Kasi/Kasubag)'
        );
    }

    // Tahap 3: Persetujuan akhir & Pengesahan Tanda Tangan Digital — Camat / Lurah (role: camat_lurah).
    public function final(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('pimpinan'), 403, 'Hanya Camat/Lurah yang berwenang memberikan persetujuan akhir & tanda tangan digital.');
        abort_unless($pengajuan->status === 'menunggu_approval_final', 400, 'Status pengajuan tidak dalam antrean persetujuan Camat/Lurah.');

        // Pejabat tidak boleh menyetujui pengajuan miliknya sendiri.
        abort_if($pengajuan->pegawai_id === $request->user()->id, 403, 'Tidak dapat menyetujui pengajuan sendiri.');

        // Pejabat Kelurahan hanya dapat menyetujui pegawai di unit kelurahannya
        if ($request->user()->unitKerja && $request->user()->unitKerja->tipe === 'kelurahan') {
            abort_unless($pengajuan->unit_kerja_id === $request->user()->unit_kerja_id, 403, 
                'Anda hanya berwenang menyetujui pengajuan pegawai dari kelurahan Anda.');
        }

        $data = $request->validate([
            'keputusan' => ['required', 'in:setuju,tolak'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['keputusan'] === 'tolak' && blank($data['catatan'])) {
            return back()->withErrors(['catatan' => 'Alasan penolakan wajib dicantumkan jika pengajuan ditolak.']);
        }

        $user = $request->user();

        DB::transaction(function () use ($pengajuan, $data, $user, $request) {
            if ($data['keputusan'] === 'setuju') {
                // Generate token & payload Tanda Tangan Digital Terverifikasi
                $signedAt = now()->toImmutable();
                $signatureToken = app(DigitalSignatureService::class)->createToken($pengajuan, $user, $signedAt);

                $signaturePayload = [
                    'penandatangan' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'nip' => $user->nip,
                        'jabatan' => $user->jabatan ?? ($user->unitKerja->nama ?? 'Camat/Lurah'),
                        'unit_kerja' => $user->unitKerja->nama ?? 'Pemerintah Kecamatan Tamalate',
                    ],
                    'dokumen' => [
                        'nomor_ajuan' => $pengajuan->nomor_ajuan,
                        'jenis_layanan' => $pengajuan->jenis_layanan,
                        'pemohon_nama' => $pengajuan->pegawai->nama ?? '-',
                        'pemohon_nip' => $pengajuan->pegawai->nip ?? '-',
                        'tanggal_ajuan' => optional($pengajuan->tanggal_ajuan)->format('Y-m-d'),
                        'hash_digest' => hash('sha256', json_encode($pengajuan->data_form)),
                    ],
                    'keamanan' => [
                        'token' => $signatureToken,
                        'waktu_utc' => $signedAt->toIso8601String(),
                        'waktu_wita' => $signedAt->timezone('Asia/Makassar')->format('d/m/Y H:i:s') . ' WITA',
                        'metode' => 'Tanda Tangan Elektronik Tersertifikasi SIKESI Tamalate (BSrE Ready)',
                        'algoritma' => 'HMAC-SHA256',
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 200),
                    ],
                ];

                $pengajuan->update([
                    'status' => 'disetujui_dan_ditandatangani',
                    'catatan' => $data['catatan'] ?? $pengajuan->catatan,
                    'tanda_tangan_pejabat_id' => $user->id,
                    'tanda_tangan_waktu' => $signedAt,
                    'tanda_tangan_token' => $signatureToken,
                    'tanda_tangan_payload' => $signaturePayload,
                ]);

                RiwayatApproval::create([
                    'pengajuan_id' => $pengajuan->id,
                    'pejabat_id' => $user->id,
                    'tahap' => 'approval_final',
                    'keputusan' => 'setuju',
                    'catatan' => $data['catatan'] ?? 'Disetujui dan ditandatangani digital oleh Camat/Lurah',
                    'waktu' => now(),
                ]);

                AuditLog::catat(
                    $user->id,
                    'tanda_tangan_digital',
                    'pengajuan',
                    $pengajuan->id,
                    "Camat/Lurah ({$user->nama}) menyetujui & menandatangani secara digital pengajuan {$pengajuan->nomor_ajuan} (Token: {$signatureToken}). Berkas diteruskan ke Super Admin untuk proses administrasi & distribusi."
                );
            } else {
                // Penolakan oleh Camat/Lurah
                $pengajuan->update([
                    'status' => 'ditolak_final',
                    'catatan' => $data['catatan'],
                ]);

                RiwayatApproval::create([
                    'pengajuan_id' => $pengajuan->id,
                    'pejabat_id' => $user->id,
                    'tahap' => 'approval_final',
                    'keputusan' => 'tolak',
                    'catatan' => $data['catatan'],
                    'waktu' => now(),
                ]);

                AuditLog::catat(
                    $user->id,
                    'penolakan_final',
                    'pengajuan',
                    $pengajuan->id,
                    "Camat/Lurah ({$user->nama}) menolak pengajuan {$pengajuan->nomor_ajuan}. Alasan dicatat dan diteruskan ke Super Admin: {$data['catatan']}"
                );
            }
        });

        $pesan = $data['keputusan'] === 'setuju'
            ? 'Pengajuan berhasil disetujui & ditandatangani secara digital oleh Camat/Lurah. Berkas diteruskan ke Super Admin untuk diproses dan didistribusikan kepada pegawai.'
            : 'Pengajuan ditolak. Catatan penolakan telah dicatat dan diteruskan ke Super Admin untuk ditindaklanjuti.';

        return back()->with('success', $pesan);
    }

    private function prosesApproval(Request $request, Pengajuan $pengajuan, string $role,
        string $statusAwal, string $tahap, string $statusSetuju, string $statusTolak, string $labelTahap)
    {
        abort_unless($request->user()->hasRole($role), 403, "Hanya {$role} yang berwenang memproses {$labelTahap}.");
        abort_unless($pengajuan->status === $statusAwal, 400, 'Status pengajuan tidak sesuai dengan tahapan persetujuan ini.');

        // Pejabat tidak boleh menyetujui pengajuan miliknya sendiri.
        abort_if($pengajuan->pegawai_id === $request->user()->id, 403, 'Tidak dapat menyetujui pengajuan sendiri.');

        // Pejabat Kelurahan hanya dapat menyetujui pegawai di unit kelurahannya
        if ($request->user()->unitKerja && $request->user()->unitKerja->tipe === 'kelurahan') {
            abort_unless($pengajuan->unit_kerja_id === $request->user()->unit_kerja_id, 403, 
                'Anda hanya berwenang menyetujui pengajuan pegawai dari kelurahan Anda.');
        }

        $data = $request->validate([
            'keputusan' => ['required', 'in:setuju,tolak'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['keputusan'] === 'tolak' && blank($data['catatan'])) {
            return back()->withErrors(['catatan' => 'Alasan penolakan wajib dicantumkan jika pengajuan ditolak.']);
        }

        DB::transaction(function () use ($pengajuan, $data, $request, $tahap, $statusSetuju, $statusTolak, $labelTahap) {
            $statusBaru = $data['keputusan'] === 'setuju' ? $statusSetuju : $statusTolak;
            $pengajuan->update([
                'status' => $statusBaru,
                'catatan' => $data['catatan'] ?? $pengajuan->catatan,
            ]);

            RiwayatApproval::create([
                'pengajuan_id' => $pengajuan->id,
                'pejabat_id' => $request->user()->id,
                'tahap' => $tahap,
                'keputusan' => $data['keputusan'],
                'catatan' => $data['catatan'] ?? null,
                'waktu' => now(),
            ]);

            AuditLog::catat(
                $request->user()->id,
                "approval_{$tahap}",
                'pengajuan',
                $pengajuan->id,
                "{$labelTahap} untuk {$pengajuan->nomor_ajuan}: {$data['keputusan']} (status baru: {$statusBaru})"
            );
        });

        $pesan = $data['keputusan'] === 'setuju'
            ? 'Pengajuan tingkat pertama disetujui dan diteruskan ke Camat/Lurah.'
            : 'Pengajuan ditolak pada tingkat pertama. Catatan penolakan diteruskan ke Super Admin untuk ditindaklanjuti.';

        return back()->with('success', $pesan);
    }
}
