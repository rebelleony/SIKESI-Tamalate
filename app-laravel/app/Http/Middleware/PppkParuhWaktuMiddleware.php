<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * PppkParuhWaktuMiddleware — SIP-TAMALATE
 *
 * Membatasi pegawai PPPK_PARUH_WAKTU pada alur pengajuan cuti:
 *   - Pengajuan KGB (Kenaikan Gaji Berkala)
 *   - Pengajuan Kenaikan Pangkat
 *   - Pengajuan Pensiun
 *
 * PPPK Paruh Waktu HANYA boleh mengakses:
 *   ✅ Presensi Apel
 *   ✅ Pengajuan cuti dan tracking pengajuan
 *   ✅ Profil
 */
class PppkParuhWaktuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isPppkParuhWaktu()) {
            $allowedRoutes = [
                'kepegawaian.index',
                'kepegawaian.create',
                'kepegawaian.store',
                'kepegawaian.show',
                'kepegawaian.form-cuti',
                'kepegawaian.lampiran.unduh',
                'kepegawaian.revisi',
            ];

            abort_unless(
                in_array($request->route()?->getName(), $allowedRoutes, true),
                403,
                'PPPK Paruh Waktu hanya dapat mengakses pengajuan cuti.'
            );
        }

        return $next($request);
    }
}

