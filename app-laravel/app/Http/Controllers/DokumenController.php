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
     * Hanya untuk pengajuan yang sudah berstatus final (selesai).
     */
    public function pengajuan(Request $request, Pengajuan $pengajuan)
    {
        $this->authorize($request, $pengajuan);

        abort_unless(
            in_array($pengajuan->status, Pengajuan::STATUS_SELESAI, true),
            400,
            'Dokumen baru dapat dicetak setelah pengajuan berstatus selesai/disetujui.'
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

    private function authorize(Request $request, Pengajuan $pengajuan): void
    {
        $user = $request->user();
        $boleh = $pengajuan->pegawai_id === $user->id
            || $user->hasRole('verifikator', 'kasi_kasubag', 'seksam', 'camat_lurah', 'super_admin');

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
