<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * PppkParuhWaktuMiddleware — SIP-TAMALATE
 *
 * Memblokir pegawai berstatus PPPK_PARUH_WAKTU dari mengakses
 * menu/route yang khusus untuk PNS dan PPPK Penuh Waktu:
 *   - Pengajuan Cuti
 *   - Pengajuan KGB (Kenaikan Gaji Berkala)
 *   - Pengajuan Kenaikan Pangkat
 *   - Pengajuan Pensiun
 *
 * PPPK Paruh Waktu HANYA boleh mengakses:
 *   ✅ Presensi Apel
 *   ✅ Dashboard (tracking status)
 *   ✅ Profil
 */
class PppkParuhWaktuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->jenis_pegawai === 'PPPK_PARUH_WAKTU') {
            abort(403, 'Pegawai PPPK Paruh Waktu tidak memiliki akses ke menu Cuti, KGB, Kenaikan Pangkat, dan Pensiun. Anda hanya dapat mengakses Presensi Apel dan Tracking pengajuan.');
        }

        return $next($request);
    }
}

