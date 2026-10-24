<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';

    protected $fillable = ['nama', 'tipe', 'induk_id'];

    public function induk()
    {
        return $this->belongsTo(UnitKerja::class, 'induk_id');
    }

    public function anak()
    {
        return $this->hasMany(UnitKerja::class, 'induk_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_kerja_id');
    }
}
