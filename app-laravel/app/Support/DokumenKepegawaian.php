<?php

namespace App\Support;

use App\Models\Pengajuan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Merakit isi dokumen resmi (SK / Surat Izin) dari sebuah Pengajuan.
 *
 * Kelas ini hanya menyusun struktur teks (kop, judul, konsideran menimbang/
 * mengingat, diktum memutuskan, data pegawai, penandatangan). Tampilan akhir
 * dirender oleh view dokumen/pengajuan.blade.php lalu dicetak ke PDF.
 */
class DokumenKepegawaian
{
    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /** Dasar hukum umum yang dipakai pada konsideran "Mengingat". */
    private const DASAR_HUKUM = [
        'Undang-Undang Nomor 20 Tahun 2023 tentang Aparatur Sipil Negara;',
        'Peraturan Pemerintah Nomor 11 Tahun 2017 tentang Manajemen Pegawai Negeri Sipil sebagaimana telah diubah dengan Peraturan Pemerintah Nomor 17 Tahun 2020;',
        'Peraturan Pemerintah Nomor 7 Tahun 1977 tentang Peraturan Gaji Pegawai Negeri Sipil beserta perubahannya;',
    ];

    /** Susun seluruh data dokumen untuk sebuah pengajuan. */
    public static function untuk(Pengajuan $p): array
    {
        $p->loadMissing(['pegawai.unitKerja', 'riwayat.pejabat']);
        $pegawai = $p->pegawai;
        $d = $p->data_form ?? [];

        return [
            'kop'        => self::kop(),
            'judul'      => self::judul($p->jenis_layanan)['judul'],
            'subjudul'   => self::judul($p->jenis_layanan)['subjudul'],
            'nomor'      => $p->nomor_agenda ?: $p->nomor_ajuan,
            'menimbang'  => self::menimbang($p, $pegawai, $d),
            'mengingat'  => self::mengingat($p->jenis_layanan),
            'memutuskan' => self::memutuskan($p, $pegawai, $d),
            'data'       => self::dataPegawai($p, $pegawai, $d),
            'tembusan'   => self::tembusan($p->jenis_layanan),
            'ttd'        => self::penandatangan($p),
            'digital_signature' => $p->isDitandatangani() ? [
                'token' => $p->tanda_tangan_token,
                'waktu' => $p->tanda_tangan_waktu,
                'waktu_wita' => $p->tanda_tangan_waktu ? $p->tanda_tangan_waktu->timezone('Asia/Makassar')->format('d/m/Y H:i') . ' WITA' : '-',
                'pejabat_nama' => $p->tandaTanganPejabat->nama ?? ($p->tanda_tangan_payload['penandatangan']['nama'] ?? '-'),
                'pejabat_nip' => $p->tandaTanganPejabat->nip ?? ($p->tanda_tangan_payload['penandatangan']['nip'] ?? '-'),
                'pejabat_jabatan' => $p->tandaTanganPejabat->jabatan ?? ($p->tanda_tangan_payload['penandatangan']['jabatan'] ?? 'Camat/Lurah'),
                'url_verifikasi' => url('/dokumen/verifikasi/' . $p->tanda_tangan_token),
            ] : null,
        ];
    }

    private static function kop(): array
    {
        return [
            'induk'   => config('dokumen.instansi_induk'),
            'instansi'=> config('dokumen.instansi'),
            'alamat'  => trim(implode(' ', array_filter([
                config('dokumen.alamat'),
                config('dokumen.kode_pos') ? 'Kode Pos ' . config('dokumen.kode_pos') : null,
            ]))),
            'kontak'  => trim(implode(' · ', array_filter([
                config('dokumen.telepon') ? 'Telp. ' . config('dokumen.telepon') : null,
                config('dokumen.email'),
            ]))),
            'logo'    => config('dokumen.logo'),
        ];
    }

    private static function judul(string $jenis): array
    {
        return match ($jenis) {
            'kgb' => [
                'judul'    => 'SURAT KEPUTUSAN',
                'subjudul' => 'TENTANG\nPEMBERIAN KENAIKAN GAJI BERKALA',
            ],
            'kenaikan_pangkat' => [
                'judul'    => 'SURAT KEPUTUSAN',
                'subjudul' => 'TENTANG\nUSULAN KENAIKAN PANGKAT',
            ],
            'pensiun' => [
                'judul'    => 'SURAT KEPUTUSAN',
                'subjudul' => 'TENTANG\nUSULAN PEMBERHENTIAN DENGAN HAK PENSIUN',
            ],
            default => [
                'judul'    => 'SURAT IZIN',
                'subjudul' => 'TENTANG\nIZIN CUTI PEGAWAI',
            ],
        };
    }

