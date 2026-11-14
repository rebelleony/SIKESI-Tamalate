<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $fillable = [
        'no_agenda', 'nomor_surat', 'nomor_agenda', 'klasifikasi', 'kode_klasifikasi_id',
        'tanggal_surat', 'tanggal_terima', 'asal_surat', 'tujuan', 'perihal', 'keterangan',
        'sifat', 'file_path', 'disposisi', 'status', 'tujuan_disposisi', 'tujuan_unit_id',
        'paraf_oleh', 'paraf_waktu', 'paraf_token',
        'disposisi_oleh', 'disposisi_waktu', 'disposisi_token',
        'distribusi_oleh', 'distribusi_waktu',
        'alasan_penolakan', 'ditolak_oleh', 'ditolak_waktu',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_terima' => 'date',
        'paraf_waktu' => 'datetime',
        'disposisi_waktu' => 'datetime',
        'distribusi_waktu' => 'datetime',
        'ditolak_waktu' => 'datetime',
    ];

    // Alur penanganan surat masuk sesuai SOP Pengendalian Surat Masuk.
    public const STATUS = [
        'dicatat'         => 'Dicatat dalam Agenda',
        'diparaf_sekcam'  => 'Diperiksa & Diparaf Sekretaris Camat',
        'didisposisi'     => 'Didisposisi Camat',
        'didistribusikan' => 'Didistribusikan ke Unit Tujuan',
        'diarsipkan'      => 'Diarsipkan',
        'ditolak'         => 'Ditolak',
    ];

    public const SIFAT = ['biasa', 'penting', 'segera', 'rahasia'];

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function kodeKlasifikasi()
    {
        return $this->belongsTo(KodeKlasifikasi::class, 'kode_klasifikasi_id');
    }

    public function tujuanUnit()
    {
        return $this->belongsTo(UnitKerja::class, 'tujuan_unit_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function pemaraf()
    {
        return $this->belongsTo(User::class, 'paraf_oleh');
    }

    public function pendisposisi()
    {
        return $this->belongsTo(User::class, 'disposisi_oleh');
    }

    public function pendistribusi()
    {
        return $this->belongsTo(User::class, 'distribusi_oleh');
    }

    public function penolak()
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public function arsip()
    {
        return $this->hasOne(Arsip::class, 'surat_masuk_id');
    }
}
