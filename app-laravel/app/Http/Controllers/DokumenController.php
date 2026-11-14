<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
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
