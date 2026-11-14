<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\PresensiApel;
use App\Models\SuratMasuk;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    public const JENIS = [
        'presensi' => 'Rekap Presensi Apel',
        'cuti' => 'Rekap Pengajuan Cuti',
        'kgb' => 'Rekap Pengajuan KGB',
        'kenaikan_pangkat' => 'Rekap Kenaikan Pangkat',
        'pensiun' => 'Rekap Pensiun',
        'surat_masuk' => 'Rekap Surat Masuk',
    ];

    public function index(Request $request)
    {
        return view('laporan.index', $this->build($request) + [
            'jenisList' => self::JENIS,
            'units' => UnitKerja::orderBy('nama')->get(),
        ]);
    }

    public function cetak(Request $request)
    {
        return view('laporan.print', $this->build($request) + ['jenisList' => self::JENIS]);
    }

    public function ekspor(Request $request)
    {
        $data = $this->build($request);
        $nama = 'laporan_' . $data['jenis'] . '_' . $data['dari'] . '_' . $data['sampai'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ];

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            // BOM agar kolom terbaca benar di Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Laporan', self::JENIS[$data['jenis']]]);
            fputcsv($out, ['Periode', $data['dari'] . ' s/d ' . $data['sampai']]);
            fputcsv($out, []);
            fputcsv($out, $data['columns']);
            foreach ($data['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $nama, $headers);
    }

    private function filters(Request $request): array
    {
        $v = $request->validate([
            'jenis' => ['nullable', 'in:' . implode(',', array_keys(self::JENIS))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerja,id'],
            'nip' => ['nullable', 'string', 'max:30'],
        ]);

        return [
            'jenis' => $v['jenis'] ?? array_key_first(self::JENIS),
            'dari' => !empty($v['dari']) ? $v['dari'] : Carbon::today()->startOfMonth()->toDateString(),
            'sampai' => !empty($v['sampai']) ? $v['sampai'] : Carbon::today()->toDateString(),
            'unitId' => isset($v['unit_kerja_id']) ? (int) $v['unit_kerja_id'] : null,
            'nip' => trim((string) ($v['nip'] ?? '')),
        ];
    }

    private function build(Request $request): array
    {
        $f = $this->filters($request);

        return match ($f['jenis']) {
            'presensi' => $this->rekapPresensi($f),
            'surat_masuk' => $this->rekapSurat($f),
            default => $this->rekapPengajuan($f),
        };
    }

    // Kolom date tersimpan sebagai datetime; rentang harus mencakup seluruh hari batas.
    private function awal(array $f): Carbon
    {
        return Carbon::parse($f['dari'])->startOfDay();
    }

    private function akhir(array $f): Carbon
    {
        return Carbon::parse($f['sampai'])->endOfDay();
    }

    private function rekapPresensi(array $f): array
    {
        $q = PresensiApel::with('pegawai.unitKerja')
            ->whereBetween('tanggal', [$this->awal($f), $this->akhir($f)]);

        if ($f['unitId']) {
            $q->whereHas('pegawai', fn ($u) => $u->where('unit_kerja_id', $f['unitId']));
        }
        if ($f['nip'] !== '') {
            $q->whereHas('pegawai', fn ($u) => $u->where('nip', 'like', '%' . $f['nip'] . '%'));
        }

        $records = $q->get();

        $perPegawai = $records->groupBy('pegawai_id')->map(function ($group) {
            $p = $group->first()->pegawai;

            return [
                $p->nip ?? '-',
                $p->nama ?? '-',
                optional($p->unitKerja)->nama ?? '-',
                (string) $group->where('sesi', 'pagi')->where('status_validasi', 'hadir')->count(),
                (string) $group->where('sesi', 'sore')->where('status_validasi', 'hadir')->count(),
                (string) $group->count(),
            ];
        })->values()->all();

        $gallery = $records->map(fn ($r) => [
            'foto' => $r->foto_path,
            'nama' => optional($r->pegawai)->nama ?? '-',
            'tanggal' => optional($r->tanggal)->format('d/m/Y'),
            'sesi' => $r->sesi,
            'status' => $r->status_validasi,
        ])->values()->all();

        return $f + [
            'columns' => ['NIP', 'Nama', 'Unit Kerja', 'Hadir Pagi', 'Hadir Sore', 'Total Rekaman'],
            'rows' => $perPegawai,
            'summary' => [
                'Total rekaman' => (string) $records->count(),
                'Hadir pagi' => (string) $records->where('sesi', 'pagi')->where('status_validasi', 'hadir')->count(),
                'Hadir sore' => (string) $records->where('sesi', 'sore')->where('status_validasi', 'hadir')->count(),
            ],
            'gallery' => $gallery,
        ];
    }

    private function rekapPengajuan(array $f): array
    {
        $q = Pengajuan::with('pegawai.unitKerja')
            ->where('jenis_layanan', $f['jenis'])
            ->whereBetween('tanggal_ajuan', [$this->awal($f), $this->akhir($f)]);

        if ($f['unitId']) {
            $q->whereHas('pegawai', fn ($u) => $u->where('unit_kerja_id', $f['unitId']));
        }
        if ($f['nip'] !== '') {
            $q->whereHas('pegawai', fn ($u) => $u->where('nip', 'like', '%' . $f['nip'] . '%'));
        }

        $items = $q->orderBy('tanggal_ajuan')->get();

        $rows = $items->map(fn ($p) => [
            $p->nomor_ajuan ?? '-',
            optional($p->pegawai)->nama ?? '-',
            optional($p->pegawai)->nip ?? '-',
            optional(optional($p->pegawai)->unitKerja)->nama ?? '-',
            $p->labelStatus(),
            optional($p->tanggal_ajuan)->format('d/m/Y') ?? '-',
        ])->all();

        $summary = $items->groupBy('status')
            ->map(fn ($g, $status) => (string) $g->count())
            ->mapWithKeys(fn ($count, $status) => [Pengajuan::STATUS[$status] ?? $status => $count])
            ->all();

        return $f + [
            'columns' => ['Nomor Ajuan', 'Pemohon', 'NIP', 'Unit Kerja', 'Status', 'Tanggal Ajuan'],
            'rows' => $rows,
            'summary' => $summary + ['Total pengajuan' => (string) $items->count()],
            'gallery' => [],
        ];
    }

    private function rekapSurat(array $f): array
    {
        $items = SuratMasuk::with('pencatat')
            ->whereBetween('tanggal_terima', [$this->awal($f), $this->akhir($f)])
            ->orderBy('tanggal_terima')->get();

        $rows = $items->map(fn ($s) => [
            $s->nomor_agenda ?? '-',
            $s->nomor_surat,
            $s->perihal,
            $s->asal_surat ?? '-',
            $s->klasifikasi ?? '-',
            $s->labelStatus(),
            optional($s->tanggal_terima)->format('d/m/Y'),
        ])->all();

        $summary = $items->groupBy('status')
            ->map(fn ($g, $status) => (string) $g->count())
            ->mapWithKeys(fn ($count, $status) => [SuratMasuk::STATUS[$status] ?? $status => $count])
            ->all();

        return $f + [
            'columns' => ['No. Agenda', 'No. Surat', 'Perihal', 'Asal', 'Klasifikasi', 'Status', 'Diterima'],
            'rows' => $rows,
            'summary' => $summary + ['Total surat' => (string) $items->count()],
            'gallery' => [],
        ];
    }
}
