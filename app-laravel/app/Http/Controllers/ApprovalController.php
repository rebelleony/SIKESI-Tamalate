<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\RiwayatApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    // Tahap 1: verifikasi kelengkapan berkas oleh Pelaksana/Kasubag (role verifikator).
    public function verifikasi(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('verifikator', 'super_admin'), 403);
        abort_unless($pengajuan->status === 'menunggu_verifikasi', 400, 'Status tidak sesuai.');

        $data = $request->validate([
            'keputusan' => ['required', 'in:teruskan,revisi'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

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
        });

        return back()->with('success', 'Berkas berhasil diproses.');
    }

    // Tahap 2: persetujuan tingkat pertama — Kasi / Kasubag Umum & Kepegawaian.
    public function tier1(Request $request, Pengajuan $pengajuan)
    {
        return $this->prosesApproval($request, $pengajuan, 'kasi_kasubag',
            'menunggu_approval_tier1', 'approval_tier1', 'menunggu_approval_tier2', 'ditolak_tier1');
    }

    // Tahap 3: persetujuan tingkat kedua — Sekretaris Camat (verifikasi & paraf draf).
    public function tier2(Request $request, Pengajuan $pengajuan)
    {
        return $this->prosesApproval($request, $pengajuan, 'seksam',
            'menunggu_approval_tier2', 'approval_tier2', 'menunggu_approval_final', 'ditolak_tier2');
    }

    // Tahap 4: persetujuan akhir — Camat / Lurah (validasi & tanda tangan).
    public function final(Request $request, Pengajuan $pengajuan)
    {
        return $this->prosesApproval($request, $pengajuan, 'camat_lurah',
            'menunggu_approval_final', 'approval_final', 'menunggu_pengiriman', 'ditolak_final');
    }

    // Tahap 5: registrasi, penomoran, stempel, kirim ke BKPSDMD oleh Pelaksana/staf.
    public function kirim(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('verifikator', 'super_admin'), 403);
        abort_unless($pengajuan->status === 'menunggu_pengiriman', 400, 'Status tidak sesuai.');

        $data = $request->validate([
            'nomor_agenda' => ['nullable', 'string', 'max:60'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($pengajuan, $data, $request) {
            $pengajuan->update([
                'status' => 'dikirim_bkpsdm',
                'nomor_agenda' => $data['nomor_agenda'] ?? $pengajuan->nomor_agenda,
            ]);

            RiwayatApproval::create([
                'pengajuan_id' => $pengajuan->id,
                'pejabat_id' => $request->user()->id,
                'tahap' => 'pengiriman',
                'keputusan' => 'dikirim',
                'catatan' => $data['catatan'] ?? null,
                'waktu' => now(),
            ]);
        });

        return back()->with('success', 'Usulan ditandai telah dikirim ke BKPSDMD.');
    }

    private function prosesApproval(Request $request, Pengajuan $pengajuan, string $role,
        string $statusAwal, string $tahap, string $statusSetuju, string $statusTolak)
    {
        abort_unless($request->user()->hasRole($role, 'super_admin'), 403);
        abort_unless($pengajuan->status === $statusAwal, 400, 'Status tidak sesuai.');

        // Pejabat tidak boleh menyetujui pengajuan miliknya sendiri.
        abort_if($pengajuan->pegawai_id === $request->user()->id, 403, 'Tidak dapat menyetujui pengajuan sendiri.');

        $data = $request->validate([
            'keputusan' => ['required', 'in:setuju,tolak'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($pengajuan, $data, $request, $tahap, $statusSetuju, $statusTolak) {
            $pengajuan->update([
                'status' => $data['keputusan'] === 'setuju' ? $statusSetuju : $statusTolak,
            ]);

            RiwayatApproval::create([
                'pengajuan_id' => $pengajuan->id,
                'pejabat_id' => $request->user()->id,
                'tahap' => $tahap,
                'keputusan' => $data['keputusan'],
                'catatan' => $data['catatan'] ?? null,
                'waktu' => now(),
            ]);
        });

        return back()->with('success', 'Keputusan persetujuan tersimpan.');
    }
}
