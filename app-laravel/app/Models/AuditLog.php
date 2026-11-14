<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $table = 'audit_log';
    protected $fillable = ['user_id', 'aksi', 'entitas', 'entitas_id', 'deskripsi', 'ip_address', 'waktu'];
    protected $casts = ['waktu' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function catat(?int $userId, string $aksi, ?string $entitas = null, ?int $entitasId = null, ?string $deskripsi = null): self
    {
        return self::create([
            'user_id' => $userId,
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'deskripsi' => $deskripsi,
            'ip_address' => request()->ip(),
            'waktu' => now(),
        ]);
    }
}
