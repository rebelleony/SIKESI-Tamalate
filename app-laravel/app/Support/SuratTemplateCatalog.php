<?php

namespace App\Support;

use App\Models\SuratKeluar;
use Illuminate\Support\Carbon;

/**
 * Katalog Master Template Surat Kepegawaian & Tata Naskah Dinas Kecamatan Tamalate.
 * Sumber data terpusat untuk Pratinjau Web, Ekspor PDF, dan Ekspor DOCX.
 */
class SuratTemplateCatalog
{
    public const JENIS_CUTI_LIST = [
        'Cuti Tahunan',
        'Cuti Sakit',
        'Cuti Melahirkan',
        'Cuti Besar',
        'Cuti Alasan Penting',
        'Cuti di Luar Tanggungan Negara (CLTN)',
    ];

    public static function all(): array
    {
        return [
            'cuti' => [
                'key'              => 'cuti',
                'judul'            => 'Formulir Permohonan Cuti Pegawai',
                'singkat'          => 'Permohonan Cuti',
                'kode_klasifikasi' => '800.1.3',
                'tag_nomor'        => '{AUTO_NUMBER_CUTI}',
                'nomor_template'   => '800.1.3 / {AUTO_NUMBER_CUTI} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Format baku permohonan Cuti Tahunan, Sakit, Melahirkan, Besar, Alasan Penting, dan CLTN lengkap dengan kolom catatan sisa cuti Kasubag Kepegawaian.',
                'persyaratan'      => [
                    'Formulir Permohonan Cuti Pegawai (ditandatangani pemohon)',
                    'Cuti Sakit: Surat Keterangan Dokter',
                    'Cuti Melahirkan: Surat Perkiraan Lahir / HPL',
                    'Cuti Besar: Bukti masa kerja > 5 tahun',
                ],
            ],
            'kgb' => [
                'key'              => 'kgb',
                'judul'            => 'Usulan Kenaikan Gaji Berkala (KGB)',
                'singkat'          => 'Usulan KGB',
                'kode_klasifikasi' => '800.1.3.1',
                'tag_nomor'        => '{AUTO_NUMBER_KGB}',
                'nomor_template'   => '800.1.3.1 / {AUTO_NUMBER_KGB} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Surat pengantar resmi Camat Tamalate kepada Kepala BKPSDMD Kota Makassar untuk usulan KGB ASN/PPPK.',
                'persyaratan'      => [
                    'Fotokopi SK Pangkat Terakhir',
                    'Fotokopi SK KGB Terakhir',
                    'Fotokopi SKP 2 Tahun Terakhir',
                ],
            ],
            'spt' => [
                'key'              => 'spt',
                'judul'            => 'Surat Perintah Tugas (SPT)',
                'singkat'          => 'Surat Perintah Tugas',
                'kode_klasifikasi' => '000.1.2',
                'tag_nomor'        => '{AUTO_NUMBER_ST}',
                'nomor_template'   => '000.1.2 / {AUTO_NUMBER_ST} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Format Surat Perintah Tugas resmi berdasarkan Perwali Makassar No. 16 Tahun 2017 tentang Tata Naskah Dinas.',
                'persyaratan'      => [
                    'Surat undangan / dasar penugasan kedinasan',
                    'Daftar nama pegawai yang ditugaskan',
                ],
            ],
            'kenaikan_pangkat' => [
                'key'              => 'kenaikan_pangkat',
                'judul'            => 'Usulan Kenaikan Pangkat PNS',
                'singkat'          => 'Kenaikan Pangkat',
                'kode_klasifikasi' => '800.1.3.2',
                'tag_nomor'        => '{AUTO_NUMBER_KP}',
                'nomor_template'   => '800.1.3.2 / {AUTO_NUMBER_KP} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Surat usulan kenaikan pangkat reguler/pilihan dari Kecamatan Tamalate ke BKPSDMD Kota Makassar.',
                'persyaratan'      => [
                    'Fotokopi SK CPNS & SK PNS',
                    'Fotokopi SK Pangkat Terakhir',
                    'SKP 2 Tahun Terakhir',
                    'Fotokopi Ijazah & Transkrip Nilai Terakhir',
                    'Daftar Riwayat Hidup / Surat Pengantar Unit',
                ],
            ],
            'pensiun' => [
                'key'              => 'pensiun',
                'judul'            => 'Surat Permohonan & Usulan Pensiun PNS',
                'singkat'          => 'Usulan Pensiun',
                'kode_klasifikasi' => '800.1.3.3',
                'tag_nomor'        => '{AUTO_NUMBER_PENSIUN}',
                'nomor_template'   => '800.1.3.3 / {AUTO_NUMBER_PENSIUN} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Format pengantar usulan Pensiun Batas Usia Pensiun (BUP) atau Atas Permintaan Sendiri (APS).',
                'persyaratan'      => [
                    'Surat Permohonan Pensiun / Pengantar Unit Kerja',
                    'Fotokopi SK CPNS, SK PNS & SK Pangkat Terakhir',
                    'Fotokopi KARPEG & Taspen',
                    'Fotokopi KK, Akta Nikah, Pas Foto & SKP 1 Tahun Terakhir',
                ],
            ],
        ];
    }

