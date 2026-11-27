<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use RuntimeException;

class DigitalSignatureService
{
    public function createToken(Pengajuan $pengajuan, User $pejabat, CarbonImmutable $signedAt): string
    {
        return hash_hmac('sha256', $this->signatureMessage(
            $pengajuan,
            $pejabat->id,
            $pejabat->nip,
            $signedAt->timestamp
        ), $this->secret());
    }

    public function isValid(Pengajuan $pengajuan): bool
    {
        if (! $this->isSigned($pengajuan) || ! $pengajuan->tanda_tangan_payload) {
            return false;
        }

        $security = $pengajuan->tanda_tangan_payload['keamanan'] ?? [];
        $signedAt = $security['waktu_utc'] ?? null;
        $signerId = $pengajuan->tanda_tangan_pejabat_id;
        $signerNip = $pengajuan->tanda_tangan_payload['penandatangan']['nip']
            ?? $pengajuan->tandaTanganPejabat?->nip
            ?? null;

        if (! $signedAt || ! $signerId || ! $signerNip) {
            return false;
        }

        try {
            $timestamp = CarbonImmutable::parse($signedAt)->timestamp;
        } catch (\Throwable) {
            return false;
        }

        $timestamps = [$timestamp];
        if ($pengajuan->tanda_tangan_waktu) {
            foreach (range(-2, 2) as $offset) {
                $timestamps[] = $pengajuan->tanda_tangan_waktu->timestamp + $offset;
            }
        }

        foreach (array_unique($timestamps) as $candidate) {
            $expected = $this->createTokenForIdentity($pengajuan, $signerId, $signerNip, $candidate);
            if (hash_equals($expected, (string) $pengajuan->tanda_tangan_token)) {
                return true;
            }
        }

        return false;
    }

    public function qrCodeDataUri(Pengajuan $pengajuan): ?string
    {
        if (! $this->isValid($pengajuan)) {
            return null;
        }

        $qrCode = new QrCode(
            data: url('/dokumen/verifikasi/' . $pengajuan->tanda_tangan_token),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 180,
            margin: 8,
        );

        $png = (new PngWriter())->write($qrCode)->getString();

        return 'data:image/png;base64,' . base64_encode($png);
    }

    public function isSigned(Pengajuan $pengajuan): bool
    {
        return $pengajuan->isDitandatangani()
            && in_array($pengajuan->status, [
                'disetujui_dan_ditandatangani',
                ...Pengajuan::STATUS_SELESAI,
            ], true);
    }

    private function createTokenForIdentity(Pengajuan $pengajuan, int $pejabatId, string $nip, int $timestamp): string
    {
        return hash_hmac('sha256', $this->signatureMessage($pengajuan, $pejabatId, $nip, $timestamp), $this->secret());
    }

    private function signatureMessage(Pengajuan $pengajuan, int $pejabatId, ?string $nip, int $timestamp): string
    {
        return implode('|', [
            $pengajuan->id,
            $pengajuan->nomor_ajuan,
            $pengajuan->pegawai_id,
            $pejabatId,
            $nip,
            $timestamp,
        ]);
    }

    private function secret(): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new RuntimeException('APP_KEY harus dikonfigurasi untuk tanda tangan digital.');
        }

        return $key;
    }
}