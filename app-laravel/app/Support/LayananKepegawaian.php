<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Aturan bisnis layanan kepegawaian (KGB, Kenaikan Pangkat, Pensiun).
 * Menyelaraskan perhitungan dengan ketentuan kepegawaian:
 *  - KGB: kenaikan gaji berkala tiap 24 bulan sejak TMT golongan/pangkat terakhir.
 *  - Kenaikan Pangkat: minimal 24 bulan dalam pangkat/golongan terakhir,
 *    usulan tepat satu tingkat di atas golongan sekarang.
 *  - Pensiun: TMT pensiun = awal bulan setelah pegawai mencapai Batas Usia Pensiun (BUP).
 */
class LayananKepegawaian
{
    public const JENIS = [
        'cuti' => 'Cuti',
        'kgb' => 'Kenaikan Gaji Berkala (KGB)',
        'kenaikan_pangkat' => 'Kenaikan Pangkat',
        'pensiun' => 'Pensiun',
    ];

    public const GOLONGAN = [
        'II/a', 'II/b', 'II/c', 'II/d',
        'III/a', 'III/b', 'III/c', 'III/d',
        'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
    ];

    public const JENIS_PENSIUN = [
        'BUP' => 'Batas Usia Pensiun (BUP)',
        'APS' => 'Atas Permintaan Sendiri (APS)',
    ];

    public static function label(string $jenis): string
    {
        return self::JENIS[$jenis] ?? $jenis;
    }

