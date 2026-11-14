<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeKlasifikasi extends Model
{
    protected $table = 'kode_klasifikasi';

    protected $fillable = ['kode', 'nama', 'induk_kode', 'keterangan'];

    public function suratMasuk()
    {
        return $this->hasMany(SuratMasuk::class, 'kode_klasifikasi_id');
    }

    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class, 'kode_klasifikasi_id');
    }

    public function labelLengkap(): string
    {
        return "{$this->kode} — {$this->nama}";
    }
}

