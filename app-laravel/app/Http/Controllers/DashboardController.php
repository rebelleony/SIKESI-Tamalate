<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pengajuan;
use App\Models\PengajuanKepegawaian;
use App\Models\PengajuanSuratMandiri;
use App\Models\PresensiApel;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Role: Pegawai / Staf
        if ($user->hasRole('pegawai')) {
            $pengajuanSop = Pengajuan::with('unitKerja')
                ->where('pegawai_id', $user->id)->latest()->get();
            $pengajuanKepegawaian = PengajuanKepegawaian::with('unitKerja')
                ->where('pegawai_id', $user->id)->latest()->get();
            $suratMandiri = PengajuanSuratMandiri::where('user_id', $user->id)
                ->latest()->get();

            $pengajuanTerbaru = collect()
                ->merge($pengajuanSop->map(fn ($p) => [
                    'nomor'        => $p->nomor_ajuan ?? '-',
                    'layanan'      => ucfirst(str_replace('_', ' ', $p->jenis_layanan)),
                    'sumber'       => 'Pengajuan SOP',
                    'status_label' => $p->labelStatus(),
                    'tanggal'      => optional($p->tanggal_ajuan)->format('d/m/Y') ?? optional($p->created_at)->format('d/m/Y'),
                    'url'          => route('pengajuan.show', $p),
                    'sort_at'      => $p->created_at?->timestamp ?? 0,
                ]))
                ->merge($pengajuanKepegawaian->map(fn ($k) => [
                    'nomor'        => $k->nomor_pengajuan,
                    'layanan'      => $k->labelJenis(),
                    'sumber'       => 'Kepegawaian',
                    'status_label' => $k->labelStatus() . ' (' . $k->meja_saat_ini . ')',
                    'tanggal'      => optional($k->created_at)->format('d/m/Y'),
                    'url'          => route('kepegawaian.show', $k),
                    'sort_at'      => $k->created_at?->timestamp ?? 0,
                ]))
                ->merge($suratMandiri->map(fn ($m) => [
                    'nomor'        => $m->kode_tracking,
                    'layanan'      => $m->jenis_surat . ' — ' . $m->perihal,
                    'sumber'       => 'Surat Mandiri',
                    'status_label' => $m->labelStatus(),
                    'tanggal'      => optional($m->created_at)->format('d/m/Y'),
                    'url'          => route('surat-mandiri.tracking', ['kode' => $m->kode_tracking]),
                    'sort_at'      => $m->created_at?->timestamp ?? 0,
                ]))
                ->sortByDesc('sort_at')
                ->values();

            $data = [
                'pengajuanSaya'      => $pengajuanSop->take(5),
                'pengajuanTerbaru'   => $pengajuanTerbaru->take(10),
                'totalPengajuanSaya' => $pengajuanTerbaru->count(),
                'presensiHariIni'    => PresensiApel::where('pegawai_id', $user->id)
                    ->whereDate('tanggal', today())->get()->keyBy('sesi'),
            ];
            return view('dashboard.pegawai', $data);
        }

        // 2. Role: Operator / Verifikator
        if ($user->hasRole('operator')) {
            $data = [
                'antrean' => Pengajuan::with('pegawai.unitKerja')
                    ->where('status', 'menunggu_verifikasi')->latest()->get(),
                'antreanKepegawaian' => PengajuanKepegawaian::with(['pegawai.unitKerja', 'unitKerja'])
                    ->whereIn('status', ['verifikasi_operator_kelurahan', 'antrean_operator_kecamatan'])
                    ->latest()->get(),
                'antreanSuratMandiri' => PengajuanSuratMandiri::whereIn('status', ['diajukan', 'diproses_operator'])
                    ->latest()->get(),
                'presensiAntrean' => PresensiApel::with('pegawai.unitKerja')
                    ->whereIn('status_validasi', ['pending', 'menunggu'])->latest()->take(10)->get(),
                'totalDiverifikasi' => Pengajuan::whereNotIn('status', ['draf', 'menunggu_verifikasi'])->count()
                    + PengajuanKepegawaian::whereNotIn('status', ['verifikasi_operator_kelurahan', 'antrean_operator_kecamatan'])->count(),
            ];
            return view('dashboard.operator', $data);
        }

        // 3. Role: Kasi / Kasubag / Sekcam (Persetujuan Tahap 1)
        if ($user->hasRole('verifikator_pimpinan')) {
            $data = [
                'tahap' => 'Persetujuan Tingkat Pertama (Verifikator Pimpinan)',
                'antrean' => Pengajuan::with('pegawai.unitKerja')
                    ->where('status', 'menunggu_approval_tier1')->latest()->get(),
                'antreanKepegawaian' => PengajuanKepegawaian::with(['pegawai.unitKerja', 'unitKerja'])
                    ->whereIn('status', ['approval_kasubag', 'approval_sekcam'])
                    ->latest()->get(),
                'antreanSuratMandiri' => collect(),
            ];
            return view('dashboard.approver', $data);
        }

        // 4. Role: Camat / Lurah (Persetujuan Akhir & Tanda Tangan Digital)
        if ($user->hasRole('pimpinan')) {
            $q = Pengajuan::with('pegawai.unitKerja')
                ->where('status', 'menunggu_approval_final');

            $isKelurahan = $user->unitKerja && $user->unitKerja->tipe === 'kelurahan';
            if ($isKelurahan) {
                $q->where('unit_kerja_id', $user->unit_kerja_id);
            }

            $qKep = PengajuanKepegawaian::with(['pegawai.unitKerja', 'unitKerja']);
            if ($isKelurahan) {
                $qKep->where('status', 'approval_lurah')
                    ->where('unit_kerja_id', $user->unit_kerja_id);
            } else {
                $qKep->where('status', 'approval_camat');
            }

            $data = [
                'tahap' => 'Persetujuan Akhir & Tanda Tangan Digital (Pimpinan)',
                'antrean' => $q->latest()->get(),
                'antreanKepegawaian' => $qKep->latest()->get(),
                'antreanSuratMandiri' => PengajuanSuratMandiri::where('status', 'menunggu_pimpinan')
                    ->latest()->get(),
                'totalDitandatangani' => Pengajuan::where('tanda_tangan_pejabat_id', $user->id)->count(),
            ];
            return view('dashboard.approver', $data);
        }

        // 5. Role: Super Admin (Pusat Kendali Data Master & Distribusi Hasil)
        $data = [
            'totalPegawai' => User::count(),
            'pegawaiAktif' => User::where('status', 'aktif')->count(),
            'totalUnit' => UnitKerja::count(),
            'totalPengajuan' => Pengajuan::count() + PengajuanKepegawaian::count(),
            'menungguDistribusi' => Pengajuan::where('status', 'disetujui_dan_ditandatangani')->count(),
            'menungguPenolakan' => Pengajuan::whereIn('status', ['ditolak_tier1', 'ditolak_final'])->count(),
            'totalSelesai' => Pengajuan::whereIn('status', Pengajuan::STATUS_SELESAI)->count()
                + PengajuanKepegawaian::whereIn('status', ['selesai', 'diteruskan_bkpsdm'])->count(),
            'antreanDistribusi' => Pengajuan::with('pegawai.unitKerja', 'tandaTanganPejabat')
                ->where('status', 'disetujui_dan_ditandatangani')->latest('tanda_tangan_waktu')->take(5)->get(),
            'recentLogs' => AuditLog::with('user')->latest('id')->take(8)->get(),
        ];
        return view('dashboard.admin', $data);
    }
}

