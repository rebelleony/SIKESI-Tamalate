<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatApproval extends Model
{
    protected $table = 'riwayat_approval';
    public $timestamps = false;

    protected $fillable = ['pengajuan_id', 'pejabat_id', 'tahap', 'keputusan', 'catatan', 'waktu'];

    protected $casts = ['waktu' => 'datetime'];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function pejabat()
    {
        return $this->belongsTo(User::class, 'pejabat_id');
    }
}
