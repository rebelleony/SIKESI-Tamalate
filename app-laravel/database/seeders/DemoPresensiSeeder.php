<?php

namespace Database\Seeders;

use App\Models\PresensiApel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DemoPresensiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat direktori dan gambar simulasi presensi jika belum ada
        Storage::disk('public')->makeDirectory('presensi/demo');
        $imgDir = storage_path('app/public/presensi/demo');

        $this->generateDummyPhoto($imgDir . '/apel_pagi.jpg', 'APEL PAGI - KEC. TAMALATE', [30, 90, 180]);
        $this->generateDummyPhoto($imgDir . '/apel_sore.jpg', 'APEL SORE - KEC. TAMALATE', [200, 100, 30]);

        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $verifikator = User::where('username', 'verifikator')->first() ?? $users->first();

        // 2. Isi kehadiran selama sepekan terakhir (Senin s.d. kemarin)
        $mulaiPekan = today()->startOfWeek(); // Senin pekan ini
        $hariKemarin = today()->isMonday() ? $mulaiPekan : today()->subDay();

        for ($tgl = $mulaiPekan->copy(); $tgl->lte($hariKemarin); $tgl->addDay()) {
            // Hindari Sabtu / Minggu
            if ($tgl->isWeekend()) {
                continue;
            }

            foreach ($users as $user) {
                // Sesi Pagi (07.00 - 08.30)
                PresensiApel::firstOrCreate(
                    [
                        'pegawai_id' => $user->id,
                        'tanggal' => $tgl->toDateString(),
                        'sesi' => 'pagi',
                    ],
                    [
                        'jam' => sprintf('07:%02d:%02d', rand(10, 28), rand(10, 50)),
                        'foto_path' => 'presensi/demo/apel_pagi.jpg',
                        'status_validasi' => 'hadir',
                        'validasi_oleh' => $verifikator->id,
                        'waktu_validasi' => $tgl->copy()->setTime(8, 45, 0),
                        'keterangan' => 'Hadir tepat waktu di halaman kantor kecamatan',
                        'created_at' => $tgl->copy()->setTime(7, 20, 0),
                    ]
                );

                // Sesi Sore (15.30 - 17.00)
                PresensiApel::firstOrCreate(
                    [
                        'pegawai_id' => $user->id,
                        'tanggal' => $tgl->toDateString(),
                        'sesi' => 'sore',
                    ],
                    [
                        'jam' => sprintf('15:%02d:%02d', rand(35, 55), rand(10, 50)),
                        'foto_path' => 'presensi/demo/apel_sore.jpg',
                        'status_validasi' => 'hadir',
                        'validasi_oleh' => $verifikator->id,
                        'waktu_validasi' => $tgl->copy()->setTime(17, 10, 0),
                        'keterangan' => 'Apel sore selesai dinas',
                        'created_at' => $tgl->copy()->setTime(15, 45, 0),
                    ]
                );
            }
        }
    }

    private function generateDummyPhoto(string $targetFile, string $label, array $rgb): void
    {
        if (file_exists($targetFile)) {
            return;
        }

        $w = 640;
        $h = 480;
        $im = imagecreatetruecolor($w, $h);

        $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        $white = imagecolorallocate($im, 255, 255, 255);
        $dark = imagecolorallocate($im, 15, 23, 42);

        imagefilledrectangle($im, 0, 0, $w, $h, $bg);
        imagefilledrectangle($im, 20, 20, $w - 20, $h - 20, $dark);

        // Header Box
        $primary = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($im, 20, 20, $w - 20, 90, $primary);

        imagestring($im, 5, 40, 45, 'BUKTI PRESENSI APEL TERVERIFIKASI', $white);
        imagestring($im, 4, 40, 130, 'LOKASI: Halaman Kantor Kecamatan Tamalate', $white);
        imagestring($im, 4, 40, 170, 'SESI  : ' . $label, $white);
        imagestring($im, 4, 40, 210, 'STATUS: Geolocation Valid & Foto Terverifikasi', $white);
        imagestring($im, 3, 40, 400, 'SI Kepegawaian & Presensi Terpadu · Pemkot Makassar', $white);

        imagejpeg($im, $targetFile, 85);
        imagedestroy($im);
    }
}
