<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LampiranBerkas;
use App\Models\LogStatusPengajuan;
use App\Models\PengajuanKepegawaian;
use App\Support\LayananKepegawaian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KepegawaianController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $jenis = $request->query('jenis');
        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        $query = PengajuanKepegawaian::with(['pegawai.unitKerja', 'unitKerja'])
            ->when($jenis, fn ($b) => $b->where('jenis_layanan', $jenis))
            ->when($status, fn ($b) => $b->where('status', $status))
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nomor_pengajuan', 'like', "%{$q}%")
                        ->orWhereHas('pegawai', function ($p) use ($q) {
                            $p->where('nama', 'like', "%{$q}%")
                                ->orWhere('nip', 'like', "%{$q}%");
                        });
                });
            });

        if ($user->hasRole('pegawai')) {
            $query->where('pegawai_id', $user->id);
        } elseif ($user->hasRole('pimpinan') && $user->unitKerja?->tipe === 'kelurahan') {
            $query->where('unit_kerja_id', $user->unit_kerja_id);
        }

        $pengajuan = $query->latest()->paginate(15)->withQueryString();

        return view('kepegawaian.index', [
            'pengajuan' => $pengajuan,
            'jenisList' => PengajuanKepegawaian::JENIS_LAYANAN,
            'statusList' => PengajuanKepegawaian::STATUS_LABELS,
            'jenis' => $jenis,
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_if($user->hasRole('super_admin'), 403, 'Super Admin tidak bertindak sebagai pemohon layanan.');
        abort_unless($user->hasRole('pegawai'), 403, 'Hanya Pegawai/Staf yang dapat mengajukan layanan.');
        abort_unless($user->berhakLayanan(), 403, 'PPPK Paruh Waktu tidak memiliki akses ke layanan Cuti, KGB, Kenaikan Pangkat, dan Pensiun.');

        return view('kepegawaian.create', [
            'pegawai'    => $user->load('unitKerja'),
            'jenisList'  => PengajuanKepegawaian::JENIS_LAYANAN,
            'kodeCuti'   => PengajuanKepegawaian::KODE_CUTI,
            'golongan'   => LayananKepegawaian::GOLONGAN,
            'jenisPensiun' => LayananKepegawaian::JENIS_PENSIUN,
            'nextKgb'    => LayananKepegawaian::nextKgbDate($user),
            'nextGol'    => LayananKepegawaian::nextGolongan($user->pangkat_gol),
            'tmtPensiun' => LayananKepegawaian::tmtPensiun($user),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($user->hasRole('super_admin'), 403, 'Super Admin tidak bertindak sebagai pemohon layanan.');
        abort_unless($user->hasRole('pegawai'), 403, 'Hanya Pegawai/Staf yang dapat mengajukan layanan.');
        abort_unless($user->berhakLayanan(), 403, 'PPPK Paruh Waktu tidak berhak mengajukan layanan kepegawaian.');

        $base = $request->validate([
            'jenis_layanan'  => ['required', 'in:cuti,kgb,kenaikan_pangkat,pensiun'],
            'lampiran'       => ['required', 'array', 'min:1'],
            'lampiran.*'     => ['file', 'mimes:pdf', 'max:5120'], // PDF max 5MB
            'nama_dokumen'   => ['nullable', 'array'],
            'nama_dokumen.*' => ['nullable', 'string', 'max:160'],
        ]);

        $jenis = $base['jenis_layanan'];
        [$dataLayanan, $kodeKlasifikasi, $checklist] = $this->validateAndBuildPayload($request, $jenis);

        $asalWilayah = ($user->unitKerja && $user->unitKerja->tipe === 'kelurahan') ? 'kelurahan' : 'kecamatan';
        $statusAwal = $asalWilayah === 'kelurahan'
            ? 'verifikasi_operator_kelurahan'
            : 'antrean_operator_kecamatan';
        $mejaAwal = PengajuanKepegawaian::MEJA_LABELS[$statusAwal];

        $pengajuan = DB::transaction(function () use (
            $user, $jenis, $kodeKlasifikasi, $asalWilayah, $statusAwal, $mejaAwal, $dataLayanan, $checklist, $request
        ) {
            $item = PengajuanKepegawaian::create([
                'nomor_pengajuan'       => PengajuanKepegawaian::generateNomor($jenis),
                'pegawai_id'            => $user->id,
                'unit_kerja_id'         => $user->unit_kerja_id,
                'asal_wilayah'          => $asalWilayah,
                'jenis_layanan'         => $jenis,
                'kode_klasifikasi'      => $kodeKlasifikasi,
                'status'                => $statusAwal,
                'meja_saat_ini'         => $mejaAwal,
                'data_layanan'          => $dataLayanan,
                'checklist_kelengkapan' => $checklist,
            ]);

            $labels = $request->input('nama_dokumen', []);
            foreach ($request->file('lampiran') as $idx => $file) {
                $path = $file->store("kepegawaian/{$item->id}", 'local');
                LampiranBerkas::create([
                    'pengajuan_kepegawaian_id' => $item->id,
                    'nama_dokumen'             => $labels[$idx] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'nama_file_asli'           => $file->getClientOriginalName(),
                    'path'                     => $path,
                    'ukuran_kb'                => (int) ceil($file->getSize() / 1024),
                    'mime_type'                => $file->getMimeType(),
                    'status_verifikasi'        => 'menunggu',
                    'uploaded_by'              => $user->id,
                ]);
            }

            LogStatusPengajuan::create([
                'pengajuan_kepegawaian_id' => $item->id,
                'status_dari'              => null,
                'status_ke'                => $statusAwal,
                'meja'                     => $mejaAwal,
                'aktor_id'                 => $user->id,
                'aksi'                     => 'ajukan',
                'catatan'                  => "Pengajuan {$item->labelJenis()} dikirim dari unit " . ($user->unitKerja->nama ?? 'Kecamatan Tamalate'),
                'waktu'                    => now(),
            ]);

            AuditLog::catat(
                $user->id,
                'ajukan_kepegawaian',
                'pengajuan_kepegawaian',
                $item->id,
                "Mengajukan {$item->labelJenis()} ({$item->nomor_pengajuan})"
            );

            return $item;
        });

        return redirect()->route('kepegawaian.show', $pengajuan)
            ->with('success', "Pengajuan {$pengajuan->nomor_pengajuan} berhasil dikirim ke meja {$pengajuan->meja_saat_ini}.");
    }

    public function show(Request $request, PengajuanKepegawaian $kepegawaian)
    {
        $user = $request->user();
        $boleh = $kepegawaian->pegawai_id === $user->id
            || $user->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin');
        abort_unless($boleh, 403, 'Anda tidak berhak mengakses pengajuan ini.');

        $kepegawaian->load(['pegawai.unitKerja', 'unitKerja', 'lampiran', 'logs.aktor']);

        return view('kepegawaian.show', [
            'p'          => $kepegawaian,
            'signatures' => $this->buildDigitalSignatures($kepegawaian),
        ]);
    }

    public function proses(Request $request, PengajuanKepegawaian $kepegawaian)
    {
        $user = $request->user();
        $this->authorizeStage($user, $kepegawaian->status);

        $data = $request->validate([
            'aksi'    => ['required', 'in:setuju,revisi,tolak'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($data['aksi'], ['revisi', 'tolak'], true) && blank($data['catatan'])) {
            return back()->withErrors(['catatan' => 'Catatan/alasan wajib diisi apabila mengembalikan revisi atau menolak.']);
        }

        $statusLama = $kepegawaian->status;
        if ($data['aksi'] === 'setuju') {
            $statusBaru = $kepegawaian->nextStatus();
            abort_unless($statusBaru, 400, 'Pengajuan sudah berada pada tahap akhir.');
        } elseif ($data['aksi'] === 'revisi') {
            $statusBaru = 'perlu_revisi';
        } else {
            $statusBaru = 'ditolak';
        }

        $mejaBaru = PengajuanKepegawaian::MEJA_LABELS[$statusBaru] ?? $statusBaru;

        DB::transaction(function () use ($kepegawaian, $statusLama, $statusBaru, $mejaBaru, $data, $user) {
            $kepegawaian->update([
                'status'           => $statusBaru,
                'meja_saat_ini'    => $mejaBaru,
                'catatan_terakhir' => $data['catatan'] ?? $kepegawaian->catatan_terakhir,
            ]);

            LogStatusPengajuan::create([
                'pengajuan_kepegawaian_id' => $kepegawaian->id,
                'status_dari'              => $statusLama,
                'status_ke'                => $statusBaru,
                'meja'                     => $mejaBaru,
                'aktor_id'                 => $user->id,
                'aksi'                     => $data['aksi'],
                'catatan'                  => $data['catatan'] ?? 'Disetujui & diteruskan ke tahap berikutnya',
                'waktu'                    => now(),
            ]);

            AuditLog::catat(
                $user->id,
                'proses_kepegawaian_' . $data['aksi'],
                'pengajuan_kepegawaian',
                $kepegawaian->id,
                "Pengajuan {$kepegawaian->nomor_pengajuan}: {$statusLama} -> {$statusBaru}"
            );
        });

        return back()->with('success', "Status pengajuan diperbarui. Posisi saat ini: {$mejaBaru}.");
    }

    public function updateChecklist(Request $request, PengajuanKepegawaian $kepegawaian)
    {
        abort_unless($request->user()->hasRole('operator'), 403, 'Hanya Operator yang dapat memverifikasi kelengkapan dokumen.');

        $checklistInput = $request->input('checklist', []);
        $existingChecklist = $kepegawaian->checklist_kelengkapan ?? [];

        foreach ($existingChecklist as $idx => &$row) {
            $row['lengkap'] = isset($checklistInput[$idx]['lengkap']);
            $row['catatan'] = $checklistInput[$idx]['catatan'] ?? '';
        }
        unset($row);

        $lampiranStatus = $request->input('lampiran_status', []);
        $lampiranCatatan = $request->input('lampiran_catatan', []);
        foreach ($kepegawaian->lampiran as $lamp) {
            if (isset($lampiranStatus[$lamp->id])) {
                $lamp->update([
                    'status_verifikasi' => $lampiranStatus[$lamp->id],
                    'catatan_operator'  => $lampiranCatatan[$lamp->id] ?? null,
                ]);
            }
        }

        $kepegawaian->update(['checklist_kelengkapan' => $existingChecklist]);

        return back()->with('success', 'Checklist kelengkapan dokumen berhasil disimpan.');
    }

    public function resubmitRevisi(Request $request, PengajuanKepegawaian $kepegawaian)
    {
        $user = $request->user();
        abort_unless($kepegawaian->pegawai_id === $user->id, 403);
        abort_unless($kepegawaian->status === 'perlu_revisi', 400, 'Pengajuan tidak dalam status perlu revisi.');

        $request->validate([
            'lampiran'   => ['nullable', 'array'],
            'lampiran.*' => ['file', 'mimes:pdf', 'max:5120'],
            'catatan'    => ['nullable', 'string', 'max:500'],
        ]);

        $statusBaru = $kepegawaian->asal_wilayah === 'kelurahan'
            ? 'verifikasi_operator_kelurahan'
            : 'antrean_operator_kecamatan';
        $mejaBaru = PengajuanKepegawaian::MEJA_LABELS[$statusBaru];

        DB::transaction(function () use ($request, $kepegawaian, $user, $statusBaru, $mejaBaru) {
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $path = $file->store("kepegawaian/{$kepegawaian->id}", 'local');
                    LampiranBerkas::create([
                        'pengajuan_kepegawaian_id' => $kepegawaian->id,
                        'nama_dokumen'             => 'Perbaikan: ' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'nama_file_asli'           => $file->getClientOriginalName(),
                        'path'                     => $path,
                        'ukuran_kb'                => (int) ceil($file->getSize() / 1024),
                        'mime_type'                => $file->getMimeType(),
                        'status_verifikasi'        => 'menunggu',
                        'uploaded_by'              => $user->id,
                    ]);
                }
            }

            $kepegawaian->update([
                'status'           => $statusBaru,
                'meja_saat_ini'    => $mejaBaru,
                'catatan_terakhir' => $request->input('catatan'),
            ]);

            LogStatusPengajuan::create([
                'pengajuan_kepegawaian_id' => $kepegawaian->id,
                'status_dari'              => 'perlu_revisi',
                'status_ke'                => $statusBaru,
                'meja'                     => $mejaBaru,
                'aktor_id'                 => $user->id,
                'aksi'                     => 'ajukan',
                'catatan'                  => $request->input('catatan') ?: 'Berkas revisi dikirim ulang oleh pemohon',
                'waktu'                    => now(),
            ]);
        });

        return back()->with('success', 'Berkas revisi berhasil dikirim kembali ke antrean.');
    }

    public function downloadLampiran(Request $request, LampiranBerkas $lampiran)
    {
        $user = $request->user();
        $p = $lampiran->pengajuan;
        $boleh = $p->pegawai_id === $user->id
            || $user->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin');
        abort_unless($boleh, 403);

        return Storage::disk('local')->download($lampiran->path, $lampiran->nama_file_asli);
    }

    /**
     * Generate Dokumen PDF otomatis sesuai template Form Cuti Baku BKPSDMD.
     */
    public function cetakFormCuti(Request $request, PengajuanKepegawaian $kepegawaian)
    {
        abort_unless($kepegawaian->jenis_layanan === 'cuti', 404, 'Dokumen ini khusus untuk pengajuan Cuti.');
        $user = $request->user();
        $boleh = $kepegawaian->pegawai_id === $user->id
            || $user->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin');
        abort_unless($boleh, 403);

        $kepegawaian->load(['pegawai.unitKerja', 'unitKerja', 'logs.aktor']);

        $pdf = Pdf::loadView('kepegawaian.pdf_form_cuti', [
            'p'          => $kepegawaian,
            'signatures' => $this->buildDigitalSignatures($kepegawaian),
        ])
            ->setPaper('a4')
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'FORM-CUTI-BKPSDMD-' . str_replace('/', '-', $kepegawaian->nomor_pengajuan) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Ekspor Rekapitulasi Daftar KGB ke format Excel (.xlsx).
     */
    public function eksporKgbExcel(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'), 403);

        $rows = PengajuanKepegawaian::with(['pegawai', 'unitKerja'])
            ->where('jenis_layanan', 'kgb')
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi KGB');

        // Kop / Judul
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'REKAPITULASI DAFTAR USULAN KENAIKAN GAJI BERKALA (KGB) · KECAMATAN TAMALATE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'No', 'Nomor Pengajuan', 'NIP', 'Nama Pegawai', 'Unit Kerja',
            'TMT KGB Terakhir', 'TMT KGB Berikutnya', 'Gaji Pokok Lama', 'Status / Meja Saat Ini',
        ];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }

        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $r = 4;
        foreach ($rows as $i => $item) {
            $d = $item->data_layanan ?? [];
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $item->nomor_pengajuan);
            $sheet->setCellValue("C{$r}", ' ' . ($item->pegawai->nip ?? '-'));
            $sheet->setCellValue("D{$r}", $item->pegawai->nama ?? '-');
            $sheet->setCellValue("E{$r}", $item->unitKerja->nama ?? '-');
            $sheet->setCellValue("F{$r}", $d['tmt_kgb_terakhir'] ?? '-');
            $sheet->setCellValue("G{$r}", $d['tmt_kgb_berikutnya'] ?? '-');
            $sheet->setCellValue("H{$r}", isset($d['gaji_pokok_lama']) ? (float) $d['gaji_pokok_lama'] : 0);
            $sheet->setCellValue("I{$r}", $item->labelStatus());
            $r++;
        }

        $lastRow = max(3, $r - 1);
        $sheet->getStyle("A3:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'rekap_kgb_tamalate_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Daftar Nominatif Kolektif & Surat Pengantar ke BKPSDMD (Kenaikan Pangkat & Pensiun).
     */
    public function nominatif(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'), 403);

        $jenis = $request->query('jenis', 'kenaikan_pangkat');
        if (! in_array($jenis, ['kenaikan_pangkat', 'pensiun'], true)) {
            $jenis = 'kenaikan_pangkat';
        }

        $daftar = PengajuanKepegawaian::with(['pegawai', 'unitKerja'])
            ->where('jenis_layanan', $jenis)
            ->latest()
            ->get();

        return view('kepegawaian.nominatif', compact('daftar', 'jenis'));
    }

    public function simpanNominatif(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'pimpinan', 'super_admin'), 403);

        $data = $request->validate([
            'ids'               => ['required', 'array', 'min:1'],
            'ids.*'             => ['exists:pengajuan_kepegawaian,id'],
            'nomor_nominatif'   => ['required', 'string', 'max:80'],
            'tanggal_nominatif' => ['required', 'date'],
        ]);

        PengajuanKepegawaian::whereIn('id', $data['ids'])->update([
            'nomor_nominatif'   => $data['nomor_nominatif'],
            'tanggal_nominatif' => $data['tanggal_nominatif'],
        ]);

        return back()->with('success', "Nomor Daftar Nominatif {$data['nomor_nominatif']} berhasil ditetapkan.");
    }

    private function validateAndBuildPayload(Request $request, string $jenis): array
    {
        if ($jenis === 'cuti') {
            $d = $request->validate([
                'kode_cuti'       => ['required', 'in:' . implode(',', array_keys(PengajuanKepegawaian::KODE_CUTI))],
                'tanggal_mulai'   => ['required', 'date'],
                'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
                'alasan'          => ['required', 'string', 'max:500'],
                'no_telp'         => ['required', 'string', 'max:25'],
            ]);
            $lamaHari = Carbon::parse($d['tanggal_mulai'])->diffInDays(Carbon::parse($d['tanggal_selesai'])) + 1;
            $payload = $d + [
                'jenis_cuti_nama' => PengajuanKepegawaian::KODE_CUTI[$d['kode_cuti']],
                'lama_hari'       => $lamaHari,
            ];

            return [$payload, $d['kode_cuti'], null];
        }

        if ($jenis === 'kgb') {
            $d = $request->validate([
                'tmt_kgb_terakhir' => ['required', 'date'],
                'gaji_pokok_lama'  => ['required', 'numeric', 'min:0'],
                'gaji_pokok_baru'  => ['nullable', 'numeric', 'min:0'],
                'masa_kerja_tahun' => ['nullable', 'integer', 'min:0'],
                'masa_kerja_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'keterangan'       => ['nullable', 'string', 'max:500'],
            ]);
            $d['tmt_kgb_berikutnya'] = Carbon::parse($d['tmt_kgb_terakhir'])->addYears(2)->toDateString();

            return [$d, '800.1.11.13', null];
        }

        if ($jenis === 'kenaikan_pangkat') {
            $d = $request->validate([
                'pangkat_lama'          => ['required', 'string', 'max:40'],
                'pangkat_diusulkan'     => ['required', 'string', 'max:40'],
                'tmt_pangkat_terakhir'  => ['required', 'date'],
                'tmt_pangkat_diusulkan' => ['required', 'date'],
                'periode_usulan'        => ['nullable', 'string', 'max:40'],
                'keterangan'            => ['nullable', 'string', 'max:500'],
            ]);
            $checklist = array_map(fn ($item) => ['item' => $item, 'lengkap' => false, 'catatan' => ''], PengajuanKepegawaian::CHECKLIST_TEMPLATE['kenaikan_pangkat']);

            return [$d, '800.1.3.2', $checklist];
        }

        // pensiun
        $d = $request->validate([
            'jenis_pensiun' => ['required', 'in:BUP,APS'],
            'tmt_pensiun'   => ['required', 'date'],
            'keterangan'    => ['nullable', 'string', 'max:500'],
        ]);
        $checklist = array_map(fn ($item) => ['item' => $item, 'lengkap' => false, 'catatan' => ''], PengajuanKepegawaian::CHECKLIST_TEMPLATE['pensiun']);

        return [$d, '800.1.6.6', $checklist];
    }

    private function authorizeStage($user, string $status): void
    {
        $allowed = match ($status) {
            'verifikasi_operator_kelurahan', 'antrean_operator_kecamatan' => $user->hasRole('operator'),
            'approval_lurah', 'approval_camat' => $user->hasRole('pimpinan'),
            'approval_kasubag' => $user->hasRole('verifikator_pimpinan'),
            'approval_sekcam' => $user->hasRole('verifikator_pimpinan', 'pimpinan'),
            default => false,
        };

        abort_unless($allowed, 403, 'Anda tidak memiliki kewenangan memproses berkas pada meja ini.');
    }

    /**
     * Ekstrak tanda tangan elektronik Lurah & Camat dari log persetujuan resmi.
     */
    private function buildDigitalSignatures(PengajuanKepegawaian $p): array
    {
        $secret = config('app.key') ?: 'sikesi-tamalate-key';
        $result = ['lurah' => null, 'camat' => null];

        $mapStage = [
            'approval_lurah' => 'lurah',
            'approval_camat' => 'camat',
        ];

        foreach ($p->logs as $log) {
            if ($log->aksi !== 'setuju' || ! isset($mapStage[$log->status_dari])) {
                continue;
            }
            $slot = $mapStage[$log->status_dari];
            $aktor = $log->aktor;
            $waktu = $log->waktu;
            $token = hash_hmac('sha256', implode('|', [
                'kepegawaian',
                $p->id,
                $p->nomor_pengajuan,
                $slot,
                $log->aktor_id,
                $aktor?->nip ?? '',
                optional($waktu)->timestamp ?? 0,
            ]), $secret);

            $result[$slot] = [
                'nama'       => $aktor?->nama ?? ($slot === 'camat' ? 'Camat Tamalate' : 'Lurah'),
                'nip'        => $aktor?->nip ?? '-',
                'jabatan'    => $aktor?->jabatan ?? ($slot === 'camat' ? 'Camat Tamalate' : 'Lurah'),
                'waktu_wita' => $waktu ? $waktu->timezone('Asia/Makassar')->format('d/m/Y H:i') . ' WITA' : '-',
                'token'      => $token,
            ];
        }

        return $result;
    }
}

