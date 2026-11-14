<?php

namespace Database\Seeders;

use App\Models\KodeKlasifikasi;
use App\Models\LogStatusPengajuan;
use App\Models\PengaturanApel;
use App\Models\PengajuanKepegawaian;
use App\Models\PresensiApel;
use App\Models\Role;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * DemoTamalateSeeder — SIP-TAMALATE
 *
 * Seeder data demo realistis Kecamatan Tamalate untuk presentasi / expo:
 * - Akun demo lengkap setiap peran (NIP & password: "password")
 * - 3 Surat Masuk dengan status disposisi berbeda
 * - 3 Surat Keluar berformat standar Pemkot Makassar
 * - 3 Pengajuan Kepegawaian (Cuti & KGB) mencakup alur Kelurahan & Kecamatan
 * - Data Presensi Apel (Pagi/Sore) & contoh akumulasi TK untuk Pembinaan
 *
 * Aman dijalankan berulang kali (idempotent).
 */
class DemoTamalateSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan unit kerja, kode klasifikasi, dan pengaturan apel tersedia
        $this->call([
            TamalateUnitSeeder::class,
            KodeKlasifikasiSeeder::class,
        ]);

        PengaturanApel::current();

        $roleId = fn (string $nama) => Role::where('nama_role', $nama)->value('id');

        $kecamatan = UnitKerja::where('nama', 'Kecamatan Tamalate')->first();
        $subbagUmum = UnitKerja::where('nama', 'Subbag Umum & Kepegawaian')->first();
        $seksiPem = UnitKerja::where('nama', 'Seksi Pemerintahan')->first();
        $seksiTrantib = UnitKerja::where('nama', 'Seksi Ketentraman & Ketertiban Umum')->first();
        $kelMaccini = UnitKerja::where('nama', 'Kelurahan Maccini Sombala')->first();

        $password = Hash::make('password');

        // ── 1. Akun Demo Lengkap (Password semua akun: "password") ──────────
        // Menggunakan kode unik demo non-sensitif sebagai NIP agar mudah diketik saat presentasi.
        $camat = User::updateOrCreate(
            ['username' => 'camat.tamalate'],
            [
                'nip'              => 'CAMAT-01',
                'nama'             => 'H. Emil Yudianto, S.STP., M.Si',
                'password_hash'    => $password,
                'role_id'          => $roleId('pimpinan'),
                'unit_kerja_id'    => $kecamatan?->id,
                'unit_kerja_nama'  => $kecamatan?->nama,
                'jabatan'          => 'Camat Tamalate',
                'pangkat_gol'      => 'Pembina Tk. I (IV/b)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $lurah = User::updateOrCreate(
            ['username' => 'lurah.maccini'],
            [
                'nip'              => 'LURAH-01',
                'nama'             => 'Andi Rahmatullah, S.Sos',
                'password_hash'    => $password,
                'role_id'          => $roleId('pimpinan'),
                'unit_kerja_id'    => $kelMaccini?->id,
                'unit_kerja_nama'  => $kelMaccini?->nama,
                'atasan_id'        => $camat->id,
                'jabatan'          => 'Lurah Maccini Sombala',
                'pangkat_gol'      => 'Penata Tk. I (III/d)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $sekcam = User::updateOrCreate(
            ['username' => 'sekcam.tamalate'],
            [
                'nip'              => 'SEKCAM-01',
                'nama'             => 'Drs. Muhammad Iqbal, M.AP',
                'password_hash'    => $password,
                'role_id'          => $roleId('verifikator_pimpinan'),
                'unit_kerja_id'    => $kecamatan?->id,
                'unit_kerja_nama'  => $kecamatan?->nama,
                'atasan_id'        => $camat->id,
                'jabatan'          => 'Sekretaris Camat Tamalate',
                'pangkat_gol'      => 'Pembina (IV/a)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $kasubag = User::updateOrCreate(
            ['username' => 'kasubag.umum'],
            [
                'nip'              => 'KASUBAG-01',
                'nama'             => 'Hj. Nurhayati, S.E',
                'password_hash'    => $password,
                'role_id'          => $roleId('verifikator_pimpinan'),
                'unit_kerja_id'    => $subbagUmum?->id,
                'unit_kerja_nama'  => $subbagUmum?->nama,
                'atasan_id'        => $sekcam->id,
                'jabatan'          => 'Kasubag Umum & Kepegawaian',
                'pangkat_gol'      => 'Penata Tk. I (III/d)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $operatorKec = User::updateOrCreate(
            ['username' => 'operator.kec'],
            [
                'nip'              => 'OP-KEC-01',
                'nama'             => 'Ahmad Fauzi, S.Kom',
                'password_hash'    => $password,
                'role_id'          => $roleId('operator'),
                'unit_kerja_id'    => $subbagUmum?->id,
                'unit_kerja_nama'  => $subbagUmum?->nama,
                'atasan_id'        => $kasubag->id,
                'jabatan'          => 'Pengelola Kepegawaian & Arsip Kecamatan',
                'pangkat_gol'      => 'Penata Muda Tk. I (III/b)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $operatorKel = User::updateOrCreate(
            ['username' => 'operator.kel'],
            [
                'nip'              => 'OP-KEL-01',
                'nama'             => 'Siti Aminah, A.Md',
                'password_hash'    => $password,
                'role_id'          => $roleId('operator'),
                'unit_kerja_id'    => $kelMaccini?->id,
                'unit_kerja_nama'  => $kelMaccini?->nama,
                'atasan_id'        => $lurah->id,
                'jabatan'          => 'Operator Kelurahan Maccini Sombala',
                'pangkat_gol'      => 'Pengatur Tk. I (II/d)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        $pegawaiKec = User::updateOrCreate(
            ['username' => 'pegawai.kec'],
            [
                'nip'              => 'PEG-KEC-01',
                'nama'             => 'Rizky Pratama, S.IP',
                'password_hash'    => $password,
                'role_id'          => $roleId('pegawai'),
                'unit_kerja_id'    => $seksiPem?->id,
                'unit_kerja_nama'  => $seksiPem?->nama,
                'atasan_id'        => $kasubag->id,
                'jabatan'          => 'Analis Pemerintahan Umum',
                'pangkat_gol'      => 'Penata Muda (III/a)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'tmt_pegawai'      => '2019-03-01',
                'tmt_golongan'     => '2023-04-01',
                'masa_kerja_tahun' => 7,
                'masa_kerja_bulan' => 8,
                'gaji_pokok'       => 3150000,
                'status'           => 'aktif',
            ]
        );

        $pegawaiKel = User::updateOrCreate(
            ['username' => 'pegawai.kel'],
            [
                'nip'              => 'PEG-KEL-01',
                'nama'             => 'Nurfadillah, S.AP',
                'password_hash'    => $password,
                'role_id'          => $roleId('pegawai'),
                'unit_kerja_id'    => $kelMaccini?->id,
                'unit_kerja_nama'  => $kelMaccini?->nama,
                'atasan_id'        => $lurah->id,
                'jabatan'          => 'Staf Pelayanan Kelurahan Maccini Sombala',
                'pangkat_gol'      => 'Penata Muda (III/a)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'tmt_pegawai'      => '2020-12-01',
                'tmt_golongan'     => '2024-04-01',
                'masa_kerja_tahun' => 5,
                'masa_kerja_bulan' => 11,
                'gaji_pokok'       => 2980000,
                'status'           => 'aktif',
            ]
        );

        $pppkParuhWaktu = User::updateOrCreate(
            ['username' => 'pppk.paruhwaktu'],
            [
                'nip'              => 'PPPK-PW-01',
                'nama'             => 'Muhammad Akbar',
                'password_hash'    => $password,
                'role_id'          => $roleId('pegawai'),
                'unit_kerja_id'    => $seksiTrantib?->id,
                'unit_kerja_nama'  => $seksiTrantib?->nama,
                'atasan_id'        => $kasubag->id,
                'jabatan'          => 'Petugas Ketertiban Umum (PPPK Paruh Waktu)',
                'pangkat_gol'      => 'Golongan V',
                'jenis_pegawai'    => 'PPPK_PARUH_WAKTU',
                'status_pegawai'   => 'PPPK',
                'status'           => 'aktif',
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin.tamalate'],
            [
                'nip'              => 'ADMIN-01',
                'nama'             => 'Admin Sistem Tamalate',
                'password_hash'    => $password,
                'role_id'          => $roleId('super_admin'),
                'unit_kerja_id'    => $subbagUmum?->id,
                'unit_kerja_nama'  => $subbagUmum?->nama,
                'jabatan'          => 'Administrator Sistem SIP-TAMALATE',
                'pangkat_gol'      => 'Penata (III/c)',
                'jenis_pegawai'    => 'PNS',
                'status_pegawai'   => 'PNS',
                'status'           => 'aktif',
            ]
        );

        // ── 2. Data Dummy Surat Masuk (3 status disposisi berbeda) ──────────
        $kode800 = KodeKlasifikasi::where('kode', '800')->first();
        $kode000 = KodeKlasifikasi::where('kode', '000')->first();
        $kode400 = KodeKlasifikasi::where('kode', '400')->first();

        $nextAgenda = ((int) SuratMasuk::max('no_agenda')) ?: 0;

        $suratMasukSamples = [
            [
                'nomor_surat'         => '800/412/BKPSDMD/XI/2026',
                'asal_surat'          => 'BKPSDMD Kota Makassar',
                'perihal'             => 'Permintaan Data Usulan KGB Periode Desember 2026',
                'kode_klasifikasi_id' => $kode800?->id,
                'klasifikasi'         => '800',
                'sifat'               => 'penting',
                'status'              => 'dicatat',
                'keterangan'          => 'Menunggu pemeriksaan dan paraf Sekretaris Camat',
                'tujuan_unit_id'      => $subbagUmum?->id,
            ],
            [
                'nomor_surat'         => '005/189/SETDA/XI/2026',
                'asal_surat'          => 'Sekretariat Daerah Kota Makassar',
                'perihal'             => 'Undangan Rapat Koordinasi Evaluasi Kinerja Kecamatan',
                'kode_klasifikasi_id' => $kode000?->id,
                'klasifikasi'         => '000',
                'sifat'               => 'segera',
                'status'              => 'diparaf_sekcam',
                'paraf_oleh'          => $sekcam->id,
                'paraf_waktu'         => now()->subHours(3),
                'paraf_token'         => 'DEMO-PARAF-SEKCAM-01',
                'keterangan'          => 'Sudah diparaf Sekcam, menunggu disposisi Camat',
                'tujuan_unit_id'      => $kecamatan?->id,
            ],
            [
                'nomor_surat'         => '400/275/DINSOS/XI/2026',
                'asal_surat'          => 'Dinas Sosial Kota Makassar',
                'perihal'             => 'Verifikasi Data Penerima Bantuan Sosial Tingkat Kelurahan',
                'kode_klasifikasi_id' => $kode400?->id,
                'klasifikasi'         => '400',
                'sifat'               => 'biasa',
                'status'              => 'didistribusikan',
                'paraf_oleh'          => $sekcam->id,
                'paraf_waktu'         => now()->subDays(1),
                'paraf_token'         => 'DEMO-PARAF-SEKCAM-02',
                'disposisi_oleh'      => $camat->id,
                'disposisi_waktu'     => now()->subHours(20),
                'disposisi_token'     => 'DEMO-DISP-CAMAT-01',
                'tujuan_disposisi'    => 'Seksi Pemerintahan & 11 Lurah se-Kecamatan Tamalate',
                'tujuan_unit_id'      => $seksiPem?->id,
                'disposisi'           => 'Segera koordinasikan dengan kelurahan untuk validasi data lapangan.',
                'distribusi_oleh'     => $operatorKec->id,
                'distribusi_waktu'    => now()->subHours(18),
                'keterangan'          => 'Telah didistribusikan ke unit tujuan',
            ],
        ];

        foreach ($suratMasukSamples as $idx => $item) {
            if (! SuratMasuk::where('nomor_surat', $item['nomor_surat'])->exists()) {
                $nextAgenda++;
                SuratMasuk::create(array_merge($item, [
                    'no_agenda'      => $nextAgenda,
                    'nomor_agenda'   => 'SM/' . now()->year . '/' . str_pad((string) $nextAgenda, 4, '0', STR_PAD_LEFT),
                    'tanggal_surat'  => now()->subDays(3 - $idx)->toDateString(),
                    'tanggal_terima' => now()->subDays(2 - min($idx, 2))->toDateString(),
                    'tujuan'         => 'Camat Tamalate',
                    'dicatat_oleh'   => $operatorKec->id,
                ]));
            }
        }

        // ── 3. Data Dummy Surat Keluar (Standar Pemkot Makassar) ────────────
        $tahun = (int) now()->year;
        $suratKeluarSamples = [
            [
                'kode'             => $kode800,
                'instansi_tujuan'  => 'Kepala BKPSDMD Kota Makassar',
                'perihal'          => 'Pengantar Usulan Kenaikan Gaji Berkala Pegawai Kecamatan Tamalate',
                'penanggung_jawab' => 'Camat Tamalate',
                'keterangan'       => 'Lampiran daftar nominatif 5 pegawai',
                'tgl'              => now()->subDays(2),
            ],
            [
                'kode'             => $kode000,
                'instansi_tujuan'  => 'Seluruh Lurah se-Kecamatan Tamalate',
                'perihal'          => 'Instruksi Pelaksanaan Apel Pagi dan Sore Terintegrasi SIP-TAMALATE',
                'penanggung_jawab' => 'Sekretaris Camat Tamalate',
                'keterangan'       => 'Wajib bagi PNS, PPPK Penuh Waktu, dan PPPK Paruh Waktu',
                'tgl'              => now()->subDay(),
            ],
            [
                'kode'             => $kode400,
                'instansi_tujuan'  => 'Kepala Dinas Dukcapil Kota Makassar',
                'perihal'          => 'Laporan Rekapitulasi Layanan Kependudukan Kecamatan Tamalate',
                'penanggung_jawab' => 'Kepala Seksi Pemerintahan',
                'keterangan'       => 'Periode bulan berjalan',
                'tgl'              => now(),
            ],
        ];

        foreach ($suratKeluarSamples as $item) {
            if (! SuratKeluar::where('perihal', $item['perihal'])->exists()) {
                $noUrut = SuratKeluar::nextNoUrut($tahun);
                $kodeStr = $item['kode']?->kode ?? '000';
                $nomorSurat = SuratKeluar::formatNomorSurat($kodeStr, $noUrut, $item['tgl']);

                SuratKeluar::create([
                    'no_urut'             => $noUrut,
                    'tahun'               => $tahun,
                    'kode_klasifikasi_id' => $item['kode']?->id,
                    'nomor_surat'         => $nomorSurat,
                    'instansi_tujuan'     => $item['instansi_tujuan'],
                    'tgl_surat'           => $item['tgl']->toDateString(),
                    'perihal'             => $item['perihal'],
                    'penanggung_jawab'    => $item['penanggung_jawab'],
                    'keterangan'          => $item['keterangan'],
                    'dicatat_oleh'        => $operatorKec->id,
                ]);
            }
        }

        // ── 4. Data Dummy Pengajuan Kepegawaian (Alur Kelurahan & Kecamatan) ─
        $pengajuanSamples = [
            [
                'nomor_pengajuan'  => 'KEP-DEMO-KEL-001',
                'pegawai_id'       => $pegawaiKel->id,
                'unit_kerja_id'    => $kelMaccini?->id,
                'asal_wilayah'     => 'kelurahan',
                'jenis_layanan'    => 'cuti',
                'kode_klasifikasi' => '800.1.11.2',
                'status'           => 'approval_lurah',
                'meja_saat_ini'    => PengajuanKepegawaian::MEJA_LABELS['approval_lurah'],
                'data_layanan'     => [
                    'kode_cuti'       => '800.1.11.2',
                    'jenis_cuti'      => 'Cuti Tahunan',
                    'tanggal_mulai'   => now()->addDays(5)->toDateString(),
                    'tanggal_selesai' => now()->addDays(7)->toDateString(),
                    'lama_hari'       => 3,
                    'alasan'          => 'Keperluan keluarga di luar kota',
                    'alamat_cuti'     => 'Jl. Dg Tata Raya, Makassar',
                    'no_hp'           => '081241009988',
                ],
                'logs' => [
                    ['dari' => null, 'ke' => 'verifikasi_operator_kelurahan', 'aktor' => $pegawaiKel->id, 'aksi' => 'ajukan', 'catatan' => 'Pengajuan Cuti Tahunan dikirim dari Kelurahan Maccini Sombala.'],
                    ['dari' => 'verifikasi_operator_kelurahan', 'ke' => 'approval_lurah', 'aktor' => $operatorKel->id, 'aksi' => 'setuju', 'catatan' => 'Berkas lengkap, diteruskan ke meja Lurah Maccini Sombala.'],
                ],
            ],
            [
                'nomor_pengajuan'  => 'KEP-DEMO-KEC-002',
                'pegawai_id'       => $pegawaiKec->id,
                'unit_kerja_id'    => $seksiPem?->id,
                'asal_wilayah'     => 'kecamatan',
                'jenis_layanan'    => 'kgb',
                'kode_klasifikasi' => '800.1.11.13',
                'status'           => 'antrean_operator_kecamatan',
                'meja_saat_ini'    => PengajuanKepegawaian::MEJA_LABELS['antrean_operator_kecamatan'],
                'data_layanan'     => [
                    'tmt_kgb_terakhir' => now()->subYears(2)->toDateString(),
                    'tmt_kgb_baru'     => now()->addMonth()->startOfMonth()->toDateString(),
                    'gaji_pokok_lama'  => 3150000,
                    'gaji_pokok_baru'  => 3280000,
                    'masa_kerja_tahun' => 7,
                    'masa_kerja_bulan' => 8,
                ],
                'logs' => [
                    ['dari' => null, 'ke' => 'antrean_operator_kecamatan', 'aktor' => $pegawaiKec->id, 'aksi' => 'ajukan', 'catatan' => 'Pengajuan KGB unit Kecamatan langsung masuk ke antrean Operator Subbag Umum & Kepegawaian.'],
                ],
            ],
            [
                'nomor_pengajuan'  => 'KEP-DEMO-KEC-003',
                'pegawai_id'       => $pegawaiKec->id,
                'unit_kerja_id'    => $seksiPem?->id,
                'asal_wilayah'     => 'kecamatan',
                'jenis_layanan'    => 'cuti',
                'kode_klasifikasi' => '800.1.11.4',
                'status'           => 'selesai',
                'meja_saat_ini'    => PengajuanKepegawaian::MEJA_LABELS['selesai'],
                'data_layanan'     => [
                    'kode_cuti'       => '800.1.11.4',
                    'jenis_cuti'      => 'Cuti Sakit',
                    'tanggal_mulai'   => now()->subDays(10)->toDateString(),
                    'tanggal_selesai' => now()->subDays(9)->toDateString(),
                    'lama_hari'       => 2,
                    'alasan'          => 'Rawat jalan sesuai surat keterangan dokter',
                    'alamat_cuti'     => 'Jl. Alauddin, Tamalate, Makassar',
                    'no_hp'           => '081355667788',
                ],
                'logs' => [
                    ['dari' => null, 'ke' => 'antrean_operator_kecamatan', 'aktor' => $pegawaiKec->id, 'aksi' => 'ajukan', 'catatan' => 'Pengajuan Cuti Sakit dikirim.'],
                    ['dari' => 'antrean_operator_kecamatan', 'ke' => 'approval_kasubag', 'aktor' => $operatorKec->id, 'aksi' => 'setuju', 'catatan' => 'Surat keterangan dokter terverifikasi.'],
                    ['dari' => 'approval_kasubag', 'ke' => 'approval_sekcam', 'aktor' => $kasubag->id, 'aksi' => 'setuju', 'catatan' => 'Disetujui Kasubag Umum & Kepegawaian.'],
                    ['dari' => 'approval_sekcam', 'ke' => 'approval_camat', 'aktor' => $sekcam->id, 'aksi' => 'setuju', 'catatan' => 'Diparaf Sekretaris Camat.'],
                    ['dari' => 'approval_camat', 'ke' => 'selesai', 'aktor' => $camat->id, 'aksi' => 'setuju', 'catatan' => 'Disetujui Camat Tamalate. Form Cuti siap diunduh.'],
                ],
            ],
        ];

        foreach ($pengajuanSamples as $sample) {
            if (! PengajuanKepegawaian::where('nomor_pengajuan', $sample['nomor_pengajuan'])->exists()) {
                $logs = $sample['logs'];
                unset($sample['logs']);
                $item = PengajuanKepegawaian::create($sample);
                foreach ($logs as $i => $log) {
                    LogStatusPengajuan::create([
                        'pengajuan_kepegawaian_id' => $item->id,
                        'status_dari'              => $log['dari'],
                        'status_ke'                => $log['ke'],
                        'meja'                     => PengajuanKepegawaian::MEJA_LABELS[$log['ke']] ?? $log['ke'],
                        'aktor_id'                 => $log['aktor'],
                        'aksi'                     => $log['aksi'],
                        'catatan'                  => $log['catatan'],
                        'waktu'                    => now()->subHours((count($logs) - $i) * 4),
                    ]);
                }
            }
        }

        // ── 5. Data Dummy Presensi Apel & Contoh TK untuk Pembinaan ─────────
        $today = Carbon::today()->toDateString();

        $presensiSamples = [
            [
                'user'             => $pegawaiKec,
                'tanggal'          => $today,
                'sesi'             => 'pagi',
                'status_kehadiran' => 'hadir',
                'status_validasi'  => 'valid',
                'jam'              => '07:18:00',
                'keterangan'       => 'Hadir apel pagi halaman Kantor Camat Tamalate',
                'validator'        => $operatorKec->id,
            ],
            [
                'user'             => $pegawaiKel,
                'tanggal'          => $today,
                'sesi'             => 'pagi',
                'status_kehadiran' => 'izin',
                'status_validasi'  => 'pending',
                'jam'              => '07:22:00',
                'keterangan'       => 'Izin mendampingi keluarga periksa kesehatan',
                'validator'        => null,
            ],
            [
                'user'             => $pppkParuhWaktu,
                'tanggal'          => $today,
                'sesi'             => 'pagi',
                'status_kehadiran' => 'hadir',
                'status_validasi'  => 'pending',
                'jam'              => '07:15:00',
                'keterangan'       => 'Siap validasi visual foto apel oleh Operator',
                'validator'        => null,
            ],
        ];

        foreach ($presensiSamples as $p) {
            $exists = PresensiApel::where('pegawai_id', $p['user']->id)
                ->whereDate('tanggal', $p['tanggal'])
                ->where('sesi', $p['sesi'])
                ->exists();

            if (! $exists) {
                PresensiApel::create([
                    'pegawai_id'       => $p['user']->id,
                    'user_id'          => $p['user']->id,
                    'tanggal'          => $p['tanggal'],
                    'sesi'             => $p['sesi'],
                    'status_kehadiran' => $p['status_kehadiran'],
                    'jam'              => $p['jam'],
                    'waktu_absen'      => Carbon::parse("{$p['tanggal']} {$p['jam']}"),
                    'foto_path'        => null,
                    'foto_apel'        => null,
                    'keterangan'       => $p['keterangan'],
                    'status_validasi'  => $p['status_validasi'],
                    'validasi_oleh'    => $p['validator'],
                    'waktu_validasi'   => $p['validator'] ? now() : null,
                ]);
            }
        }

        // Tambahkan 3 catatan Tanpa Keterangan (TK) pada bulan berjalan untuk PPPK Paruh Waktu
        // agar menu Rekapitulasi & Peringatan Pembinaan langsung menampilkan tombol Surat Panggilan.
        foreach ([1, 2, 3] as $hariLalu) {
            $tgl = Carbon::today()->subDays($hariLalu);
            if ($tgl->month === Carbon::today()->month) {
                $exists = PresensiApel::where('pegawai_id', $pppkParuhWaktu->id)
                    ->whereDate('tanggal', $tgl->toDateString())
                    ->where('sesi', 'sore')
                    ->exists();

                if (! $exists) {
                    PresensiApel::create([
                        'pegawai_id'       => $pppkParuhWaktu->id,
                        'user_id'          => $pppkParuhWaktu->id,
                        'tanggal'          => $tgl->toDateString(),
                        'sesi'             => 'sore',
                        'status_kehadiran' => 'tanpa_keterangan',
                        'jam'              => '16:00:00',
                        'waktu_absen'      => $tgl->copy()->setTime(16, 0),
                        'keterangan'       => 'Tidak mengikuti apel sore tanpa keterangan (data demo pembinaan)',
                        'status_validasi'  => 'valid',
                        'validasi_oleh'    => $operatorKec->id,
                        'waktu_validasi'   => $tgl->copy()->setTime(17, 0),
                    ]);
                }
            }
        }

        $this->command?->info('✓ DemoTamalateSeeder selesai: akun demo lengkap & data transaksi realistis siap digunakan.');
    }
}