    private static function menimbang(Pengajuan $p, ?User $pegawai, array $d): array
    {
        $nama = $pegawai->nama ?? '-';
        $nip = $pegawai->nip ?? '-';
        $label = LayananKepegawaian::label($p->jenis_layanan);

        $umum = [
            "a. bahwa {$nama} (NIP. {$nip}) telah mengajukan permohonan {$label} dan telah melalui proses verifikasi serta persetujuan berjenjang sesuai ketentuan;",
            'b. bahwa untuk tertib administrasi kepegawaian, permohonan sebagaimana dimaksud huruf a perlu ditetapkan dengan surat keputusan;',
        ];

        $khusus = match ($p->jenis_layanan) {
            'kgb' => ['c. bahwa pegawai yang bersangkutan telah memenuhi syarat masa kerja untuk memperoleh Kenaikan Gaji Berkala terhitung mulai tanggal ' . self::tglPanjang($d['tanggal_kgb_baru'] ?? null) . ';'],
            'kenaikan_pangkat' => ['c. bahwa pegawai yang bersangkutan telah memenuhi syarat masa kerja dalam pangkat terakhir untuk diusulkan naik pangkat setingkat lebih tinggi;'],
            'pensiun' => ['c. bahwa pegawai yang bersangkutan telah mencapai Batas Usia Pensiun sebagaimana ketentuan peraturan perundang-undangan;'],
            default => ['c. bahwa pegawai yang bersangkutan memenuhi persyaratan untuk diberikan izin cuti;'],
        };

        return array_merge($umum, $khusus);
    }

    private static function mengingat(string $jenis): array
    {
        $dasar = self::DASAR_HUKUM;

        if ($jenis === 'cuti') {
            $dasar[] = 'Peraturan Badan Kepegawaian Negara Nomor 24 Tahun 2017 tentang Tata Cara Pemberian Cuti Pegawai Negeri Sipil;';
        }

        return array_values(array_map(
            fn ($i, $t) => ($i + 1) . '. ' . $t,
            array_keys($dasar),
            $dasar
        ));
    }

    /** Bagian "MEMUTUSKAN" — diktum keputusan per jenis layanan. */
    private static function memutuskan(Pengajuan $p, ?User $pegawai, array $d): array
    {
        $nama = $pegawai->nama ?? '-';
        $nip = $pegawai->nip ?? '-';

        $judulTeks = str_replace('\n', ' ', self::judul($p->jenis_layanan)['subjudul']);
        $menetapkan = 'Menetapkan: ' . preg_replace('/^TENTANG\s*/i', '', $judulTeks) . '.';

        $diktum = match ($p->jenis_layanan) {
            'kgb' => [
                'KESATU'  => "Memberikan Kenaikan Gaji Berkala kepada {$nama} (NIP. {$nip}) dalam golongan/ruang " . ($d['golongan_ruang'] ?? '-') . '.',
                'KEDUA'   => 'Kenaikan Gaji Berkala sebagaimana diktum KESATU berlaku terhitung mulai tanggal ' . self::tglPanjang($d['tanggal_kgb_baru'] ?? null) . '.',
                'KETIGA'  => 'Keputusan ini mulai berlaku pada tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.',
            ],
            'kenaikan_pangkat' => [
                'KESATU'  => "Mengusulkan Kenaikan Pangkat {$nama} (NIP. {$nip}) dari pangkat/golongan " . ($d['pangkat_gol_sekarang'] ?? '-') . ' menjadi ' . ($d['pangkat_gol_diusulkan'] ?? '-') . '.',
                'KEDUA'   => 'Usulan kenaikan pangkat sebagaimana diktum KESATU berlaku terhitung mulai tanggal ' . self::tglPanjang($d['tmt_pangkat_diusulkan'] ?? null) . ', untuk selanjutnya diproses oleh BKPSDMD Kota Makassar.',
                'KETIGA'  => 'Keputusan ini mulai berlaku pada tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.',
            ],
            'pensiun' => [
                'KESATU'  => "Mengusulkan pemberhentian dengan hak pensiun {$nama} (NIP. {$nip}) karena " . (LayananKepegawaian::JENIS_PENSIUN[$d['jenis_pensiun'] ?? ''] ?? 'Batas Usia Pensiun') . '.',
                'KEDUA'   => 'Pemberhentian sebagaimana diktum KESATU berlaku terhitung mulai tanggal ' . self::tglPanjang($d['tmt_pensiun'] ?? null) . ', untuk selanjutnya diproses oleh BKPSDMD Kota Makassar.',
                'KETIGA'  => 'Keputusan ini mulai berlaku pada tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.',
            ],
            default => [
                'KESATU'  => "Memberikan izin cuti kepada {$nama} (NIP. {$nip}) selama " . ($d['lama_hari'] ?? '-') . ' hari kerja.',
                'KEDUA'   => 'Izin cuti sebagaimana diktum KESATU berlaku sejak tanggal ' . self::tglPanjang($d['tanggal_mulai'] ?? null) . ' sampai dengan ' . self::tglPanjang($d['tanggal_selesai'] ?? null) . '.',
                'KETIGA'  => 'Selama menjalankan cuti, pegawai yang bersangkutan wajib menyerahkan pekerjaan kepada atasan langsung dan dapat dihubungi melalui nomor ' . ($d['no_telp'] ?? '-') . '.',
                'KEEMPAT' => 'Keputusan ini mulai berlaku pada tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.',
            ],
        };

        return array_merge([$menetapkan], self::prefiksDiktum($diktum));
    }

