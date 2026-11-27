<?php

namespace App\Support;

use App\Models\SuratKeluar;
use Illuminate\Support\Carbon;

/**
 * Katalog Master Template Surat Kepegawaian & Persuratan Resmi Kecamatan Tamalate.
 * Diselaraskan dengan dokumen resmi Tata Naskah Dinas Kecamatan Tamalate (Permendagri 83/2022 & Perwali Makassar).
 */
class SuratTemplateCatalog
{
    public const JENIS_CUTI_LIST = [
        'Cuti Tahunan',
        'Cuti Besar',
        'Cuti Sakit',
        'Cuti Melahirkan',
        'Cuti Karena Alasan Penting',
        'Cuti Di Luar Tanggungan Negara',
    ];

    public static function all(): array
    {
        return [
            'cuti' => [
                'key'              => 'cuti',
                'judul'            => 'Formulir Permintaan dan Pemberian Cuti',
                'singkat'          => 'Formulir Cuti',
                'kode_klasifikasi' => '800.1.3',
                'tag_nomor'        => '{AUTO_NUMBER_CUTI}',
                'nomor_template'   => '800.1.3 / {AUTO_NUMBER_CUTI} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Format resmi Permintaan & Pemberian Cuti kepada Wali Kota Makassar Cq. Kepala BKPSDMD Kota Makassar lengkap dengan Catatan Cuti, Pertimbangan Atasan Langsung, dan Keputusan Camat.',
                'persyaratan'      => [
                    'Formulir Permintaan & Pemberian Cuti',
                    'Cuti Sakit: Surat Keterangan Dokter',
                    'Cuti Melahirkan: Surat Keterangan / Perkiraan Lahir',
                    'Cuti Besar: Bukti masa kerja > 5 tahun',
                ],
            ],
            'pengantar' => [
                'key'              => 'pengantar',
                'judul'            => 'Surat Pengantar',
                'singkat'          => 'Surat Pengantar',
                'kode_klasifikasi' => '800.1.11.4',
                'tag_nomor'        => '{AUTO_NUMBER_PENGANTAR}',
                'nomor_template'   => '800.1.11.4 / {AUTO_NUMBER_PENGANTAR} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Surat Pengantar pengiriman berkas kepegawaian (mis. Tunjangan Anak / Usulan BKPSDMD) dengan tabel Jenis Dokumen, Banyaknya, dan Keterangan.',
                'persyaratan'      => [
                    'Daftar berkas yang dikirimkan',
                    'Dokumen pendukung sesuai tujuan surat pengantar',
                ],
            ],
            'fasilitasi' => [
                'key'              => 'fasilitasi',
                'judul'            => 'Permohonan Fasilitasi Aula / Kegiatan',
                'singkat'          => 'Permohonan Fasilitasi',
                'kode_klasifikasi' => '800',
                'tag_nomor'        => '{AUTO_NUMBER_FASILITASI}',
                'nomor_template'   => '800 / {AUTO_NUMBER_FASILITASI} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Surat permohonan izin/fasilitasi tempat kegiatan kepada instansi mitra, ditandatangani Camat atau a.n. Camat Sekretaris Camat.',
                'persyaratan'      => [
                    'Rincian hari, tanggal, waktu, tempat, dan jumlah peserta kegiatan',
                ],
            ],
            'undangan' => [
                'key'              => 'undangan',
                'judul'            => 'Surat Undangan Resmi',
                'singkat'          => 'Undangan',
                'kode_klasifikasi' => '800',
                'tag_nomor'        => '{AUTO_NUMBER_UNDANGAN}',
                'nomor_template'   => '800 / {AUTO_NUMBER_UNDANGAN} / KT / {BULAN_ROMAWI} / {TAHUN}',
                'deskripsi'        => 'Surat Undangan rapat/sosialisasi/pembinaan kepada Lurah se-Kecamatan Tamalate atau perangkat terkait.',
                'persyaratan'      => [
                    'Rincian hari, tanggal, waktu, tempat, dan acara kegiatan',
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
                'deskripsi'        => 'Format Surat Perintah Tugas resmi berdasarkan Perwali Makassar tentang Tata Naskah Dinas.',
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
        $romawi = SuratKeluar::BULAN_ROMAWI[(int) $now->month] ?? 'VI';
        $tahun = (string) $now->year;

        if ($mode === 'blank') {
            return [
                'mode'              => 'blank',
                'meta'              => $meta,
                'nomor_surat'       => $meta['nomor_template'],
                'tanggal_surat'     => '[Tanggal_Surat]',
                'tahun'             => '{TAHUN}',
                'nama_pegawai'      => '[Nama Pegawai]',
                'nip'               => '[NIP / NIPPPK Pegawai]',
                'pangkat_gol'       => '[Pangkat, Gol/Ruang]',
                'jabatan'           => '[Jabatan Pegawai]',
                'unit_kerja'        => 'Kecamatan Tamalate',
                'masa_kerja_teks'   => '[... Tahun ... Bulan]',
                // Cuti
                'jenis_cuti'        => '',
                'lama_hari'         => '[Lama Cuti]',
                'tanggal_mulai'     => '[Tanggal Mulai]',
                'tanggal_selesai'   => '[Tanggal Selesai]',
                'alasan'            => '[Alasan Pengajuan Cuti]',
                'no_hp'             => '[Nomor Telepon Aktif]',
                'alamat_cuti'       => '[Alamat Selama Menjalankan Cuti]',
                'tahun_cuti'        => '{TAHUN}',
                'sisa_cuti'         => '.....',
                'keterangan_cuti'   => '[Keterangan Cuti Tahunan]',
                'atasan_jabatan'    => '[Jabatan Atasan Langsung]',
                'atasan_nama'       => '[Nama Atasan Langsung]',
                'atasan_nip'        => '[NIP Atasan Langsung]',
                // Surat Pengantar
                'tujuan_surat'      => '[Jabatan / Instansi Tujuan]',
                'dokumen_dikirim'   => '[Jenis Dokumen yang Dikirim a/n Pegawai]',
                'banyaknya'         => '1 (Satu) Berkas',
                'ket_pengantar'     => 'Dikirim dengan hormat untuk bahan seperlunya',
                // Fasilitasi & Undangan
                'sifat_surat'       => 'Penting',
                'hal_surat'         => '[Hal Surat]',
                'kegiatan_rangka'   => '[Maksud / Rangka Kegiatan]',
                'hari_tanggal'      => '[Hari, Tanggal Pelaksanaan]',
                'waktu_kegiatan'    => '[08.00 Wita - Selesai]',
                'tempat_kegiatan'   => '[Lokasi / Tempat Kegiatan]',
                'jumlah_peserta'    => '[Jumlah Peserta]',
                'acara_kegiatan'    => '[Nama Acara / Agenda]',
                'pesan_penutup'     => '[Catatan tambahan / utusan peserta]',
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
                'jabatan_ttd'       => $jenis === 'fasilitasi' ? "a.n Camat\nSekretaris Camat," : 'Camat,',
                'nama_camat'        => $jenis === 'fasilitasi' ? '[Nama Sekretaris Camat]' : '[Nama Camat Tamalate]',
                'pangkat_camat'     => '[Pangkat, Gol/Ruang]',
                'nip_camat'         => '[NIP Pejabat]',
            ];
        }

        $tglSuratRaw = $input['tanggal_surat'] ?? $now->toDateString();
        $tglMulaiRaw = $input['tanggal_mulai'] ?? $now->copy()->addDays(2)->toDateString();
        $tglSelesaiRaw = $input['tanggal_selesai'] ?? $now->copy()->addDays(4)->toDateString();

        $val = static fn (string $key, string $fallback) => filled($input[$key] ?? null) ? trim((string) $input[$key]) : $fallback;

        return [
            'mode'              => 'filled',
            'meta'              => $meta,
            'nomor_surat'       => "{$meta['kode_klasifikasi']}/0001/KT/{$romawi}/{$tahun}",
            'tanggal_surat'     => self::formatTanggalId($tglSuratRaw),
            'tahun'             => $tahun,
            'nama_pegawai'      => $val('nama_pegawai', 'A. Mila Karmila'),
            'nip'               => $val('nip', '199509112025212056'),
            'pangkat_gol'       => $val('pangkat_gol', 'Penata Muda Tk. I, III/b'),
            'jabatan'           => $val('jabatan', 'Operator Layanan Operasional'),
            'unit_kerja'        => $val('unit_kerja', 'Kecamatan Tamalate'),
            'masa_kerja_teks'   => $val('masa_kerja_teks', '8 Tahun 10 Bulan'),
            // Cuti
            'jenis_cuti'        => $val('jenis_cuti', 'Cuti Melahirkan'),
            'lama_hari'         => $val('lama_hari', '90 Hari'),
            'tanggal_mulai'     => self::formatTanggalId($tglMulaiRaw),
            'tanggal_selesai'   => self::formatTanggalId($tglSelesaiRaw),
            'alasan'            => $val('alasan', 'Melahirkan / Persiapan Persalinan'),
            'no_hp'             => $val('no_hp', '082395565653'),
            'alamat_cuti'       => $val('alamat_cuti', 'Alamat selama menjalankan cuti'),
            'tahun_cuti'        => $val('tahun_cuti', $tahun),
            'sisa_cuti'         => $val('sisa_cuti', '12'),
            'keterangan_cuti'   => $val('keterangan_cuti', ''),
            'atasan_jabatan'    => $val('atasan_jabatan', 'Kepala Seksi Pemerintahan'),
            'atasan_nama'       => $val('atasan_nama', 'DEWI ROSITA DJAHINI, S.STP'),
            'atasan_nip'        => $val('atasan_nip', '198301282001122001'),
            // Surat Pengantar
            'tujuan_surat'      => $val('tujuan_surat', $jenis === 'fasilitasi' ? 'Direktur Politeknik Pariwisata Makassar' : ($jenis === 'undangan' ? 'Lurah Se Kecamatan Tamalate' : 'Kepala Bidang Mutasi BKPSDMD Kota Makassar')),
            'dokumen_dikirim'   => $val('dokumen_dikirim', "Berkas Permohonan Untuk Mendapatkan Tunjangan Anak\na/n : Ardiansyah Zuqur\nNIP. 19910403 202421 1 007\nPenggerak Swadaya Masyarakat Ahli Pertama"),
            'banyaknya'         => $val('banyaknya', '1 (Satu) Berkas'),
            'ket_pengantar'     => $val('ket_pengantar', 'Dikirim dengan hormat untuk bahan seperlunya'),
            // Fasilitasi & Undangan
            'sifat_surat'       => $val('sifat_surat', 'Penting'),
            'hal_surat'         => $val('hal_surat', $jenis === 'fasilitasi' ? 'Permohonan Fasilitasi Aula' : 'Undangan'),
            'kegiatan_rangka'   => $val('kegiatan_rangka', $jenis === 'fasilitasi'
                ? 'Sosialisasi Pekan Panutan Pajak Bumi dan Bangunan (PBB) Tingkat Kecamatan Tamalate'
                : 'Menindaklanjuti Hasil Bimbingan Teknis Penerapan Perwali tentang Pakaian Dinas ASN dan Tata Naskah Dinas Pemerintah Kota Makassar serta meningkatkan tertib administrasi'),
            'hari_tanggal'      => $val('hari_tanggal', 'Rabu, 17 Juni 2026'),
            'waktu_kegiatan'    => $val('waktu_kegiatan', $jenis === 'fasilitasi' ? '08.00 Wita - Selesai' : '09.00 Wita'),
            'tempat_kegiatan'   => $val('tempat_kegiatan', $jenis === 'fasilitasi' ? 'Politeknik Pariwisata Makassar' : 'Aula Kantor Kecamatan Tamalate Lt. 3'),
            'jumlah_peserta'    => $val('jumlah_peserta', '100 orang'),
            'acara_kegiatan'    => $val('acara_kegiatan', 'Penerapan Peraturan Wali Kota (Perwali) tentang Pakaian Dinas ASN dan Tata Naskah Dinas'),
            'pesan_penutup'     => $val('pesan_penutup', 'Diharapkan agar mengutus Sekretaris Lurah dan 1 orang staf untuk mengikuti kegiatan tersebut.'),
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
            'jabatan_ttd'       => $val('jabatan_ttd', $jenis === 'fasilitasi' ? "a.n Camat\nSekretaris Camat," : 'Camat,'),
            'nama_camat'        => $val('nama_camat', $jenis === 'fasilitasi' ? 'Saddam Musma, S.STP,M.Si' : 'Muh Aril Syahbani K., S.IP'),
            'pangkat_camat'     => $val('pangkat_camat', $jenis === 'fasilitasi' ? 'Penata Tk I, III/d' : 'Pembina, IV/a'),
            'nip_camat'         => $val('nip_camat', $jenis === 'fasilitasi' ? '19910705 201507 1 002' : '19880415 200701 1 001'),
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
