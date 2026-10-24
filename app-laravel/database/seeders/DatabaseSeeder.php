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
        $roles = [
            ['super_admin', 'Administrator sistem: kelola data master & akun'],
            ['verifikator', 'Staf kepegawaian/arsip: verifikasi berkas & laporan'],
            ['kasi_kasubag', 'Kepala Seksi / Kepala Sub-bagian: persetujuan tingkat pertama'],
            ['seksam', 'Sekretaris Camat: verifikasi & paraf draf (persetujuan tingkat kedua)'],
            ['camat_lurah', 'Camat / Lurah: persetujuan akhir'],
            ['pegawai', 'Pegawai/staf: pengajuan layanan & presensi apel'],
        ];
        foreach ($roles as [$nama, $deskripsi]) {
            Role::firstOrCreate(['nama_role' => $nama], ['deskripsi' => $deskripsi]);
        }

        $kecamatan = UnitKerja::firstOrCreate(
            ['nama' => 'Kecamatan Tamalate', 'tipe' => 'kecamatan'],
            ['induk_id' => null]
        );

        UnitKerja::firstOrCreate(
            ['nama' => 'Subbag Umum & Kepegawaian', 'tipe' => 'subbag'],
            ['induk_id' => $kecamatan->id]
        );

        $cuti = [
            ['Cuti Tahunan', 12, 'Maksimal 12 hari kerja per tahun'],
            ['Cuti Besar', null, 'Hak setelah masa kerja tertentu'],
            ['Cuti Sakit', null, 'Berdasarkan surat keterangan dokter'],
            ['Cuti Melahirkan', 90, 'Untuk pegawai wanita, 3 bulan'],
            ['Cuti Karena Alasan Penting', null, 'Sesuai ketentuan BKN'],
            ['Cuti Di Luar Tanggungan Negara', null, 'Maksimal 3 tahun'],
        ];
        foreach ($cuti as [$nama, $max, $ket]) {
            JenisCuti::firstOrCreate(['nama' => $nama], ['max_hari' => $max, 'keterangan' => $ket]);
        }

        $this->call(DemoUserSeeder::class);
    }
}
