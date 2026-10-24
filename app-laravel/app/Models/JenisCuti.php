<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    protected $table = 'jenis_cuti';
    public $timestamps = false;

    protected $fillable = ['nama', 'max_hari', 'keterangan'];
}
