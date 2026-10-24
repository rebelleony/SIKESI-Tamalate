<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    protected $table = 'berkas';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'nama_file', 'path', 'tipe', 'ukuran',
        'kategori', 'versi', 'uploaded_by', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
