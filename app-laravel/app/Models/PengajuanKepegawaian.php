<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanKepegawaian extends Model
{
    protected $table = 'pengajuan_kepegawaian';

    protected $fillable = [
        'nomor_pengajuan',
        'pegawai_id',
        'unit_kerja_id',
        'asal_wilayah',
        'jenis_layanan',
        'kode_klasifikasi',
        'status',
        'meja_saat_ini',
        'data_layanan',
        'checklist_kelengkapan',
        'nomor_nominatif',
        'tanggal_nominatif',
        'catatan_terakhir',
    ];

    protected $casts = [
        'data_layanan'          => 'array',
        'checklist_kelengkapan' => 'array',
        'tanggal_nominatif'     => 'date',
    ];

    public const JENIS_LAYANAN = [
        'cuti'             => 'Cuti Pegawai',
        'kgb'              => 'Kenaikan Gaji Berkala (KGB)',
        'kenaikan_pangkat' => 'Kenaikan Pangkat',
        'pensiun'          => 'Pensiun PNS',
    ];

    public const KODE_CUTI = [
        '800.1.11.2' => 'Cuti Tahunan',
        '800.1.11.3' => 'Cuti Besar',
        '800.1.11.4' => 'Cuti Sakit',
        '800.1.11.5' => 'Cuti Melahirkan',
        '800.1.11.6' => 'Cuti Karena Alasan Penting',
        '800.1.11.7' => 'Cuti di Luar Tanggungan Negara',
    ];

    public const STATUS_LABELS = [
        'verifikasi_operator_kelurahan' => 'Verifikasi Operator Kelurahan',
        'approval_lurah'                => 'Persetujuan / TTD Lurah',
        'antrean_operator_kecamatan'    => 'Antrean Operator Subbag Umum & Kepegawaian',
        'approval_kasubag'              => 'Persetujuan Kasubag Umum & Kepegawaian',
        'approval_sekcam'               => 'Pemeriksaan / Paraf Sekretaris Camat',
        'approval_camat'                => 'Persetujuan Akhir Camat Tamalate',
        'selesai'                       => 'Selesai',
        'diteruskan_bkpsdm'             => 'Diteruskan ke BKPSDMD Kota Makassar',
        'perlu_revisi'                  => 'Perlu Revisi Pemohon',
        'ditolak'                       => 'Ditolak',
    ];

    public const MEJA_LABELS = [
        'verifikasi_operator_kelurahan' => 'Operator Kelurahan',
        'approval_lurah'                => 'Lurah',
        'antrean_operator_kecamatan'    => 'Operator Subbag Umum & Kepegawaian Kecamatan',
        'approval_kasubag'              => 'Kasubag Umum & Kepegawaian',
        'approval_sekcam'               => 'Sekretaris Camat',
        'approval_camat'                => 'Camat Tamalate',
        'selesai'                       => 'Selesai (Dapat Diunduh)',
        'diteruskan_bkpsdm'             => 'BKPSDMD Kota Makassar',
        'perlu_revisi'                  => 'Pemohon (Revisi Berkas)',
        'ditolak'                       => 'Ditolak',
    ];

    public const CHECKLIST_TEMPLATE = [
        'kenaikan_pangkat' => [
            'Fotokopi SK CPNS & SK PNS',
            'Fotokopi SK Pangkat Terakhir',
            'Penilaian Prestasi Kerja / SKP 2 Tahun Terakhir',
            'Fotokopi Ijazah Terakhir & Transkrip Nilai',
            'Daftar Riwayat Hidup / Surat Pengantar Unit',
        ],
        'pensiun' => [
            'Surat Permohonan Pensiun / Pengantar Unit Kerja',
            'Fotokopi SK CPNS, SK PNS, & SK Pangkat Terakhir',
            'Fotokopi Kartu Pegawai (KARPEG) & Taspen',
            'Daftar Susunan Keluarga / Fotokopi KK & Akta Nikah',
            'Pas Foto Terbaru & SKP 1 Tahun Terakhir',
        ],
    ];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function lampiran()
    {
        return $this->hasMany(LampiranBerkas::class, 'pengajuan_kepegawaian_id');
    }

    public function logs()
    {
        return $this->hasMany(LogStatusPengajuan::class, 'pengajuan_kepegawaian_id')->orderBy('waktu')->orderBy('id');
    }

    public function labelJenis(): string
    {
        return self::JENIS_LAYANAN[$this->jenis_layanan] ?? $this->jenis_layanan;
    }

    public function labelStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Daftar tahapan berurutan sesuai asal wilayah (Kelurahan vs Kecamatan).
     */
    public function tahapanAlur(): array
    {
        $kelurahanSteps = $this->asal_wilayah === 'kelurahan'
            ? [
                'verifikasi_operator_kelurahan' => 'Operator Kelurahan',
                'approval_lurah'                => 'Lurah',
            ]
            : [];

        $kecamatanSteps = [
            'antrean_operator_kecamatan' => 'Operator Subbag Umum & Kepegawaian',
            'approval_kasubag'           => 'Kasubag Umum & Kepegawaian',
            'approval_sekcam'            => 'Sekretaris Camat',
            'approval_camat'             => 'Camat Tamalate',
            'final'                      => 'Selesai / BKPSDMD',
        ];

        return $kelurahanSteps + $kecamatanSteps;
    }

    public function nextStatus(): ?string
    {
        return match ($this->status) {
            'verifikasi_operator_kelurahan' => 'approval_lurah',
            'approval_lurah'                => 'antrean_operator_kecamatan',
            'antrean_operator_kecamatan'    => 'approval_kasubag',
            'approval_kasubag'              => 'approval_sekcam',
            'approval_sekcam'               => 'approval_camat',
            'approval_camat'                => $this->jenis_layanan === 'cuti' ? 'selesai' : 'diteruskan_bkpsdm',
            default                         => null,
        };
    }

    public static function generateNomor(string $jenis): string
    {
        $prefix = match ($jenis) {
            'cuti'             => '800.1.11',
            'kgb'              => '800.1.11.13',
            'kenaikan_pangkat' => '800.1.3.2',
            'pensiun'          => '800.1.6.6',
            default            => '800',
        };
        $urut = (static::whereYear('created_at', now()->year)->count()) + 1;

        return sprintf('%s/PEG/%d/%04d', $prefix, now()->year, $urut);
    }
}

