<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DemoUserSeeder — SIP-TAMALATE
 *
 * Role yang digunakan (nama baru):
 *   - super_admin
 *   - operator          (dahulu: verifikator)
 *   - verifikator_pimpinan (dahulu: kasi_kasubag)
 *   - pimpinan          (dahulu: camat_lurah)
 *   - pegawai
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $subbag  = UnitKerja::where('tipe', 'subbag')->first();
        $roleId  = fn (string $r) => Role::where('nama_role', $r)->value('id');

        // ── Pimpinan: Camat ──────────────────────────────────────────────────
        $camat = User::firstOrCreate(['username' => 'camat'], [
            'nip'          => '198804152007011001',
            'nama'         => 'Camat (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('pimpinan'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Camat Tamalate',
            'pangkat_gol'  => 'Pembina Tk. I, IV/b',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // ── Pimpinan: Sekretaris Camat (dahulu role seksam) ─────────────────
        $seksam = User::firstOrCreate(['username' => 'seksam'], [
            'nip'          => '197612102003121002',
            'nama'         => 'Sekretaris Kecamatan (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('pimpinan'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id'    => $camat->id,
            'jabatan'      => 'Sekretaris Camat',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // ── Verifikator Pimpinan: Kepala Seksi ──────────────────────────────
        $kasi = User::firstOrCreate(['username' => 'kasi'], [
            'nip'          => '198301282001122001',
            'nama'         => 'Kepala Seksi (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('verifikator_pimpinan'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id'    => $seksam->id,
            'jabatan'      => 'Kepala Seksi Pemerintahan',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // ── Operator: Staf Arsip Kepegawaian ────────────────────────────────
        User::firstOrCreate(['username' => 'operator'], [
            'nip'          => '199001012015011003',
            'nama'         => 'Staf Arsip Kepegawaian (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('operator'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Staf Arsip',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // Dukung juga username lama 'verifikator' agar tidak pecah saat login
        User::firstOrCreate(['username' => 'verifikator'], [
            'nip'          => '199001012015011001',
            'nama'         => 'Staf Arsip (Alias Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('operator'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Staf Arsip',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // ── Super Admin ──────────────────────────────────────────────────────
        User::firstOrCreate(['username' => 'admin'], [
            'nip'          => '198501012010011001',
            'nama'         => 'Super Admin Sistem',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('super_admin'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Administrator',
            'jenis_pegawai' => 'PNS',
            'status'       => 'aktif',
        ]);

        // ── Pegawai PNS ──────────────────────────────────────────────────────
        User::firstOrCreate(['username' => 'pegawai'], [
            'nip'          => '199509112025212056',
            'nama'         => 'Pegawai PNS (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('pegawai'),
            'unit_kerja_id' => $subbag?->id,
            'atasan_id'    => $kasi->id,
            'jabatan'      => 'Operator Layanan Operasional',
            'jenis_pegawai' => 'PNS',
            'pangkat_gol'  => 'Penata Muda, III/a',
            'tanggal_lahir' => '1995-09-11',
            'tmt_golongan' => now()->subMonths(30)->toDateString(),
            'masa_kerja_tahun' => 8,
            'masa_kerja_bulan' => 10,
            'status'       => 'aktif',
        ]);

        // ── Pegawai PPPK Penuh Waktu ─────────────────────────────────────────
        User::firstOrCreate(['username' => 'pppk_penuh'], [
            'nip'          => '199203202023111088',
            'nama'         => 'Pegawai PPPK Penuh Waktu (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('pegawai'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Staf Administrasi',
            'jenis_pegawai' => 'PPPK_PENUH_WAKTU',
            'status'       => 'aktif',
        ]);

        // ── Pegawai PPPK Paruh Waktu (hanya presensi & tracking) ─────────────
        User::firstOrCreate(['username' => 'pppk_paruh'], [
            'nip'          => '199803152023212099',
            'nama'         => 'Pegawai PPPK Paruh Waktu (Demo)',
            'password_hash' => Hash::make('password'),
            'role_id'      => $roleId('pegawai'),
            'unit_kerja_id' => $subbag?->id,
            'jabatan'      => 'Staf Administrasi',
            'pangkat_gol'  => '-',
            'jenis_pegawai' => 'PPPK_PARUH_WAKTU',
            'status'       => 'aktif',
        ]);
    }
}
