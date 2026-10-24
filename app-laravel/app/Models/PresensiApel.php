<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiApel extends Model
{
    protected $table = 'presensi_apel';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id', 'tanggal', 'sesi', 'jam', 'foto_path',
        'status_validasi', 'validasi_oleh', 'waktu_validasi', 'keterangan', 'created_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_validasi' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validasi_oleh');
    }
}
