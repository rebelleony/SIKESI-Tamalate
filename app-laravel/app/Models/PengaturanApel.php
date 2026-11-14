<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanApel extends Model
{
    protected $table = 'pengaturan_apel';

    protected $fillable = [
        'pagi_mulai',
        'pagi_selesai',
        'sore_mulai',
        'sore_selesai',
        'mode_testing',
        'ambang_batas_tk',
    ];

    protected $casts = [
        'mode_testing' => 'boolean',
    ];

    public static function current(): self
    {
        return static::first() ?? new static(config('presensi'));
    }
}

