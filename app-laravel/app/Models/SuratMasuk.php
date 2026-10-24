<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $fillable = [
        'nomor_surat', 'nomor_agenda', 'klasifikasi', 'tanggal_surat', 'tanggal_terima',
        'asal_surat', 'tujuan', 'perihal', 'sifat', 'file_path', 'disposisi',
        'status', 'tujuan_disposisi',
        'paraf_oleh', 'paraf_waktu', 'disposisi_oleh', 'disposisi_waktu',
        'distribusi_oleh', 'distribusi_waktu', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_terima' => 'date',
        'paraf_waktu' => 'datetime',
        'disposisi_waktu' => 'datetime',
        'distribusi_waktu' => 'datetime',
    ];

    // Alur penanganan surat masuk sesuai SOP mitra.
    public const STATUS = [
        'dicatat' => 'Dicatat dalam Agenda',
        'diparaf_sekcam' => 'Diperiksa & Diparaf Sekretaris Camat',
        'didisposisi' => 'Didisposisi Camat',
        'didistribusikan' => 'Didistribusikan',
        'diarsipkan' => 'Diarsipkan',
    ];

    public const SIFAT = ['biasa', 'penting', 'segera', 'rahasia'];

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
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

    public function arsip()
    {
        return $this->hasOne(Arsip::class, 'surat_masuk_id');
    }
}
