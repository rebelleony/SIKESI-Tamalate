<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = [
        'nomor_ajuan', 'pegawai_id', 'unit_kerja_id', 'jenis_layanan',
        'data_form', 'status', 'tanggal_ajuan', 'catatan', 'file_output', 'nomor_agenda',
        'tanda_tangan_pejabat_id', 'tanda_tangan_waktu', 'tanda_tangan_token', 'tanda_tangan_payload',
        'distribusi_admin_id', 'distribusi_waktu', 'distribusi_catatan',
    ];

    protected $casts = [
        'data_form' => 'array',
        'tanggal_ajuan' => 'date',
        'tanda_tangan_payload' => 'array',
        'tanda_tangan_waktu' => 'datetime',
        'distribusi_waktu' => 'datetime',
    ];

    // Alur 5 role:
    // Pegawai (aju) -> Verifikator (periksa/revisi) -> Kasi/Kasubag (persetujuan 1) 
    // -> Camat/Lurah (persetujuan akhir & tanda tangan digital) 
    // -> Super Admin (pemeriksaan administrasi & distribusi hasil kepada pegawai).
    public const STATUS = [
        'draf' => 'Draf',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'perlu_revisi' => 'Perlu Revisi',
        'menunggu_approval_tier1' => 'Menunggu Persetujuan Kasi/Kasubag',
        'ditolak_tier1' => 'Ditolak Kasi/Kasubag',
        'menunggu_approval_final' => 'Menunggu Persetujuan Camat/Lurah',
        'ditolak_final' => 'Ditolak Camat/Lurah',
        'disetujui_dan_ditandatangani' => 'Disetujui dan Ditandatangani',
        'selesai' => 'Selesai (Didistribusikan)',
        'selesai_ditolak' => 'Selesai (Penolakan Disampaikan)',
        // Backward compatibility
        'disetujui' => 'Disetujui / Selesai (Legacy)',
        'menunggu_approval_tier2' => 'Menunggu Paraf (Legacy)',
        'menunggu_pengiriman' => 'Menunggu Pengiriman (Legacy)',
        'dikirim_bkpsdm' => 'Dikirim ke BKPSDMD / Selesai (Legacy)',
        'ditolak_tier2' => 'Ditolak (Legacy)',
    ];

    // Status yang menandakan pengajuan telah selesai didistribusikan oleh Super Admin ke Pegawai
    public const STATUS_SELESAI = ['selesai', 'disetujui', 'dikirim_bkpsdm'];
    public const STATUS_DITOLAK = ['ditolak_tier1', 'ditolak_final', 'selesai_ditolak'];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function tandaTanganPejabat()
    {
        return $this->belongsTo(User::class, 'tanda_tangan_pejabat_id');
    }

    public function distribusiAdmin()
    {
        return $this->belongsTo(User::class, 'distribusi_admin_id');
    }

    public function berkas()
    {
        return $this->hasMany(Berkas::class)->latest();
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatApproval::class)->orderBy('waktu');
    }

    public function isDitandatangani(): bool
    {
        return !empty($this->tanda_tangan_token) && !empty($this->tanda_tangan_waktu);
    }

    public function isSelesai(): bool
    {
        return in_array($this->status, self::STATUS_SELESAI, true);
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function generateNomor(): string
    {
        return sprintf('PKG/%s/%s/%d', strtoupper(substr($this->jenis_layanan, 0, 3)), now()->format('m'), now()->year)
            . '/' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}