    /** Aturan validasi input request khusus per jenis layanan. */
    public static function rulesFor(string $jenis): array
    {
        return match ($jenis) {
            'cuti' => [
                'jenis_cuti_id' => ['nullable', 'exists:jenis_cuti,id'],
                'alasan' => ['required', 'string', 'max:255'],
                'lama_hari' => ['required', 'integer', 'min:1'],
                'tanggal_mulai' => ['required', 'date'],
                'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
                'alamat_cuti' => ['required', 'string', 'max:255'],
                'no_telp' => ['required', 'string', 'max:20'],
            ],
            'kgb' => [
                'keterangan' => ['nullable', 'string', 'max:255'],
            ],
            'kenaikan_pangkat' => [
                'pangkat_gol_diusulkan' => ['required', 'string', 'max:30'],
                'tmt_pangkat_diusulkan' => ['required', 'date'],
                'keterangan' => ['nullable', 'string', 'max:255'],
            ],
            'pensiun' => [
                'jenis_pensiun' => ['required', 'in:BUP,APS'],
                'keterangan' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }

    /**
     * Penjagaan aturan bisnis sebelum pengajuan disimpan.
     * Melempar ValidationException bila pegawai belum memenuhi syarat.
     */
    public static function assertEligible(string $jenis, User $u): void
    {
        if (! $u->berhakLayanan()) {
            throw ValidationException::withMessages([
                'jenis_layanan' => 'PPPK Paruh Waktu tidak berhak atas layanan ini.',
            ]);
        }

        if ($jenis === 'kgb' && ! $u->tmt_golongan) {
            throw ValidationException::withMessages([
                'jenis_layanan' => 'Data TMT golongan terakhir belum ada; lengkapi profil pegawai dahulu.',
            ]);
        }

        if ($jenis === 'kenaikan_pangkat') {
            $bulan = self::masaKerjaDalamPangkatBulan($u);
            if ($bulan === null) {
                throw ValidationException::withMessages([
                    'jenis_layanan' => 'Data TMT pangkat/golongan terakhir belum ada; lengkapi profil pegawai dahulu.',
                ]);
            }
            if ($bulan < 24) {
                throw ValidationException::withMessages([
                    'jenis_layanan' => "Belum memenuhi syarat: masa kerja dalam pangkat terakhir baru {$bulan} bulan (minimal 24 bulan).",
                ]);
            }
        }

        if ($jenis === 'pensiun' && ! $u->tanggal_lahir) {
            throw ValidationException::withMessages([
                'jenis_layanan' => 'Data tanggal lahir belum ada; TMT pensiun tidak dapat dihitung.',
            ]);
        }
    }

    /** Menyusun payload data_form per jenis; nilai turunan dihitung server-side. */
    public static function normalize(string $jenis, array $in, User $u): array
    {
        return match ($jenis) {
            'cuti' => [
                'jenis_cuti_id' => $in['jenis_cuti_id'] ?? null,
                'alasan' => $in['alasan'],
                'lama_hari' => $in['lama_hari'],
                'tanggal_mulai' => $in['tanggal_mulai'],
                'tanggal_selesai' => $in['tanggal_selesai'],
                'alamat_cuti' => $in['alamat_cuti'],
                'no_telp' => $in['no_telp'],
            ],
            'kgb' => [
                'golongan_ruang' => $u->pangkat_gol,
                'tmt_golongan_lama' => optional($u->tmt_golongan)->toDateString(),
                'tanggal_kgb_baru' => optional(self::nextKgbDate($u))->toDateString(),
                'masa_kerja' => $u->masaKerjaTeks(),
                'keterangan' => $in['keterangan'] ?? null,
            ],
            'kenaikan_pangkat' => [
                'pangkat_gol_sekarang' => $u->pangkat_gol,
                'tmt_pangkat_sekarang' => optional($u->tmt_golongan)->toDateString(),
                'pangkat_gol_diusulkan' => $in['pangkat_gol_diusulkan'],
                'tmt_pangkat_diusulkan' => $in['tmt_pangkat_diusulkan'],
                'masa_kerja' => $u->masaKerjaTeks(),
                'keterangan' => $in['keterangan'] ?? null,
            ],
            'pensiun' => [
                'jenis_pensiun' => $in['jenis_pensiun'],
                'tanggal_lahir' => optional($u->tanggal_lahir)->toDateString(),
                'bup_tahun' => self::bup($u),
                'tmt_pensiun' => optional(self::tmtPensiun($u))->toDateString(),
                'masa_kerja' => $u->masaKerjaTeks(),
                'keterangan' => $in['keterangan'] ?? null,
            ],
            default => [],
        };
    }

    /** TMT KGB berikutnya = TMT golongan terakhir + 24 bulan. */
    public static function nextKgbDate(User $u): ?Carbon
    {
        return $u->tmt_golongan ? $u->tmt_golongan->copy()->addMonths(24) : null;
    }

    /** Lama (bulan) pegawai berada di pangkat/golongan terakhir. */
    public static function masaKerjaDalamPangkatBulan(User $u): ?int
    {
        if (! $u->tmt_golongan) {
            return null;
        }

        return (int) $u->tmt_golongan->diffInMonths(Carbon::today());
    }

    /** Batas Usia Pensiun: 60 untuk JPT/pimpinan, selain itu 58. */
    public static function bup(User $u): int
    {
        $jabatan = mb_strtolower((string) $u->jabatan);
        $isJpt = str_contains($jabatan, 'camat') || str_contains($jabatan, 'sekretaris')
            || str_contains($jabatan, 'kepala') || str_contains($jabatan, 'lurah');

        return $isJpt ? 60 : 58;
    }

    /** TMT pensiun = tanggal 1 pada bulan setelah pegawai mencapai BUP. */
    public static function tmtPensiun(User $u): ?Carbon
    {
        if (! $u->tanggal_lahir) {
            return null;
        }

        return $u->tanggal_lahir->copy()->addYears(self::bup($u))->addMonth()->startOfMonth();
    }

    /** Golongan tepat satu tingkat di atas golongan sekarang (null bila puncak/tak terbaca). */
    public static function nextGolongan(?string $current): ?string
    {
        if (! $current || ! preg_match('/([IVX]+)\s*\/\s*([a-d])/i', $current, $m)) {
            return null;
        }

        $key = strtoupper($m[1]) . '/' . strtolower($m[2]);
        $idx = array_search($key, self::GOLONGAN, true);

        return ($idx === false || $idx + 1 >= count(self::GOLONGAN)) ? null : self::GOLONGAN[$idx + 1];
    }
}
