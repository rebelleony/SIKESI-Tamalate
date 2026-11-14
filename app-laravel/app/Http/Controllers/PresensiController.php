<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PengaturanApel;
use App\Models\PresensiApel;
use App\Models\UnitKerja;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $pengaturan = PengaturanApel::current();
        $sesiAktif = $this->sesiAktif($request, $pengaturan);

        $hariIni = PresensiApel::where('pegawai_id', $user->id)
            ->whereDate('tanggal', today())
            ->get()
            ->keyBy('sesi');

        $riwayat = PresensiApel::where('pegawai_id', $user->id)
            ->latest('tanggal')->latest('id')
            ->paginate(15);

        $tanggalFilter = $request->query('tanggal');
        $antreanValidasi = $user->hasRole('operator', 'super_admin')
            ? PresensiApel::with('pegawai.unitKerja')
                ->whereIn('status_validasi', ['pending', 'menunggu'])
                ->when($tanggalFilter, fn ($q) => $q->whereDate('tanggal', $tanggalFilter))
                ->latest('tanggal')
                ->latest('id')
                ->get()
            : collect();

        return view('presensi.index', [
            'hariIni'         => $hariIni,
            'riwayat'         => $riwayat,
            'sesiAktif'       => $sesiAktif,
            'pengaturan'      => $pengaturan,
            'rekapPekan'      => $this->rekapPekan($user->id),
            'antreanValidasi' => $antreanValidasi,
            'tanggalFilter'   => $tanggalFilter,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $pengaturan = PengaturanApel::current();

        $sesi = $this->sesiAktif($request, $pengaturan);
        abort_if(
            $sesi === null,
            400,
            "Presensi hanya dapat dilakukan pada jam apel pagi ({$pengaturan->pagi_mulai}-{$pengaturan->pagi_selesai}) atau sore ({$pengaturan->sore_mulai}-{$pengaturan->sore_selesai}) WITA."
        );

        $data = $request->validate([
            'status_kehadiran' => ['nullable', 'in:hadir,izin,dinas_luar'],
            'keterangan'       => ['nullable', 'string', 'max:500'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $statusKehadiran = $data['status_kehadiran'] ?? 'hadir';

        if ($statusKehadiran === 'hadir' && ! $request->hasFile('foto')) {
            return back()->withErrors(['foto' => 'Foto kehadiran di barisan apel wajib diambil/diunggah untuk status Hadir.']);
        }
        if (in_array($statusKehadiran, ['izin', 'dinas_luar'], true) && blank($data['keterangan'] ?? null)) {
            return back()->withErrors(['keterangan' => 'Keterangan wajib diisi untuk pengajuan Izin / Dinas Luar.']);
        }

        $sudah = PresensiApel::where('pegawai_id', $user->id)
            ->whereDate('tanggal', today())
            ->where('sesi', $sesi)
            ->exists();
        abort_if($sudah, 400, "Anda sudah melakukan presensi apel {$sesi} hari ini.");

        $path = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('presensi/' . now()->format('Y/m/d'), 'public');
        }

        $now = now();
        $presensi = PresensiApel::create([
            'pegawai_id'       => $user->id,
            'user_id'          => $user->id,
            'tanggal'          => today()->toDateString(),
            'sesi'             => $sesi,
            'status_kehadiran' => $statusKehadiran,
            'jam'              => $now->format('H:i:s'),
            'waktu_absen'      => $now,
            'foto_path'        => $path,
            'foto_apel'        => $path,
            'status_validasi'  => 'pending',
            'keterangan'       => $data['keterangan'] ?? null,
            'created_at'       => $now,
        ]);

        AuditLog::catat(
            $user->id,
            'presensi_apel',
            'presensi_apel',
            $presensi->id,
            "Presensi apel {$sesi} ({$statusKehadiran}) dicatat pada {$presensi->jam} WITA"
        );

        return back()->with('success', "Presensi apel {$sesi} ({$presensi->labelKehadiran()}) berhasil dicatat dan menunggu validasi.");
    }

    public function validasi(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'super_admin'), 403, 'Hanya Operator/Admin Kepegawaian yang berwenang memvalidasi bukti presensi apel.');

        $data = $request->validate([
            'presensi_id'     => ['required', 'exists:presensi_apel,id'],
            'status_validasi' => ['required', 'in:pending,valid,ditolak,hadir,izin,sakit,alpa'],
        ]);

        $presensi = PresensiApel::findOrFail($data['presensi_id']);
        $val = $data['status_validasi'];

        $update = [
            'status_validasi' => $val,
            'validasi_oleh'   => $request->user()->id,
            'waktu_validasi'  => now(),
        ];

        if ($val === 'ditolak' || $val === 'alpa') {
            $update['status_kehadiran'] = 'tanpa_keterangan';
        } elseif ($val === 'izin') {
            $update['status_kehadiran'] = 'izin';
        } elseif ($val === 'hadir') {
            $update['status_kehadiran'] = 'hadir';
        }

        $presensi->update($update);

        AuditLog::catat(
            $request->user()->id,
            'validasi_presensi',
            'presensi_apel',
            $presensi->id,
            "Status validasi presensi ID {$presensi->id} diubah menjadi {$val}"
        );

        return back()->with('success', 'Status presensi berhasil divalidasi.');
    }

    /**
     * Rekapitulasi Bulanan & Peringatan Pembinaan Pegawai.
     */
    public function rekap(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'), 403);

        $bulan = $request->query('bulan', now()->format('Y-m'));
        $unitId = $request->query('unit_kerja_id');
        $pengaturan = PengaturanApel::current();
        $ambangTk = (int) $pengaturan->ambang_batas_tk;

        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $pegawaiList = User::with('unitKerja')
            ->where('status', 'aktif')
            ->when($unitId, fn ($q) => $q->where('unit_kerja_id', $unitId))
            ->orderBy('nama')
            ->get();

        $presensiBulan = PresensiApel::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('pegawai_id');

        $rekap = $pegawaiList->map(function ($pegawai) use ($presensiBulan, $ambangTk) {
            $rows = $presensiBulan->get($pegawai->id, collect());
            $hadir = 0;
            $izin = 0;
            $dl = 0;
            $tk = 0;

            foreach ($rows as $r) {
                if (in_array($r->status_validasi, ['ditolak', 'alpa'], true) || $r->status_kehadiran === 'tanpa_keterangan') {
                    $tk++;
                } elseif ($r->status_kehadiran === 'dinas_luar') {
                    $dl++;
                } elseif ($r->status_kehadiran === 'izin' || $r->status_validasi === 'izin') {
                    $izin++;
                } else {
                    $hadir++;
                }
            }

            return [
                'pegawai'    => $pegawai,
                'hadir'      => $hadir,
                'izin'       => $izin,
                'dinas_luar' => $dl,
                'tk'         => $tk,
                'pembinaan'  => $tk >= $ambangTk,
            ];
        });

        $peringatanCount = $rekap->where('pembinaan', true)->count();

        if ($request->query('export') === 'xlsx') {
            return $this->exportRekapExcel($rekap, $bulan);
        }
        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('presensi.pdf_rekap', [
                'rekap'    => $rekap,
                'bulan'    => $bulan,
                'ambangTk' => $ambangTk,
            ])->setPaper('a4', 'landscape');

            return $pdf->download("rekap_presensi_apel_{$bulan}.pdf");
        }

        return view('presensi.rekap', [
            'rekap'           => $rekap,
            'bulan'           => $bulan,
            'unitId'          => $unitId,
            'units'           => UnitKerja::orderBy('tipe')->orderBy('nama')->get(),
            'ambangTk'        => $ambangTk,
            'peringatanCount' => $peringatanCount,
            'pengaturan'      => $pengaturan,
        ]);
    }

    /**
     * Buat Draf Surat Panggilan Pembinaan Pegawai (PDF).
     */
    public function suratPembinaan(Request $request, User $pegawai)
    {
        abort_unless($request->user()->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'), 403);

        $bulan = $request->query('bulan', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $jumlahTk = PresensiApel::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where(function ($q) {
                $q->where('status_kehadiran', 'tanpa_keterangan')
                    ->orWhereIn('status_validasi', ['ditolak', 'alpa']);
            })
            ->count();

        $pegawai->load('unitKerja');

        $pdf = Pdf::loadView('presensi.pdf_surat_pembinaan', [
            'pegawai'  => $pegawai,
            'bulan'    => $bulan,
            'jumlahTk' => $jumlahTk,
        ])->setPaper('a4');

        return $pdf->stream("surat_pembinaan_{$pegawai->nip}_{$bulan}.pdf");
    }

    /**
     * Simpan konfigurasi jam sesi apel & mode testing.
     */
    public function updatePengaturan(Request $request)
    {
        abort_unless($request->user()->hasRole('operator', 'super_admin'), 403);

        $data = $request->validate([
            'pagi_mulai'      => ['required', 'date_format:H:i'],
            'pagi_selesai'    => ['required', 'date_format:H:i'],
            'sore_mulai'      => ['required', 'date_format:H:i'],
            'sore_selesai'    => ['required', 'date_format:H:i'],
            'ambang_batas_tk' => ['required', 'integer', 'min:1', 'max:31'],
        ]);

        $pengaturan = PengaturanApel::current();
        $pengaturan->fill($data + ['mode_testing' => $request->boolean('mode_testing')]);
        $pengaturan->save();

        return back()->with('success', 'Pengaturan jam sesi apel & mode testing berhasil diperbarui.');
    }

    private function exportRekapExcel($rekap, string $bulan)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Rekap {$bulan}");

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', "REKAPITULASI PRESENSI APEL BULAN {$bulan} · KECAMATAN TAMALATE");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'NIP', 'Nama Pegawai', 'Unit Kerja', 'Hadir', 'Izin', 'Dinas Luar', 'Tanpa Keterangan (TK)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
        ]);

        $r = 4;
        foreach ($rekap->values() as $i => $row) {
            $p = $row['pegawai'];
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", ' ' . ($p->nip ?? '-'));
            $sheet->setCellValue("C{$r}", $p->nama);
            $sheet->setCellValue("D{$r}", $p->unitKerja->nama ?? '-');
            $sheet->setCellValue("E{$r}", $row['hadir']);
            $sheet->setCellValue("F{$r}", $row['izin']);
            $sheet->setCellValue("G{$r}", $row['dinas_luar']);
            $sheet->setCellValue("H{$r}", $row['tk']);
            $r++;
        }

        $lastRow = max(3, $r - 1);
        $sheet->getStyle("A3:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'H') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "rekap_presensi_apel_{$bulan}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function rekapPekan(int $pegawaiId): array
    {
        $mulaiPekan = today()->startOfWeek();
        $rows = PresensiApel::where('pegawai_id', $pegawaiId)
            ->whereBetween('tanggal', [$mulaiPekan, $mulaiPekan->copy()->endOfWeek()])
            ->get();

        $rekap = ['pagi' => 0, 'sore' => 0, 'total' => 0];
        foreach ($rows as $r) {
            if (in_array($r->status_validasi, ['valid', 'pending', 'hadir', 'menunggu'], true)) {
                $rekap[$r->sesi] = ($rekap[$r->sesi] ?? 0) + 1;
                $rekap['total']++;
            }
        }
        $rekap['periode'] = $mulaiPekan->format('d M Y') . ' - ' . $mulaiPekan->copy()->endOfWeek()->format('d M Y');

        return $rekap;
    }

    private function sesiAktif(?Request $request = null, ?PengaturanApel $pengaturan = null): ?string
    {
        $request = $request ?? request();
        $pengaturan = $pengaturan ?? PengaturanApel::current();

        if ($pengaturan->mode_testing || config('app.debug') || app()->environment('local')) {
            if ($request && $request->filled('sesi_demo')) {
                $demo = $request->query('sesi_demo') ?? $request->input('sesi_demo');
                if (in_array($demo, ['pagi', 'sore'], true)) {
                    session(['sesi_demo' => $demo]);
                    return $demo;
                }
                if (in_array($demo, ['reset', 'auto'], true)) {
                    session()->forget('sesi_demo');
                }
            }
            if (session()->has('sesi_demo')) {
                return session('sesi_demo');
            }
        }

        $now = now();
        $windows = [
            'pagi' => [$pengaturan->pagi_mulai, $pengaturan->pagi_selesai],
            'sore' => [$pengaturan->sore_mulai, $pengaturan->sore_selesai],
        ];
        foreach ($windows as $sesi => [$mulaiStr, $selesaiStr]) {
            [$mH, $mM] = array_map('intval', explode(':', $mulaiStr));
            [$sH, $sM] = array_map('intval', explode(':', $selesaiStr));
            $mulai = $now->copy()->setTime($mH, $mM, 0);
            $selesai = $now->copy()->setTime($sH, $sM, 0);
            if ($now->betweenIncluded($mulai, $selesai)) {
                return $sesi;
            }
        }

        // Jika mode testing aktif tanpa sesi_demo terpilih, izinkan sesi pagi secara default agar mudah didemonstrasikan
        if ($pengaturan->mode_testing) {
            return $now->hour < 12 ? 'pagi' : 'sore';
        }

        return null;
    }
}
