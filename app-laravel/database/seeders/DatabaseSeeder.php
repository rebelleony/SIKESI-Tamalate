<?php

namespace Database\Seeders;

use App\Models\JenisCuti;
use App\Models\Role;
use App\Models\UnitKerja;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 5 Role SIP-TAMALATE ──────────────────────────────────────────────
        $roles = [
            ['super_admin',          'Super Admin: kelola data master, akun pengguna & hak akses sistem'],
            ['operator',             'Operator: verifikasi berkas pengajuan, validasi presensi & rekap laporan'],
            ['verifikator_pimpinan', 'Verifikator Pimpinan: persetujuan tingkat pertama (Kasi/Kasubag)'],
            ['pimpinan',             'Pimpinan: persetujuan akhir & pengesahan dokumen (Camat/Lurah)'],
            ['pegawai',              'Pegawai / Staf: pengajuan layanan & presensi apel'],
        ];
        foreach ($roles as [$nama, $deskripsi]) {
            Role::firstOrCreate(['nama_role' => $nama], ['deskripsi' => $deskripsi]);
        }

        // ── Unit Kerja Dasar (Kecamatan + Subbag minimal) ───────────────────
        $kecamatan = UnitKerja::firstOrCreate(
            ['nama' => 'Kecamatan Tamalate', 'tipe' => 'kecamatan'],
            ['induk_id' => null]
        );

        UnitKerja::firstOrCreate(
            ['nama' => 'Subbag Umum & Kepegawaian', 'tipe' => 'subbag'],
            ['induk_id' => $kecamatan->id]
        );

        // ── Jenis Cuti ───────────────────────────────────────────────────────
        $cuti = [
            ['Cuti Tahunan',                    12,   'Maksimal 12 hari kerja per tahun'],
            ['Cuti Besar',                       null, 'Hak setelah masa kerja tertentu'],
            ['Cuti Sakit',                       null, 'Berdasarkan surat keterangan dokter'],
            ['Cuti Melahirkan',                  90,   'Untuk pegawai wanita, 3 bulan'],
            ['Cuti Karena Alasan Penting',       null, 'Sesuai ketentuan BKN'],
            ['Cuti Di Luar Tanggungan Negara',   null, 'Maksimal 3 tahun'],
        ];
        foreach ($cuti as [$nama, $max, $ket]) {
            JenisCuti::firstOrCreate(['nama' => $nama], ['max_hari' => $max, 'keterangan' => $ket]);
        }

        // ── Jalankan seeder lanjutan ─────────────────────────────────────────
        $this->call([
            TamalateUnitSeeder::class,    // 11 kelurahan, 5 seksi, 1 subbag tambahan + demo PPPK
            KodeKlasifikasiSeeder::class, // Master kode klasifikasi Permendagri 83/2022
            DemoUserSeeder::class,
            DemoTamalateSeeder::class,    // Akun demo lengkap & transaksi realistis Kecamatan Tamalate
        ]);
    }
}
