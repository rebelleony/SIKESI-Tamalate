<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PengajuanSuratMandiri extends Model
{
    protected $table = 'pengajuan_surat_mandiri';

    protected $fillable = [
        'kode_tracking',
        'jenis_surat',
        'nama_pemohon',
        'nip_nik',
        'unit_asal',
        'no_hp',
        'perihal',
        'keterangan',
        'file_pendukung',
        'status',
        'catatan_admin',
        'surat_keluar_id',
        'user_id',
        'diproses_oleh',
        'waktu_diproses',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_diproses' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public const JENIS_SURAT = [
        'Surat Pengantar',
        'Undangan',
        'Permohonan Fasilitasi',
    ];

    public const STATUS = [
        'diajukan'          => 'Diajukan (Menunggu Operator)',
        'diproses_operator' => 'Sedang Diproses Operator',
        'menunggu_pimpinan' => 'Menunggu Tanda Tangan Pimpinan',
        'selesai'           => 'Selesai / Siap Diambil',
        'ditolak'           => 'Ditolak',
    ];

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_keluar_id');
    }

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public static function generateKodeTracking(): string
    {
        do {
            $kode = 'TRK-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (static::where('kode_tracking', $kode)->exists());

        return $kode;
    }
}

