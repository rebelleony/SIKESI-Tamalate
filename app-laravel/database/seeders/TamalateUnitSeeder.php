<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TamalateUnitSeeder — SIP-TAMALATE
 *
 * Memastikan semua unit kerja Kecamatan Tamalate tersedia di DB:
 * - 1 Kecamatan Tamalate
 * - 2 Subbagian (Subbag Umum & Kepegawaian, Subbag Perencanaan & Keuangan)
 * - 5 Seksi
 * - 11 Kelurahan
 *
 * Seeder ini bersifat idempotent (aman dijalankan berulang kali).
 */
class TamalateUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Kecamatan ────────────────────────────────────────────────────────
        $kecamatan = UnitKerja::firstOrCreate(
            ['nama' => 'Kecamatan Tamalate', 'tipe' => 'kecamatan'],
            ['induk_id' => null]
        );

        // ── 2 Subbagian ──────────────────────────────────────────────────────
        $subbagian = [
            'Subbag Umum & Kepegawaian',
            'Subbag Perencanaan & Keuangan',
        ];
        $subbagPertama = null;
        foreach ($subbagian as $nama) {
            $unit = UnitKerja::firstOrCreate(
                ['nama' => $nama, 'tipe' => 'subbag'],
                ['induk_id' => $kecamatan->id]
            );
            if ($subbagPertama === null) {
                $subbagPertama = $unit;
            }
        }

        // ── 5 Seksi ───────────────────────────────────────────────────────────
        $seksi = [
            'Seksi Pemerintahan',
            'Seksi Pembangunan',
            'Seksi Pemberdayaan Masyarakat',
            'Seksi Kesejahteraan Sosial',
            'Seksi Ketentraman & Ketertiban Umum',
        ];
        foreach ($seksi as $nama) {
            UnitKerja::firstOrCreate(
                ['nama' => $nama, 'tipe' => 'seksi'],
                ['induk_id' => $kecamatan->id]
            );
        }

        // ── 11 Kelurahan resmi Kecamatan Tamalate ────────────────────────────
        $kelurahan = [
            'Kelurahan Barombong',
            'Kelurahan Tanjung Merdeka',
            'Kelurahan Maccini Sombala',
            'Kelurahan Parang Tambung',
            'Kelurahan Mannuruki',
            "Kelurahan Pa'baeng-baeng",
            'Kelurahan Balang Baru',
            'Kelurahan Jongaya',
            'Kelurahan Bongaya',
            'Kelurahan Tamparang Keke',
            'Kelurahan Mangasa',
        ];
        foreach ($kelurahan as $nama) {
            UnitKerja::firstOrCreate(
                ['nama' => $nama, 'tipe' => 'kelurahan'],
                ['induk_id' => $kecamatan->id]
            );
        }

        $this->command?->info('✓ Unit kerja Kecamatan Tamalate berhasil di-seed ('
            . count($subbagian) . ' subbag, '
            . count($seksi) . ' seksi, '
            . count($kelurahan) . ' kelurahan).');

        // ── Demo user PPPK Paruh Waktu (untuk pengujian) ─────────────────────
        $pegawaiRole = Role::where('nama_role', 'pegawai')->value('id');

        if ($pegawaiRole && $subbagPertama) {
            User::firstOrCreate(['username' => 'pppk_paruh'], [
                'nip'          => '199803152023212099',
                'nama'         => 'Pegawai PPPK Paruh Waktu (Demo)',
                'password_hash' => Hash::make('password'),
                'role_id'      => $pegawaiRole,
                'unit_kerja_id' => $subbagPertama->id,
                'jabatan'      => 'Staf Administrasi',
                'pangkat_gol'  => '-',
                'jenis_pegawai' => 'PPPK_PARUH_WAKTU',
                'status'       => 'aktif',
            ]);

            $this->command?->info('✓ Demo user PPPK Paruh Waktu (username: pppk_paruh, password: password) tersedia.');
        }
    }
}

