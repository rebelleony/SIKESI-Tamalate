<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Services\DigitalSignatureService;
use App\Support\DokumenKepegawaian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DokumenController extends Controller
{
    /**
     * Render SK / surat izin pengajuan menjadi PDF.
     * Dapat diunduh oleh Pegawai setelah didistribusikan (selesai), 
     * atau dipratinjau oleh Super Admin setelah ditandatangani.
     */
    public function pengajuan(Request $request, Pengajuan $pengajuan)
    {
        $this->authorize($request, $pengajuan);

        $siapCetak = in_array($pengajuan->status, Pengajuan::STATUS_SELESAI, true)
            || ($request->user()->hasRole('super_admin') && $pengajuan->status === 'disetujui_dan_ditandatangani');

        abort_unless(
            $siapCetak,
            403,
            'Dokumen resmi baru dapat dicetak/diunduh setelah pengajuan selesai didistribusikan oleh Super Admin.'
        );

        $doc = DokumenKepegawaian::untuk($pengajuan);
        $nama = $this->namaFile($pengajuan);

        $pdf = Pdf::loadView('dokumen.pengajuan', ['doc' => $doc])
            ->setPaper('a4')
            ->setOption('defaultFont', 'DejaVu Sans');

        return $request->boolean('unduh')
            ? $pdf->download($nama)
            : $pdf->stream($nama);
    }

    public function cetak(Request $request, Pengajuan $pengajuan, DigitalSignatureService $signatures)
    {
        $this->authorize($request, $pengajuan);

        return view('surat.cetak', $this->dataCetak($pengajuan, $signatures, false));
    }

    public function cetakPdf(Request $request, Pengajuan $pengajuan, DigitalSignatureService $signatures)
    {
        $this->authorize($request, $pengajuan);
        $data = $this->dataCetak($pengajuan, $signatures, true);
        $filename = 'SURAT-' . preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($pengajuan->nomor_agenda ?: $pengajuan->nomor_ajuan)) . '.pdf';

        return Pdf::loadView('surat.cetak', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->stream($filename);
    }

    private function dataCetak(Pengajuan $pengajuan, DigitalSignatureService $signatures, bool $pdf): array
    {
        $pengajuan->loadMissing(['pegawai.unitKerja', 'tandaTanganPejabat', 'riwayat.pejabat']);
        $signed = $signatures->isSigned($pengajuan);
        $verified = $signed && $signatures->isValid($pengajuan);
        $logos = [];

        foreach (['logo-makassar.png', 'logo-kecamatan.png'] as $name) {
            $path = public_path('images/' . $name);
            $logos[$name] = ! is_file($path)
                ? null
                : ($pdf
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($path))
                    : asset('images/' . $name));
        }

        return [
            'doc' => DokumenKepegawaian::untuk($pengajuan),
            'pengajuan' => $pengajuan,
            'logoKiri' => $logos['logo-makassar.png'],
            'logoKanan' => $logos['logo-kecamatan.png'],
            'qrCode' => $verified ? $signatures->qrCodeDataUri($pengajuan) : null,
            'signatureValid' => $verified,
            'signaturePresent' => $signed,
        ];
    }

    /**
     * Halaman Publik Verifikasi Tanda Tangan Digital & Keaslian Dokumen SIKESI Tamalate.
     * Dapat diakses bebas oleh siapa saja yang memindai QR code pada dokumen fisik/digital.
     */
    public function verifikasi(string $token)
    {
        $pengajuan = Pengajuan::with(['pegawai.unitKerja', 'tandaTanganPejabat', 'distribusiAdmin'])
            ->where('tanda_tangan_token', $token)
            ->firstOrFail();

        return view('dokumen.verifikasi', compact('pengajuan'));
    }

    private function authorize(Request $request, Pengajuan $pengajuan): void
    {
        $user = $request->user();
        $boleh = $pengajuan->pegawai_id === $user->id
            || $user->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin');

        abort_unless($boleh, 403, 'Anda tidak berhak mengakses dokumen ini.');
    }

    private function namaFile(Pengajuan $p): string
    {
        $jenis = strtoupper(Str::of($p->jenis_layanan)->replace('_', '-'));
        $nomor = Str::of((string) ($p->nomor_agenda ?: $p->nomor_ajuan))
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '_');

        return "{$jenis}-{$nomor}.pdf";
    }
}