    public static function get(string $jenis): ?array
    {
        return self::all()[$jenis] ?? null;
    }

    public static function resolveData(string $jenis, string $mode, array $input = []): array
    {
        $meta = self::get($jenis) ?? self::get('cuti');
        $now = Carbon::now('Asia/Makassar');
        $romawi = SuratKeluar::BULAN_ROMAWI[(int) $now->month] ?? 'XI';
        $tahun = (string) $now->year;

        if ($mode === 'blank') {
            return [
                'mode'              => 'blank',
                'meta'              => $meta,
                'nomor_surat'       => $meta['nomor_template'],
                'tanggal_surat'     => '[Tanggal_Pengajuan]',
                'tahun'             => '{TAHUN}',
                'nama_pegawai'      => '[Nama Pegawai]',
                'nip'               => '[NIP / NIPPPK Pegawai]',
                'pangkat_gol'       => '[Pangkat, Gol/Ruang]',
                'jabatan'           => '[Jabatan Pegawai]',
                'unit_kerja'        => 'Kecamatan Tamalate / Kelurahan [Nama Kelurahan]',
                // Cuti
                'jenis_cuti'        => '',
                'lama_hari'         => '[Jumlah_Hari]',
                'tanggal_mulai'     => '[Tanggal_Mulai]',
                'tanggal_selesai'   => '[Tanggal_Selesai]',
                'alasan'            => '[Jelaskan alasan cuti secara singkat dan jelas]',
                'no_hp'             => '[Nomor Telepon Aktif]',
                'sisa_cuti'         => '.....',
                // KGB
                'gaji_lama'         => '[Gaji Lama]',
                'tmt_kgb_lama'      => '[TMT KGB Lama]',
                'gaji_baru'         => '[Gaji Baru]',
                'tmt_kgb_baru'      => '[TMT KGB Baru]',
                'masa_kerja_tahun'  => '[Tahun]',
                'masa_kerja_bulan'  => '[Bulan]',
                // SPT
                'dasar_instansi'    => '[Nama Instansi Pengirim / Undangan]',
                'dasar_nomor'       => '[Nomor_Surat_Undangan]',
                'dasar_tanggal'     => '[Tanggal_Surat]',
                'dasar_perihal'     => '[Perihal_Tugas]',
                'maksud_tugas'      => '[Maksud Penugasan Kedinasan]',
                'lokasi_tugas'      => '[Lokasi Tujuan]',
                'tanggal_tugas'     => '[Tanggal Pelaksanaan]',
                'pegawai2_nama'     => '[Nama Pegawai 2]',
                'pegawai2_nip'      => '[NIP Pegawai 2]',
                'pegawai2_gol'      => '[Gol 2]',
                'pegawai2_jabatan'  => '[Jabatan Pegawai 2]',
                // KP & Pensiun
                'golongan_usulan'   => '[Golongan Diusulkan]',
                'tmt_usulan'        => '[TMT Diusulkan]',
                'jenis_pensiun'     => '[BUP / APS]',
                // Penandatangan
                'nama_camat'        => '[NAMA CAMAT TAMALATE]',
                'pangkat_camat'     => '[Pangkat Camat]',
                'nip_camat'         => '[NIP Camat]',
            ];
        }

        $tglSuratRaw = $input['tanggal_surat'] ?? $now->toDateString();
        $tglMulaiRaw = $input['tanggal_mulai'] ?? $now->copy()->addDays(2)->toDateString();
        $tglSelesaiRaw = $input['tanggal_selesai'] ?? $now->copy()->addDays(4)->toDateString();

        $val = static fn (string $key, string $fallback) => filled($input[$key] ?? null) ? trim((string) $input[$key]) : $fallback;

        return [
            'mode'              => 'filled',
            'meta'              => $meta,
            'nomor_surat'       => "{$meta['kode_klasifikasi']} / 0001 / KT / {$romawi} / {$tahun}",
            'tanggal_surat'     => self::formatTanggalId($tglSuratRaw),
            'tahun'             => $tahun,
            'nama_pegawai'      => $val('nama_pegawai', 'Andi Rahmat Hidayat, S.STP'),
            'nip'               => $val('nip', '199405122020121004'),
            'pangkat_gol'       => $val('pangkat_gol', 'Penata Muda Tk. I / III/b'),
            'jabatan'           => $val('jabatan', 'Analis Sumber Daya Manusia Aparatur'),
            'unit_kerja'        => $val('unit_kerja', 'Kecamatan Tamalate / Subbag Umum & Kepegawaian'),
            // Cuti
            'jenis_cuti'        => $val('jenis_cuti', 'Cuti Tahunan'),
            'lama_hari'         => $val('lama_hari', '3'),
            'tanggal_mulai'     => self::formatTanggalId($tglMulaiRaw),
            'tanggal_selesai'   => self::formatTanggalId($tglSelesaiRaw),
            'alasan'            => $val('alasan', 'Keperluan urusan keluarga di luar kota'),
            'no_hp'             => $val('no_hp', '081241009988'),
            'sisa_cuti'         => $val('sisa_cuti', '12'),
            // KGB
            'gaji_lama'         => $val('gaji_lama', '3.150.000'),
            'tmt_kgb_lama'      => $val('tmt_kgb_lama', '01 November 2024'),
            'gaji_baru'         => $val('gaji_baru', '3.280.000'),
            'tmt_kgb_baru'      => $val('tmt_kgb_baru', '01 November 2026'),
            'masa_kerja_tahun'  => $val('masa_kerja_tahun', '8'),
            'masa_kerja_bulan'  => $val('masa_kerja_bulan', '0'),
            // SPT
            'dasar_instansi'    => $val('dasar_instansi', 'BKPSDMD Kota Makassar'),
            'dasar_nomor'       => $val('dasar_nomor', '800/412/BKPSDMD/XI/2026'),
            'dasar_tanggal'     => self::formatTanggalId($tglSuratRaw),
            'dasar_perihal'     => $val('dasar_perihal', 'Bimbingan Teknis Aplikasi Kepegawaian Terpadu'),
            'maksud_tugas'      => $val('maksud_tugas', 'Mengikuti Bimbingan Teknis Sistem Informasi Kepegawaian'),
            'lokasi_tugas'      => $val('lokasi_tugas', 'Balai Kota Makassar'),
            'tanggal_tugas'     => $val('tanggal_tugas', self::formatTanggalId($tglMulaiRaw)),
            'pegawai2_nama'     => $val('pegawai2_nama', 'Siti Nurhaliza, S.AP'),
            'pegawai2_nip'      => $val('pegawai2_nip', '199708172022032005'),
            'pegawai2_gol'      => $val('pegawai2_gol', 'III/a'),
            'pegawai2_jabatan'  => $val('pegawai2_jabatan', 'Pengadministrasi Kepegawaian'),
            // KP & Pensiun
            'golongan_usulan'   => $val('golongan_usulan', 'III/c'),
            'tmt_usulan'        => $jenis === 'pensiun'
                ? $val('tmt_usulan_pensiun', '01 Januari 2027')
                : $val('tmt_usulan', '01 April 2027'),
            'jenis_pensiun'     => $val('jenis_pensiun', 'Batas Usia Pensiun (BUP)'),
            // Penandatangan
            'nama_camat'        => $val('nama_camat', 'H. Emil Yudianto Tadjuddin, S.STP., M.Si'),
            'pangkat_camat'     => $val('pangkat_camat', 'Pembina Tk. I / IV/b'),
            'nip_camat'         => $val('nip_camat', '197804151998031002'),
        ];
    }

    public static function formatTanggalId(string $dateStr): string
    {
        try {
            return Carbon::parse($dateStr)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $dateStr;
        }
    }
}
