<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class DistribusiPengajuanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'menunggu_distribusi');
        $q = trim((string) $request->query('q'));
        $layanan = $request->query('layanan');

        $query = Pengajuan::with(['pegawai.unitKerja', 'tandaTanganPejabat', 'distribusiAdmin'])
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nomor_ajuan', 'like', "%{$q}%")
                      ->orWhereHas('pegawai', function ($p) use ($q) {
                          $p->where('nama', 'like', "%{$q}%")
                            ->orWhere('nip', 'like', "%{$q}%");
                      });
                });
            })
            ->when($layanan, fn ($b) => $b->where('jenis_layanan', $layanan));

        $counts = [
            'menunggu_distribusi' => Pengajuan::where('status', 'disetujui_dan_ditandatangani')->count(),
            'penolakan' => Pengajuan::whereIn('status', ['ditolak_tier1', 'ditolak_final'])->count(),
            'selesai' => Pengajuan::whereIn('status', Pengajuan::STATUS_SELESAI)->count(),
        ];

        if ($tab === 'penolakan') {
            $pengajuan = $query->whereIn('status', ['ditolak_tier1', 'ditolak_final'])->latest()->paginate(15);
        } elseif ($tab === 'selesai') {
            $pengajuan = $query->whereIn('status', array_merge(Pengajuan::STATUS_SELESAI, ['selesai_ditolak']))->latest('updated_at')->paginate(15);
        } else {
            // Default: menunggu_distribusi
            $tab = 'menunggu_distribusi';
            $pengajuan = $query->where('status', 'disetujui_dan_ditandatangani')->latest('tanda_tangan_waktu')->paginate(15);
        }

        return view('admin.distribusi.index', [
            'pengajuan' => $pengajuan,
            'tab' => $tab,
            'counts' => $counts,
            'filter' => compact('q', 'layanan'),
        ]);
    }

    public function show(Request $request, Pengajuan $pengajuan)
    {
        $pengajuan->load(['pegawai.unitKerja', 'tandaTanganPejabat', 'distribusiAdmin', 'berkas', 'riwayat.pejabat']);

        return view('admin.distribusi.show', compact('pengajuan'));
    }

    /**
     * Super Admin memverifikasi kelengkapan administrasi dan mendistribusikan hasil pengajuan ke Pegawai/Staf.
     */
    public function distribusikan(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403, 'Hanya Super Admin yang berwenang mendistribusikan hasil pengajuan.');
        abort_unless($pengajuan->status === 'disetujui_dan_ditandatangani', 400, 'Pengajuan belum selesai ditandatangani oleh Camat/Lurah.');
        abort_unless($pengajuan->isDitandatangani(), 400, 'Tanda tangan digital pejabat belum tersemat pada pengajuan ini.');

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $pengajuan->update([
            'status' => 'selesai',
            'distribusi_admin_id' => $user->id,
            'distribusi_waktu' => now(),
            'distribusi_catatan' => $request->catatan,
        ]);

        AuditLog::catat(
            $user->id,
            'distribusi_hasil',
            'pengajuan',
            $pengajuan->id,
            "Super Admin ({$user->nama}) memverifikasi kelengkapan administrasi & tanda tangan digital serta mendistribusikan SK/Surat Izin pengajuan {$pengajuan->nomor_ajuan} kepada pegawai {$pengajuan->pegawai->nama}"
        );

        return redirect()->route('admin.distribusi.index')
            ->with('success', "Hasil pengajuan {$pengajuan->nomor_ajuan} berhasil didistribusikan ke Pegawai ({$pengajuan->pegawai->nama}). Dokumen resmi kini aktif dan dapat diunduh pemohon.");
    }

    /**
     * Super Admin menyampaikan hasil penolakan pejabat kepada Pegawai/Staf tanpa mengubah keputusan pejabat.
     */
    public function sampaikanPenolakan(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403, 'Hanya Super Admin yang berwenang menyampaikan hasil penolakan.');
        abort_unless(in_array($pengajuan->status, ['ditolak_tier1', 'ditolak_final'], true), 400, 'Pengajuan tidak dalam status ditolak pejabat.');

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $pengajuan->update([
            'status' => 'selesai_ditolak',
            'distribusi_admin_id' => $user->id,
            'distribusi_waktu' => now(),
            'distribusi_catatan' => $request->catatan,
        ]);

        AuditLog::catat(
            $user->id,
            'distribusi_penolakan',
            'pengajuan',
            $pengajuan->id,
            "Super Admin ({$user->nama}) menyampaikan hasil penolakan pengajuan {$pengajuan->nomor_ajuan} kepada pegawai {$pengajuan->pegawai->nama} tanpa mengubah keputusan pejabat"
        );

        return redirect()->route('admin.distribusi.index', ['tab' => 'penolakan'])
            ->with('success', "Hasil penolakan pengajuan {$pengajuan->nomor_ajuan} telah disampaikan secara resmi kepada pegawai ({$pengajuan->pegawai->nama}).");
    }

    /**
     * Mengembalikan berkas untuk perbaikan administratif jika ditemukan kekeliruan (prosedur koreksi tercatat).
     */
    public function kembalikanKoreksi(Request $request, Pengajuan $pengajuan)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403, 'Hanya Super Admin yang berwenang melakukan koreksi administratif.');
        abort_unless(in_array($pengajuan->status, ['disetujui_dan_ditandatangani', 'menunggu_approval_final'], true), 400, 'Status pengajuan tidak dapat dikembalikan untuk koreksi saat ini.');

        $data = $request->validate([
            'catatan_koreksi' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $user = $request->user();

        $pengajuan->update([
            'status' => 'perlu_revisi',
            'catatan' => "Koreksi Administratif Super Admin: {$data['catatan_koreksi']}",
            'tanda_tangan_pejabat_id' => null,
            'tanda_tangan_waktu' => null,
            'tanda_tangan_token' => null,
            'tanda_tangan_payload' => null,
            'distribusi_catatan' => "Dikembalikan untuk koreksi oleh Super Admin ({$user->nama}) pada " . now()->format('d/m/Y H:i'),
        ]);

        AuditLog::catat(
            $user->id,
            'koreksi_administratif',
            'pengajuan',
            $pengajuan->id,
            "Super Admin ({$user->nama}) mengembalikan pengajuan {$pengajuan->nomor_ajuan} untuk koreksi administratif: {$data['catatan_koreksi']}"
        );

        return redirect()->route('admin.distribusi.index')
            ->with('success', "Pengajuan {$pengajuan->nomor_ajuan} berhasil dikembalikan untuk perbaikan administratif. Catatan koreksi resmi telah dicatat dalam Audit Log.");
    }
}
