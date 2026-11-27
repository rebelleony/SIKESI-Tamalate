<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'nip', 'nama', 'username', 'password_hash', 'role_id', 'unit_kerja_id',
        'unit_kerja_nama', 'atasan_id', 'jabatan', 'tmt_jabatan', 'pangkat_gol',
        'tmt_golongan', 'eselon', 'jenis_pegawai', 'status_pegawai', 'tmt_pegawai',
        'tanggal_lahir', 'tempat_lahir', 'tmt_cpns', 'tmt_pns', 'masa_kerja_tahun',
        'masa_kerja_bulan', 'gaji_pokok', 'jenis_kelamin', 'agama', 'status_perkawinan',
        'pendidikan_awal', 'pendidikan_akhir', 'no_askes', 'npwp', 'no_ktp', 'alamat',
        'no_telp', 'no_hp', 'email', 'foto_profil', 'status',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_jabatan' => 'date',
        'tmt_golongan' => 'date',
        'tmt_pegawai' => 'date',
        'tmt_cpns' => 'date',
        'tmt_pns' => 'date',
        'terkunci_sampai' => 'datetime',
        'last_login' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'pegawai_id');
    }

    public function presensi()
    {
        return $this->hasMany(PresensiApel::class, 'pegawai_id');
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role && in_array($this->role->nama_role, $roles, true);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function berhakLayanan(): bool
    {
        return $this->jenis_pegawai !== 'PPPK_PARUH_WAKTU';
    }

    public function isPppk(): bool
    {
        return in_array($this->jenis_pegawai, ['PPPK_PENUH', 'PPPK_PENUH_WAKTU', 'PPPK_PARUH_WAKTU'], true);
    }

    public function isPppkParuhWaktu(): bool
    {
        return $this->jenis_pegawai === 'PPPK_PARUH_WAKTU';
    }

    public function berhakCuti(): bool
    {
        return $this->jenis_pegawai === null
            || in_array($this->jenis_pegawai, ['PNS', 'PPPK_PENUH', 'PPPK_PENUH_WAKTU', 'PPPK_PARUH_WAKTU'], true);
    }

    public function masaKerjaTeks(): string
    {
        $tahun = (int) $this->masa_kerja_tahun;
        $bulan = (int) $this->masa_kerja_bulan;
        if ($tahun === 0 && $bulan === 0 && $this->masa_kerja_bulan > 0) {
            $tahun = intdiv($this->masa_kerja_bulan, 12);
            $bulan = $this->masa_kerja_bulan % 12;
        }
        return "{$tahun} Tahun {$bulan} Bulan";
    }
}
