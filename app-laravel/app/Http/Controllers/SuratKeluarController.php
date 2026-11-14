<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KodeKlasifikasi;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $klasifikasiId = $request->query('kode_klasifikasi_id');

        $surat = SuratKeluar::with(['kodeKlasifikasi', 'pencatat'])
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nomor_surat', 'like', "%{$q}%")
                        ->orWhere('instansi_tujuan', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%")
                        ->orWhere('penanggung_jawab', 'like', "%{$q}%");
                });
            })
            ->when($klasifikasiId, fn ($b) => $b->where('kode_klasifikasi_id', $klasifikasiId))
            ->latest('tgl_surat')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $klasifikasiList = KodeKlasifikasi::orderBy('kode')->get();

        return view('surat_keluar.index', compact('surat', 'klasifikasiList', 'q', 'klasifikasiId'));
    }

    public function create()
    {
        $klasifikasiList = KodeKlasifikasi::orderBy('kode')->get();
        $tahun = now()->year;
        $nextNoUrut = SuratKeluar::nextNoUrut($tahun);

        return view('surat_keluar.create', [
            'klasifikasiList' => $klasifikasiList,
            'nextNoUrut'      => $nextNoUrut,
            'tahun'           => $tahun,
            'bulanRomawi'     => SuratKeluar::BULAN_ROMAWI,
        ]);
    }

    public function previewNomor(Request $request)
    {
        $klasifikasiId = $request->query('kode_klasifikasi_id');
        $tglSurat = $request->query('tgl_surat', now()->toDateString());

        $date = Carbon::parse($tglSurat);
        $noUrut = SuratKeluar::nextNoUrut($date->year);
        $kode = KodeKlasifikasi::find($klasifikasiId)?->kode ?? '000';

        return response()->json([
            'no_urut'     => $noUrut,
            'nomor_surat' => SuratKeluar::formatNomorSurat($kode, $noUrut, $date),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'instansi_tujuan'     => ['required', 'string', 'max:180'],
            'kode_klasifikasi_id' => ['required', 'exists:kode_klasifikasi,id'],
            'tgl_surat'           => ['required', 'date'],
            'perihal'             => ['required', 'string', 'max:255'],
            'penanggung_jawab'    => ['required', 'string', 'max:160'],
            'keterangan'          => ['nullable', 'string', 'max:1000'],
            'file'                => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat_keluar', 'local');
        }

        $surat = DB::transaction(function () use ($data, $path, $request) {
            $date = Carbon::parse($data['tgl_surat']);
            $tahun = $date->year;
            $noUrut = SuratKeluar::nextNoUrut($tahun);
            $kode = KodeKlasifikasi::findOrFail($data['kode_klasifikasi_id']);
            $nomorSurat = SuratKeluar::formatNomorSurat($kode->kode, $noUrut, $date);

            $created = SuratKeluar::create([
                'no_urut'             => $noUrut,
                'tahun'               => $tahun,
                'kode_klasifikasi_id' => $kode->id,
                'nomor_surat'         => $nomorSurat,
                'instansi_tujuan'     => $data['instansi_tujuan'],
                'tgl_surat'           => $date->toDateString(),
                'perihal'             => $data['perihal'],
                'penanggung_jawab'    => $data['penanggung_jawab'],
                'keterangan'          => $data['keterangan'] ?? null,
                'file_path'           => $path,
                'dicatat_oleh'        => $request->user()->id,
            ]);

            AuditLog::catat(
                $request->user()->id,
                'catat_surat_keluar',
                'surat_keluar',
                $created->id,
                "Mencatat surat keluar {$created->nomor_surat} ke {$created->instansi_tujuan}"
            );

            return $created;
        });

        return redirect()->route('surat-keluar.show', $surat)
            ->with('success', "Surat keluar berhasil dicatat dengan nomor: {$surat->nomor_surat}");
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['kodeKlasifikasi', 'pencatat']);

        return view('surat_keluar.show', ['surat' => $suratKeluar]);
    }

    public function unduh(SuratKeluar $suratKeluar)
    {
        abort_unless($suratKeluar->file_path, 404, 'Berkas surat keluar tidak tersedia.');

        return Storage::disk('local')->download($suratKeluar->file_path);
    }
}

