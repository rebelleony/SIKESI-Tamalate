<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogStatusPengajuan extends Model
{
    protected $table = 'log_status_pengajuan';

    protected $fillable = [
        'pengajuan_kepegawaian_id',
        'status_dari',
        'status_ke',
        'meja',
        'aktor_id',
        'aksi',
        'catatan',
        'waktu',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanKepegawaian::class, 'pengajuan_kepegawaian_id');
    }

    public function aktor()
    {
        return $this->belongsTo(User::class, 'aktor_id');
    }
}