    /** Ubah ['KESATU' => 'isi', ...] menjadi ['KESATU: isi', ...] (list beralamat numerik). */
    private static function prefiksDiktum(array $diktum): array
    {
        $hasil = [];
        foreach ($diktum as $kunci => $isi) {
            $hasil[] = $kunci . ': ' . $isi;
        }

        return $hasil;
    }

    /** Tabel identitas pegawai yang melekat pada dokumen. */
    private static function dataPegawai(Pengajuan $p, ?User $pegawai, array $d): array
    {
        $rows = [
            'Nama'            => $pegawai->nama ?? '-',
            'NIP'             => $pegawai->nip ?? '-',
            'Tempat/Tgl Lahir'=> trim(implode(', ', array_filter([
                $pegawai->tempat_lahir,
                self::tglPanjang(optional($pegawai->tanggal_lahir)->toDateString()),
            ]))) ?: '-',
            'Jabatan'         => $pegawai->jabatan ?: '-',
            'Pangkat/Golongan'=> $pegawai->pangkat_gol ?: '-',
            'Unit Kerja'      => optional($pegawai->unitKerja)->nama ?? '-',
            'Masa Kerja'      => $pegawai->masaKerjaTeks(),
        ];

        $tambahan = match ($p->jenis_layanan) {
            'kgb' => [
                'TMT Golongan Terakhir' => self::tglPanjang($d['tmt_golongan_lama'] ?? null),
                'TMT KGB Baru'          => self::tglPanjang($d['tanggal_kgb_baru'] ?? null),
            ],
            'kenaikan_pangkat' => [
                'Pangkat Diusulkan'     => $d['pangkat_gol_diusulkan'] ?? '-',
                'TMT Pangkat Diusulkan' => self::tglPanjang($d['tmt_pangkat_diusulkan'] ?? null),
            ],
            'pensiun' => [
                'Jenis Pensiun'         => LayananKepegawaian::JENIS_PENSIUN[$d['jenis_pensiun'] ?? ''] ?? '-',
                'Batas Usia Pensiun'    => isset($d['bup_tahun']) ? $d['bup_tahun'] . ' tahun' : '-',
                'TMT Pensiun'           => self::tglPanjang($d['tmt_pensiun'] ?? null),
            ],
            'cuti' => [
                'Jenis Cuti'            => \App\Models\JenisCuti::find($d['jenis_cuti_id'] ?? null)?->nama ?? '-',
                'Lama Cuti'             => ($d['lama_hari'] ?? '-') . ' hari',
                'Periode Cuti'          => self::tglPanjang($d['tanggal_mulai'] ?? null) . ' s.d. ' . self::tglPanjang($d['tanggal_selesai'] ?? null),
            ],
            default => [],
        };

        return array_merge($rows, $tambahan);
    }

    private static function tembusan(string $jenis): array
    {
        $dasar = ['BKPSDMD Kota Makassar', 'Sekretaris Kecamatan Tamalate', 'Kepala Seksi terkait', 'Yang bersangkutan'];

        return $jenis === 'cuti'
            ? ['Sekretaris Kecamatan Tamalate', 'Atasan langsung pegawai', 'Yang bersangkutan']
            : $dasar;
    }

    /** Penandatangan: user camat_lurah di basis data, atau config bila tak ada. */
    private static function penandatangan(Pengajuan $p): array
    {
        $cfg = config('dokumen.penandatangan');

        $camatRoleId = Role::where('nama_role', 'camat_lurah')->value('id');
        $camat = $camatRoleId ? User::where('role_id', $camatRoleId)->orderBy('id')->first() : null;

        $pejabat = $p->tandaTanganPejabat;
        $nama = $pejabat ? $pejabat->nama : ($cfg['nama'] ?: ($camat->nama ?? null));
        $nip  = $pejabat ? $pejabat->nip : ($cfg['nip'] ?: ($camat->nip ?? null));
        $jabatan = $pejabat ? ($pejabat->jabatan ?: $cfg['jabatan']) : $cfg['jabatan'];

        // Tanggal tanda tangan: waktu persetujuan akhir / tanda tangan bila ada, selain itu hari ini.
        $final = $p->riwayat->first(fn ($r) => $r->tahap === 'approval_final' && $r->keputusan === 'setuju');
        $waktu = $p->tanda_tangan_waktu ?? ($final ? $final->waktu : null);

        return [
            'jabatan' => $jabatan,
            'nama'    => $nama,
            'nip'     => $nip,
            'kota'    => config('dokumen.kota_ttd'),
            'tanggal' => self::tglPanjang($waktu ? $waktu->toDateString() : now()->toDateString()),
        ];
    }

    /** Format tanggal panjang Indonesia, mis. "9 Oktober 2026". */
    public static function tglPanjang(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        $c = Carbon::parse($date);

        return $c->day . ' ' . (self::BULAN[$c->month] ?? '') . ' ' . $c->year;
    }
}
