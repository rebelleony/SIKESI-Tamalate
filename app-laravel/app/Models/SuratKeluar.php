<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluar';

    protected $fillable = [
        'no_urut',
        'tahun',
        'kode_klasifikasi_id',
        'nomor_surat',
        'instansi_tujuan',
        'tgl_surat',
        'perihal',
        'penanggung_jawab',
        'keterangan',
        'file_path',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
    ];

    public const BULAN_ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function kodeKlasifikasi()
    {
        return $this->belongsTo(KodeKlasifikasi::class, 'kode_klasifikasi_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public static function nextNoUrut(int $tahun): int
    {
        $max = static::where('tahun', $tahun)->max('no_urut');

        return ($max ?? 0) + 1;
    }

    public static function formatNomorSurat(string $kodeKlasifikasi, int $noUrut, Carbon|string $tglSurat): string
    {
        $date = $tglSurat instanceof Carbon ? $tglSurat : Carbon::parse($tglSurat);
        $romawi = self::BULAN_ROMAWI[(int) $date->month] ?? 'I';

        return "{$kodeKlasifikasi}/{$noUrut}/KT/{$romawi}/{$date->year}";
    }
}

