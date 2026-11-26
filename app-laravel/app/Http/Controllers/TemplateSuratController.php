<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use App\Support\DocxTemplateBuilder;
use App\Support\LayananKepegawaian;
use App\Support\SuratTemplateCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TemplateSuratController extends Controller
{
    public function index(Request $request)
    {
        $templates = SuratTemplateCatalog::all();
        $activeJenis = $request->query('jenis', 'cuti');
        if (! isset($templates[$activeJenis])) {
            $activeJenis = 'cuti';
        }

        $units = UnitKerja::orderBy('tipe')->orderBy('nama')->get();

        return view('template_surat.index', [
            'templates'      => $templates,
            'activeJenis'    => $activeJenis,
            'units'          => $units,
            'golongan'       => LayananKepegawaian::GOLONGAN,
            'jenisCutiList'  => SuratTemplateCatalog::JENIS_CUTI_LIST,
            'sampleFilled'   => SuratTemplateCatalog::resolveData($activeJenis, 'filled'),
            'userPrefill'    => auth()->user()?->load('unitKerja'),
        ]);
    }

    public function downloadBlank(string $jenis, string $format)
    {
        abort_unless(SuratTemplateCatalog::get($jenis) !== null, 404);
        abort_unless(in_array($format, ['pdf', 'docx'], true), 404);

        $data = SuratTemplateCatalog::resolveData($jenis, 'blank');

        return $this->streamDocument($jenis, $format, $data, "TEMPLATE-KOSONG-{$jenis}-TAMALATE.{$format}");
    }

    public function exportFilled(Request $request, string $jenis, string $format)
    {
        abort_unless(SuratTemplateCatalog::get($jenis) !== null, 404);
        abort_unless(in_array($format, ['pdf', 'docx'], true), 404);

        $data = SuratTemplateCatalog::resolveData($jenis, 'filled', $request->all());

        return $this->streamDocument($jenis, $format, $data, "CONTOH-SURAT-{$jenis}-TAMALATE.{$format}");
    }

    private function streamDocument(string $jenis, string $format, array $d, string $filename)
    {
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('template_surat.pdf', [
                'jenis' => $jenis,
                'd'     => $d,
            ])->setPaper('a4')->setOption('defaultFont', 'DejaVu Sans');

            return $pdf->download($filename);
        }

        $binary = $this->buildDocx($jenis, $d)->buildBinary();

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    private function buildDocx(string $jenis, array $d): DocxTemplateBuilder
    {
        $doc = (new DocxTemplateBuilder())->addKopSurat();

        if ($jenis === 'cuti') {
            $doc->addTitle('FORMULIR PERMOHONAN CUTI PEGAWAI', 'Nomor: ' . $d['nomor_surat'])
                ->addParagraph("Makassar, {$d['tanggal_surat']}", ['align' => 'right', 'spaceAfter' => 60])
                ->addParagraph("Kepada Yth.\nCamat Tamalate Kota Makassar\nc.q. Kasubag Umum dan Kepegawaian\ndi - Makassar", ['spaceAfter' => 120])
                ->addParagraph('Saya yang bertanda tangan di bawah ini:', ['spaceAfter' => 60])
                ->addKeyValueTable([
                    ['Nama Lengkap', $d['nama_pegawai']],
                    ['NIP / NIPPPK', $d['nip']],
                    ['Pangkat / Gol. Ruang', $d['pangkat_gol']],
                    ['Jabatan', $d['jabatan']],
                    ['Satuan Organisasi', $d['unit_kerja']],
                ], true)
                ->addParagraph("Dengan ini mengajukan permohonan cuti untuk tahun {$d['tahun']} selama {$d['lama_hari']} hari kerja, terhitung mulai tanggal {$d['tanggal_mulai']} s.d. {$d['tanggal_selesai']}, dengan rincian jenis cuti sebagai berikut:", ['spaceAfter' => 80]);

            $mark = static fn (string $label) => ($d['mode'] === 'filled' && $d['jenis_cuti'] === $label) ? '[ ✓ ] ' : '[   ] ';
            $doc->addGridTable(null, [
                [$mark('Cuti Tahunan') . 'Cuti Tahunan', $mark('Cuti Besar') . 'Cuti Besar (Masa kerja > 5 tahun)'],
                [$mark('Cuti Sakit') . 'Cuti Sakit (Lampiran Surat Dokter)', $mark('Cuti Alasan Penting') . 'Cuti Alasan Penting'],
                [$mark('Cuti Melahirkan') . 'Cuti Melahirkan', $mark('Cuti di Luar Tanggungan Negara (CLTN)') . 'Cuti di Luar Tanggungan Negara (CLTN)'],
            ], [4650, 4650], true);

            $doc->addKeyValueTable([
                ['Alasan Pengajuan Cuti', $d['alasan']],
                ['Nomor Telepon / HP', $d['no_hp']],
            ], true)
                ->addParagraph('Demikian permohonan ini saya buat untuk dapat dipertimbangkan sebagaimana mestinya.', ['spaceAfter' => 140])
                ->addSignatureColumns(
                    [
                        'Catatan Kasubag Kepegawaian:',
                        "Sisa Hak Cuti: [ {$d['sisa_cuti']} ] Hari",
                        'Paraf Verifikasi: ..................',
                    ],
                    [
                        'Hormat saya,',
                        "Pemohon Cuti,\n\n",
                        "({$d['nama_pegawai']})",
                        "NIP. {$d['nip']}",
                    ],
                    true
                );

            return $doc;
        }

        if ($jenis === 'kgb') {
            return $doc->addParagraph("Makassar, {$d['tanggal_surat']}", ['align' => 'right', 'spaceAfter' => 80])
                ->addKeyValueTable([
                    ['Nomor', $d['nomor_surat']],
                    ['Sifat', 'Biasa'],
                    ['Lampiran', '1 (satu) Berkas'],
                    ['Perihal', "Usulan Kenaikan Gaji Berkala (KGB) Pegawai A.n. {$d['nama_pegawai']}"],
                ], false)
                ->addParagraph("Kepada Yth.\nKepala BKPSDMD Kota Makassar\ndi - Makassar", ['spaceAfter' => 120])
                ->addParagraph('Dengan hormat, bersama ini kami sampaikan usulan Kenaikan Gaji Berkala (KGB) Pegawai Negeri Sipil / PPPK instansi Kecamatan Tamalate Kota Makassar atas nama:', ['spaceAfter' => 80])
                ->addKeyValueTable([
                    ['1. Nama Pegawai', $d['nama_pegawai']],
                    ['2. NIP / NIPPPK', $d['nip']],
                    ['3. Pangkat / Gol. Ruang', $d['pangkat_gol']],
                    ['4. Jabatan', $d['jabatan']],
                    ['5. Unit Kerja', $d['unit_kerja']],
                    ['6. Gaji Pokok Lama', "Rp {$d['gaji_lama']} (SK KGB Terakhir)"],
                    ['7. TMT KGB Lama', $d['tmt_kgb_lama']],
                    ['8. Gaji Pokok Baru', "Rp {$d['gaji_baru']}"],
                    ['9. TMT KGB Baru', $d['tmt_kgb_baru']],
                    ['10. Masa Kerja Golongan', "{$d['masa_kerja_tahun']} Tahun {$d['masa_kerja_bulan']} Bulan"],
                ], true)
                ->addParagraph('Sebagai bahan pertimbangan, terlampir disertakan: (1) Fotokopi SK Pangkat Terakhir, (2) Fotokopi SK KGB Terakhir, (3) Fotokopi SKP 2 Tahun Terakhir.', ['spaceAfter' => 60])
                ->addParagraph('Demikian pengusulan ini disampaikan, atas perhatiannya diucapkan terima kasih.', ['spaceAfter' => 160])
                ->addSignatureColumns([], [
                    'Camat Tamalate Kota Makassar,',
                    "\n\n{$d['nama_camat']}",
                    $d['pangkat_camat'],
                    "NIP. {$d['nip_camat']}",
                ], false);
        }

        if ($jenis === 'spt') {
            return $doc->addTitle('SURAT PERINTAH TUGAS', 'NOMOR: ' . $d['nomor_surat'])
                ->addKeyValueTable([
                    ['Dasar', "1. Peraturan Wali Kota Makassar Nomor 16 Tahun 2017 tentang Tata Naskah Dinas.\n2. Surat {$d['dasar_instansi']} Nomor: {$d['dasar_nomor']} Tanggal {$d['dasar_tanggal']} Hal {$d['dasar_perihal']}."],
                ], false)
                ->addParagraph('MEMERINTAHKAN:', ['align' => 'center', 'bold' => true, 'spaceBefore' => 60, 'spaceAfter' => 80])
                ->addGridTable(
                    ['No', 'Nama Pegawai', 'NIP / NIPPPK', 'Pangkat / Gol.', 'Jabatan'],
                    [
                        ['1', $d['nama_pegawai'], $d['nip'], $d['pangkat_gol'], $d['jabatan']],
                        ['2', $d['pegawai2_nama'], $d['pegawai2_nip'], $d['pegawai2_gol'], $d['pegawai2_jabatan']],
                    ],
                    [600, 2300, 2200, 1700, 2500],
                    true
                )
                ->addParagraph("Untuk:\n1. Melaksanakan {$d['maksud_tugas']} yang bertempat di {$d['lokasi_tugas']} pada tanggal {$d['tanggal_tugas']}.\n2. Melaporkan hasil pelaksanaan penugasan kepada Camat Tamalate setelah kegiatan selesai dilaksanakan.\n3. Melaksanakan perintah ini dengan penuh rasa tanggung jawab.", ['spaceAfter' => 160])
                ->addSignatureColumns([], [
                    "Ditetapkan di Makassar\nPada tanggal {$d['tanggal_surat']}",
                    'Camat Tamalate Kota Makassar,',
                    "\n\n{$d['nama_camat']}",
                    $d['pangkat_camat'],
                    "NIP. {$d['nip_camat']}",
                ], false);
        }

        // Kenaikan Pangkat / Pensiun
        $perihal = $jenis === 'kenaikan_pangkat'
            ? "Usulan Kenaikan Pangkat PNS A.n. {$d['nama_pegawai']}"
            : "Usulan Pemberhentian / Pensiun PNS ({$d['jenis_pensiun']}) A.n. {$d['nama_pegawai']}";

        $rows = [
            ['1. Nama Pegawai', $d['nama_pegawai']],
            ['2. NIP', $d['nip']],
            ['3. Pangkat / Gol. Saat Ini', $d['pangkat_gol']],
            ['4. Jabatan', $d['jabatan']],
            ['5. Unit Kerja', $d['unit_kerja']],
        ];
        if ($jenis === 'kenaikan_pangkat') {
            $rows[] = ['6. Golongan Diusulkan', $d['golongan_usulan']];
            $rows[] = ['7. TMT Pangkat Diusulkan', $d['tmt_usulan']];
        } else {
            $rows[] = ['6. Jenis Pensiun', $d['jenis_pensiun']];
            $rows[] = ['7. TMT Pensiun', $d['tmt_usulan']];
        }

        $syaratText = implode(', ', $d['meta']['persyaratan']);

        return $doc->addParagraph("Makassar, {$d['tanggal_surat']}", ['align' => 'right', 'spaceAfter' => 80])
            ->addKeyValueTable([
                ['Nomor', $d['nomor_surat']],
                ['Sifat', 'Biasa'],
                ['Lampiran', '1 (satu) Berkas'],
                ['Perihal', $perihal],
            ], false)
            ->addParagraph("Kepada Yth.\nKepala BKPSDMD Kota Makassar\ndi - Makassar", ['spaceAfter' => 120])
            ->addParagraph('Dengan hormat, bersama ini kami teruskan berkas usulan administrasi kepegawaian Kecamatan Tamalate atas nama:', ['spaceAfter' => 80])
            ->addKeyValueTable($rows, true)
            ->addParagraph("Berkas persyaratan terlampir: {$syaratText}.", ['spaceAfter' => 140])
            ->addSignatureColumns([], [
                'Camat Tamalate Kota Makassar,',
                "\n\n{$d['nama_camat']}",
                $d['pangkat_camat'],
                "NIP. {$d['nip_camat']}",
            ], false);
    }
}
