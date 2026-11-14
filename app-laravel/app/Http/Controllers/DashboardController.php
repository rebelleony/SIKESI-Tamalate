<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pengajuan;
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
            $data = [
                'pengajuanSaya' => Pengajuan::with('unitKerja')
                    ->where('pegawai_id', $user->id)->latest()->take(5)->get(),
                'presensiHariIni' => PresensiApel::where('pegawai_id', $user->id)
                    ->whereDate('tanggal', today())->get()->keyBy('sesi'),
            ];
            return view('dashboard.pegawai', $data);
        }

        // 2. Role: Verifikator
        if ($user->hasRole('operator')) {
            $data = [
                'antrean' => Pengajuan::with('pegawai.unitKerja')
                    ->where('status', 'menunggu_verifikasi')->latest()->get(),
                'presensiAntrean' => PresensiApel::with('pegawai.unitKerja')
                    ->where('status_validasi', 'menunggu')->latest()->take(10)->get(),
                'totalDiverifikasi' => Pengajuan::whereNotIn('status', ['draf', 'menunggu_verifikasi'])->count(),
            ];
            return view('dashboard.operator', $data);
        }

        // 3. Role: Kasi / Kasubag (Persetujuan Tahap 1)
        if ($user->hasRole('verifikator_pimpinan')) {
            $data = [
                'tahap' => 'Persetujuan Tingkat Pertama (Verifikator Pimpinan)',
                'antrean' => Pengajuan::with('pegawai.unitKerja')
                    ->where('status', 'menunggu_approval_tier1')->latest()->get(),
            ];
            return view('dashboard.approver', $data);
        }

        // 4. Role: Camat / Lurah (Persetujuan Akhir & Tanda Tangan Digital)
        if ($user->hasRole('pimpinan')) {
            $q = Pengajuan::with('pegawai.unitKerja')
                ->where('status', 'menunggu_approval_final');

            if ($user->unitKerja && $user->unitKerja->tipe === 'kelurahan') {
                $q->where('unit_kerja_id', $user->unit_kerja_id);
            }

            $data = [
                'tahap' => 'Persetujuan Akhir & Tanda Tangan Digital (Pimpinan)',
                'antrean' => $q->latest()->get(),
                'totalDitandatangani' => Pengajuan::where('tanda_tangan_pejabat_id', $user->id)->count(),
            ];
            return view('dashboard.approver', $data);
        }

        // 5. Role: Super Admin (Pusat Kendali Data Master & Distribusi Hasil)
        $data = [
            'totalPegawai' => User::count(),
            'pegawaiAktif' => User::where('status', 'aktif')->count(),
            'totalUnit' => UnitKerja::count(),
            'totalPengajuan' => Pengajuan::count(),
            'menungguDistribusi' => Pengajuan::where('status', 'disetujui_dan_ditandatangani')->count(),
            'menungguPenolakan' => Pengajuan::whereIn('status', ['ditolak_tier1', 'ditolak_final'])->count(),
            'totalSelesai' => Pengajuan::whereIn('status', Pengajuan::STATUS_SELESAI)->count(),
            'antreanDistribusi' => Pengajuan::with('pegawai.unitKerja', 'tandaTanganPejabat')
                ->where('status', 'disetujui_dan_ditandatangani')->latest('tanda_tangan_waktu')->take(5)->get(),
            'recentLogs' => AuditLog::with('user')->latest('id')->take(8)->get(),
        ];
        return view('dashboard.admin', $data);
    }
}

