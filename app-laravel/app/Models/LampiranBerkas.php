<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LampiranBerkas extends Model
{
    protected $table = 'lampiran_berkas';

    protected $fillable = [
        'pengajuan_kepegawaian_id',
        'nama_dokumen',
        'nama_file_asli',
        'path',
        'ukuran_kb',
        'mime_type',
        'status_verifikasi',
        'catatan_operator',
        'uploaded_by',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanKepegawaian::class, 'pengajuan_kepegawaian_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

