<?php

namespace Database\Seeders;

use App\Models\KodeKlasifikasi;
use Illuminate\Database\Seeder;

/**
 * KodeKlasifikasiSeeder — Permendagri No. 83 Tahun 2022 (Pemkot Makassar).
 */
class KodeKlasifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['kode' => '000', 'nama' => 'Umum / Ketatausahaan', 'induk_kode' => null, 'keterangan' => 'Urusan umum, ketatausahaan, organisasi dan tata laksana'],
            ['kode' => '400', 'nama' => 'Kesejahteraan Rakyat / Kependudukan / Humas', 'induk_kode' => null, 'keterangan' => 'Bidang sosial, kependudukan, pemberdayaan masyarakat, dan kehumasan'],
            ['kode' => '600', 'nama' => 'Pekerjaan Umum / Kebersihan', 'induk_kode' => null, 'keterangan' => 'Infrastruktur, pekerjaan umum, tata ruang, dan kebersihan wilayah'],
            ['kode' => '800', 'nama' => 'Kepegawaian', 'induk_kode' => null, 'keterangan' => 'Administrasi kepegawaian ASN dan non-ASN'],
            ['kode' => '800.1.3.2', 'nama' => 'Kenaikan Pangkat', 'induk_kode' => '800', 'keterangan' => 'Usulan dan SK kenaikan pangkat pegawai'],
            ['kode' => '800.1.6.6', 'nama' => 'Pensiun', 'induk_kode' => '800', 'keterangan' => 'Pengurusan pemberhentian dan pensiun pegawai'],
            ['kode' => '800.1.11.2-7', 'nama' => 'Cuti', 'induk_kode' => '800', 'keterangan' => 'Pengajuan cuti tahunan, cuti besar, cuti sakit, cuti melahirkan, dan cuti alasan penting'],
            ['kode' => '800.1.11.13', 'nama' => 'Kenaikan Gaji Berkala (KGB)', 'induk_kode' => '800', 'keterangan' => 'Penerbitan surat pemberitahuan KGB'],
            ['kode' => '900', 'nama' => 'Keuangan', 'induk_kode' => null, 'keterangan' => 'Pengelolaan anggaran, perbendaharaan, dan pertanggungjawaban keuangan'],
        ];

        foreach ($items as $item) {
            KodeKlasifikasi::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'induk_kode' => $item['induk_kode'],
                    'keterangan' => $item['keterangan'],
                ]
            );
        }
    }
}

