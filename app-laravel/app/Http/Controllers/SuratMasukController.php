<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KodeKlasifikasi;
use App\Models\SuratMasuk;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('status');

        $surat = SuratMasuk::with(['pencatat', 'kodeKlasifikasi', 'tujuanUnit'])
            ->when($status, fn ($b) => $b->where('status', $status))
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nomor_agenda', 'like', "%{$q}%")
                        ->orWhere('nomor_surat', 'like', "%{$q}%")
                        ->orWhere('asal_surat', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            })
            ->latest('tanggal_terima')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('surat.index', compact('surat', 'q', 'status'));
    }

    public function create()
    {
        $nextNoAgenda = (SuratMasuk::max('no_agenda') ?? 0) + 1;
        $klasifikasiList = KodeKlasifikasi::orderBy('kode')->get();

        return view('surat.create', [
            'sifat'           => SuratMasuk::SIFAT,
            'nextNoAgenda'    => $nextNoAgenda,
            'klasifikasiList' => $klasifikasiList,
        ]);
    }

    // SOP langkah 1: menerima, memilah, mencatat dalam agenda + generate Lembar Disposisi.
    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_surat'         => ['required', 'string', 'max:60'],
            'kode_klasifikasi_id' => ['nullable', 'exists:kode_klasifikasi,id'],
            'klasifikasi'         => ['nullable', 'string', 'max:60'],
            'tanggal_surat'       => ['required', 'date'],
            'tanggal_terima'      => ['required', 'date'],
            'asal_surat'          => ['required', 'string', 'max:160'],
            'tujuan'              => ['nullable', 'string', 'max:160'],
            'perihal'             => ['required', 'string', 'max:255'],
            'keterangan'          => ['nullable', 'string', 'max:1000'],
            'sifat'               => ['required', 'in:' . implode(',', SuratMasuk::SIFAT)],
            'disposisi'           => ['nullable', 'string', 'max:500'],
            'file'                => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat_masuk', 'local');
        }

        if (! empty($data['kode_klasifikasi_id']) && empty($data['klasifikasi'])) {
            $kodeObj = KodeKlasifikasi::find($data['kode_klasifikasi_id']);
            if ($kodeObj) {
                $data['klasifikasi'] = $kodeObj->kode . ' - ' . $kodeObj->nama;
            }
        }

        $surat = DB::transaction(function () use ($data, $path, $request) {
            $noAgenda = (SuratMasuk::max('no_agenda') ?? 0) + 1;
            $tahun = now()->year;
            $nomorAgenda = sprintf('ASM/%d/%04d', $tahun, $noAgenda);

            $created = SuratMasuk::create($data + [
                'no_agenda'    => $noAgenda,
                'nomor_agenda' => $nomorAgenda,
                'file_path'    => $path,
                'status'       => 'dicatat',
                'dicatat_oleh' => $request->user()->id,
            ]);

            AuditLog::catat(
                $request->user()->id,
                'catat_surat_masuk',
                'surat_masuk',
                $created->id,
                "Mencatat surat masuk {$created->nomor_agenda} dari {$created->asal_surat}"
            );

            return $created;
        });

        return redirect()->route('surat.show', $surat)
            ->with('success', 'Surat masuk tercatat dalam agenda: ' . $surat->nomor_agenda);
    }

    public function show(SuratMasuk $surat)
    {
        $surat->load([
            'kodeKlasifikasi', 'tujuanUnit', 'pencatat', 'pemaraf',
            'pendisposisi', 'pendistribusi', 'penolak', 'arsip.pencatat',
        ]);

        $unitsDisposisi = UnitKerja::whereIn('tipe', ['seksi', 'subbag', 'kelurahan'])
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();

        return view('surat.show', compact('surat', 'unitsDisposisi'));
    }

    /**
     * Cetak / pratinjau Lembar Disposisi Kecamatan Tamalate.
     */
    public function lembarDisposisi(SuratMasuk $surat)
    {
        $surat->load(['kodeKlasifikasi', 'tujuanUnit', 'pencatat', 'pemaraf', 'pendisposisi', 'pendistribusi']);

        return view('surat.disposisi', compact('surat'));
    }

    // SOP langkah 2: Sekcam / Pimpinan memeriksa & memberi Paraf Digital pada lembar disposisi.
    public function paraf(Request $request, SuratMasuk $surat)
    {
        abort_unless(
            $request->user()->hasRole('verifikator_pimpinan', 'pimpinan'),
            403,
            'Hanya Sekretaris Camat / Pimpinan yang berwenang memberi paraf disposisi.'
        );
        abort_unless($surat->status === 'dicatat', 400, 'Status surat tidak berada pada tahap paraf Sekcam.');

        $token = hash('sha256', $surat->id . '|' . $request->user()->id . '|' . now()->toIso8601String() . '|' . Str::random(16));

        $surat->update([
            'status'      => 'diparaf_sekcam',
            'paraf_oleh'  => $request->user()->id,
            'paraf_waktu' => now(),
            'paraf_token' => $token,
        ]);

        AuditLog::catat(
            $request->user()->id,
            'paraf_surat_masuk',
            'surat_masuk',
            $surat->id,
            "Paraf digital diberikan untuk surat {$surat->nomor_agenda}"
        );

        return back()->with('success', 'Surat diperiksa dan diberi Paraf Digital pada lembar disposisi.');
    }

    // SOP langkah 3: Camat menandatangani & memilih tujuan disposisi (Seksi/Subbag).
    public function disposisi(Request $request, SuratMasuk $surat)
    {
        abort_unless($request->user()->hasRole('pimpinan'), 403, 'Hanya Camat/Pimpinan yang berwenang mendisposisikan surat.');
        abort_unless($surat->status === 'diparaf_sekcam', 400, 'Status surat harus sudah diparaf Sekcam sebelum didisposisi.');

        $data = $request->validate([
            'tujuan_unit_id'   => ['nullable', 'exists:unit_kerja,id'],
            'tujuan_disposisi' => ['required', 'string', 'max:500'],
        ]);

        $unitNama = null;
        if (! empty($data['tujuan_unit_id'])) {
            $unitNama = UnitKerja::find($data['tujuan_unit_id'])?->nama;
        }

        $tujuanTeks = $unitNama
            ? "[{$unitNama}] " . $data['tujuan_disposisi']
            : $data['tujuan_disposisi'];

        $token = hash('sha256', 'DISP|' . $surat->id . '|' . $request->user()->id . '|' . now()->toIso8601String() . '|' . Str::random(16));

        $surat->update([
            'tujuan_unit_id'   => $data['tujuan_unit_id'] ?? null,
            'tujuan_disposisi' => $tujuanTeks,
            'status'           => 'didisposisi',
            'disposisi_oleh'   => $request->user()->id,
            'disposisi_waktu'  => now(),
            'disposisi_token'  => $token,
        ]);

        AuditLog::catat(
            $request->user()->id,
            'disposisi_surat_masuk',
            'surat_masuk',
            $surat->id,
            "Surat {$surat->nomor_agenda} didisposisi ke: {$tujuanTeks}"
        );

        return back()->with('success', 'Surat telah ditandatangani & didisposisi Camat.');
    }

    // Opsi "Tolak Surat" pada tahap Sekcam/Camat dengan kolom wajib alasan penolakan.
    public function tolak(Request $request, SuratMasuk $surat)
    {
        abort_unless(
            $request->user()->hasRole('verifikator_pimpinan', 'pimpinan'),
            403,
            'Hanya Sekcam atau Camat/Pimpinan yang berwenang menolak surat.'
        );
        abort_unless(
            in_array($surat->status, ['dicatat', 'diparaf_sekcam'], true),
            400,
            'Surat hanya dapat ditolak pada tahap pemeriksaan Sekcam atau disposisi Camat.'
        );

        $data = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        $surat->update([
            'status'           => 'ditolak',
            'alasan_penolakan' => $data['alasan_penolakan'],
            'ditolak_oleh'     => $request->user()->id,
            'ditolak_waktu'    => now(),
        ]);

        AuditLog::catat(
            $request->user()->id,
            'tolak_surat_masuk',
            'surat_masuk',
            $surat->id,
            "Surat {$surat->nomor_agenda} ditolak: {$data['alasan_penolakan']}"
        );

        return back()->with('success', 'Surat masuk telah ditolak dengan alasan tercatat.');
    }

    // SOP langkah 4: Operator mendistribusikan surat ke unit tujuan.
    public function distribusi(Request $request, SuratMasuk $surat)
    {
        abort_unless($request->user()->hasRole('operator'), 403, 'Hanya Operator/Staf Arsip yang berwenang mendistribusikan surat.');
        abort_unless($surat->status === 'didisposisi', 400, 'Status surat harus sudah didisposisi sebelum didistribusikan.');

        $surat->update([
            'status'           => 'didistribusikan',
            'distribusi_oleh'  => $request->user()->id,
            'distribusi_waktu' => now(),
        ]);

        AuditLog::catat(
            $request->user()->id,
            'distribusi_surat_masuk',
            'surat_masuk',
            $surat->id,
            "Surat {$surat->nomor_agenda} didistribusikan ke unit tujuan"
        );

        return back()->with('success', 'Surat telah didistribusikan sesuai disposisi.');
    }

    public function unduh(Request $request, SuratMasuk $surat)
    {
        abort_unless($surat->file_path, 404, 'Berkas tidak ada.');

        return Storage::disk('local')->download($surat->file_path);
    }
}
