<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiApel extends Model
{
    protected $table = 'presensi_apel';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'user_id',
        'tanggal',
        'sesi',
        'status_kehadiran',
        'jam',
        'waktu_absen',
        'foto_path',
        'foto_apel',
        'status_validasi',
        'validasi_oleh',
        'waktu_validasi',
        'keterangan',
        'created_at',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'waktu_absen'    => 'datetime',
        'waktu_validasi' => 'datetime',
        'created_at'     => 'datetime',
    ];

    public const STATUS_KEHADIRAN = [
        'hadir'            => 'Hadir',
        'izin'             => 'Izin',
        'dinas_luar'       => 'Dinas Luar (DL)',
        'tanpa_keterangan' => 'Tanpa Keterangan (TK)',
    ];

    public const STATUS_VALIDASI = [
        'pending' => 'Menunggu Validasi',
        'valid'   => 'Valid',
        'ditolak' => 'Ditolak',
    ];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validasi_oleh');
    }

    public function fotoUrl(): ?string
    {
        $path = $this->foto_apel ?: $this->foto_path;

        return $path ? asset('storage/' . $path) : null;
    }

    public function isPending(): bool
    {
        return in_array($this->status_validasi, ['pending', 'menunggu'], true);
    }

    public function labelKehadiran(): string
    {
        return self::STATUS_KEHADIRAN[$this->status_kehadiran] ?? ucfirst((string) $this->status_kehadiran);
    }
}
