<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\PresensiApel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('pegawai')) {
            $data = [
                'pengajuanSaya' => Pengajuan::with('unitKerja')
                    ->where('pegawai_id', $user->id)->latest()->take(5)->get(),
                'presensiHariIni' => PresensiApel::where('pegawai_id', $user->id)
                    ->whereDate('tanggal', today())->first(),
            ];
            return view('dashboard.pegawai', $data);
        }

        if ($user->hasRole('verifikator')) {
            $data = ['antrean' => Pengajuan::with('pegawai')->where('status', 'menunggu_verifikasi')->latest()->get()];
            return view('dashboard.verifikator', $data);
        }

        if ($user->hasRole('kasi_kasubag')) {
            $data = ['antrean' => Pengajuan::with('pegawai')->where('status', 'menunggu_approval_tier1')->latest()->get()];
            return view('dashboard.approver', $data);
        }

        if ($user->hasRole('seksam')) {
            $data = ['antrean' => Pengajuan::with('pegawai')->where('status', 'menunggu_approval_tier2')->latest()->get()];
            return view('dashboard.approver', $data);
        }

        if ($user->hasRole('camat_lurah')) {
            $data = ['antrean' => Pengajuan::with('pegawai')->where('status', 'menunggu_approval_final')->latest()->get()];
            return view('dashboard.approver', $data);
        }

        $data = [
            'totalPegawai' => \App\Models\User::where('status', 'aktif')->count(),
            'totalPengajuan' => Pengajuan::count(),
            'menungguVerifikasi' => Pengajuan::where('status', 'menunggu_verifikasi')->count(),
            'disetujui' => Pengajuan::whereIn('status', Pengajuan::STATUS_SELESAI)->count(),
        ];
        return view('dashboard.admin', $data);
    }
}
