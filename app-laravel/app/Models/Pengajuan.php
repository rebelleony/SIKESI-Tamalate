<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = [
        'nomor_ajuan', 'pegawai_id', 'unit_kerja_id', 'jenis_layanan',
        'data_form', 'status', 'tanggal_ajuan', 'catatan', 'file_output', 'nomor_agenda',
    ];

    protected $casts = [
        'data_form' => 'array',
        'tanggal_ajuan' => 'date',
    ];

    // Rantai persetujuan berjenjang sesuai SOP mitra:
    // verifikasi (Kasubag) -> tier1 (Kasi/Kasubag) -> tier2 (Sekcam) -> final (Camat) -> pengiriman (BKPSDMD).
    public const STATUS = [
        'draf' => 'Draf',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'perlu_revisi' => 'Perlu Revisi',
        'menunggu_approval_tier1' => 'Menunggu Persetujuan Kasi/Kasubag',
        'menunggu_approval_tier2' => 'Menunggu Paraf Sekretaris Camat',
        'menunggu_approval_final' => 'Menunggu Persetujuan Camat/Lurah',
        'menunggu_pengiriman' => 'Disetujui - Menunggu Dikirim ke BKPSDMD',
        'dikirim_bkpsdm' => 'Dikirim ke BKPSDMD / Selesai',
        'disetujui' => 'Disetujui / Selesai',
        'ditolak_tier1' => 'Ditolak Kasi/Kasubag',
        'ditolak_tier2' => 'Ditolak Sekretaris Camat',
        'ditolak_final' => 'Ditolak Camat/Lurah',
    ];

    // Status yang menandakan usulan sudah final (bukan lagi dalam antrean approval).
    public const STATUS_SELESAI = ['dikirim_bkpsdm', 'disetujui'];

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function berkas()
    {
        return $this->hasMany(Berkas::class)->latest();
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatApproval::class)->orderBy('waktu');
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
