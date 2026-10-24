<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $subbag = UnitKerja::where('tipe', 'subbag')->first();
        $roleId = fn (string $r) => Role::where('nama_role', $r)->value('id');

        $camat = User::firstOrCreate(['username' => 'camat'], [
            'nip' => '198804152007011001',
            'nama' => 'Camat (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('camat_lurah'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan' => 'Camat Tamalate',
            'pangkat_gol' => 'Pembina Tk. I, IV/b',
            'jenis_pegawai' => 'PNS',
            'status' => 'aktif',
        ]);

        $seksam = User::firstOrCreate(['username' => 'seksam'], [
            'nip' => '197612102003121002',
            'nama' => 'Sekretaris Kecamatan (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('seksam'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id' => $camat->id,
            'jabatan' => 'Sekretaris Camat',
            'jenis_pegawai' => 'PNS',
            'status' => 'aktif',
        ]);

        $kasi = User::firstOrCreate(['username' => 'kasi'], [
            'nip' => '198301282001122001',
            'nama' => 'Kepala Seksi (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('kasi_kasubag'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id' => $seksam->id,
            'jabatan' => 'Kepala Seksi Pemerintahan',
            'jenis_pegawai' => 'PNS',
            'status' => 'aktif',
        ]);

        User::firstOrCreate(['username' => 'verifikator'], [
            'nip' => '199001012015011001',
            'nama' => 'Staf Arsip Kepegawaian (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('verifikator'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan' => 'Staf Arsip',
            'jenis_pegawai' => 'PNS',
            'status' => 'aktif',
        ]);

        User::firstOrCreate(['username' => 'admin'], [
            'nip' => '198501012010011001',
            'nama' => 'Super Admin Sistem',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('super_admin'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan' => 'Administrator',
            'jenis_pegawai' => 'PNS',
            'status' => 'aktif',
        ]);

        User::firstOrCreate(['username' => 'pegawai'], [
            'nip' => '199509112025212056',
            'nama' => 'Pegawai (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId('pegawai'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id' => $kasi->id,
            'jabatan' => 'Operator Layanan Operasional',
            'jenis_pegawai' => 'PNS',
            'pangkat_gol' => 'Penata Muda, III/a',
            'tanggal_lahir' => '1995-09-11',
            'tmt_golongan' => now()->subMonths(30)->toDateString(),
            'masa_kerja_tahun' => 8,
            'masa_kerja_bulan' => 10,
            'status' => 'aktif',
        ]);
    }
}
