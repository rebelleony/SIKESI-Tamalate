<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $table = 'arsip';

    protected $fillable = [
        'surat_masuk_id', 'pengajuan_id', 'jenis_dokumen', 'judul', 'kode_arsip',
        'kategori', 'status_dokumen', 'lokasi', 'nomor_daftar_arsip',
        'ditandatangai_oleh', 'dicatat_oleh',
    ];

    public const STATUS_DOKUMEN = [
        'aktif' => 'Aktif (lemari arsip)',
        'tidak_aktif' => 'Tidak Aktif (gudang arsip)',
        'penting' => 'Penting (disimpan Lurah)',
    ];

    public const LOKASI = [
        'lemari_arsip' => 'Lemari Arsip',
        'gudang_arsip' => 'Gudang Arsip',
        'lurah' => 'Lurah',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function penandatangan()
    {
        return $this->belongsTo(User::class, 'ditandatangai_oleh');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function labelLokasi(): string
    {
        return self::LOKASI[$this->lokasi] ?? $this->lokasi;
    }
}
