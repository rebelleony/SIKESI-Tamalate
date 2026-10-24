<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $surat = SuratMasuk::with('pencatat')
            ->latest('tanggal_terima')->paginate(15);

        return view('surat.index', compact('surat'));
    }

    public function create()
    {
        return view('surat.create', ['sifat' => SuratMasuk::SIFAT]);
    }

    // SOP langkah 1: menerima, memilah, mencatat dalam agenda + lembar disposisi.
    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:60'],
            'klasifikasi' => ['nullable', 'string', 'max:60'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_terima' => ['required', 'date'],
            'asal_surat' => ['nullable', 'string', 'max:160'],
            'tujuan' => ['nullable', 'string', 'max:160'],
            'perihal' => ['required', 'string', 'max:255'],
            'sifat' => ['required', 'in:' . implode(',', SuratMasuk::SIFAT)],
            'disposisi' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat_masuk', 'local');
        }

        $surat = SuratMasuk::create($data + [
            'file_path' => $path,
            'status' => 'dicatat',
            'dicatat_oleh' => $request->user()->id,
        ]);

        $surat->update(['nomor_agenda' => sprintf('ASM/%d/%04d', now()->year, $surat->id)]);

        return redirect()->route('surat.show', $surat)
            ->with('success', 'Surat masuk tercatat dalam agenda: ' . $surat->nomor_agenda);
    }

    public function show(SuratMasuk $surat)
    {
        $surat->load(['pencatat', 'pemaraf', 'pendisposisi', 'pendistribusi', 'arsip.pencatat']);

        return view('surat.show', compact('surat'));
    }

    // SOP langkah 2: Sekcam memeriksa & memberi paraf pada lembar disposisi.
    public function paraf(Request $request, SuratMasuk $surat)
    {
        abort_unless($request->user()->hasRole('seksam', 'super_admin'), 403);
        abort_unless($surat->status === 'dicatat', 400, 'Status tidak sesuai.');

        $surat->update([
            'status' => 'diparaf_sekcam',
            'paraf_oleh' => $request->user()->id,
            'paraf_waktu' => now(),
        ]);

        return back()->with('success', 'Surat diperiksa dan diparaf Sekretaris Camat.');
    }

    // SOP langkah 3: Camat mendisposisi & menandatangani lembar disposisi sesuai tujuan.
    public function disposisi(Request $request, SuratMasuk $surat)
    {
        abort_unless($request->user()->hasRole('camat_lurah', 'super_admin'), 403);
        abort_unless($surat->status === 'diparaf_sekcam', 400, 'Status tidak sesuai.');

        $data = $request->validate([
            'tujuan_disposisi' => ['required', 'string', 'max:500'],
        ]);

        $surat->update($data + [
            'status' => 'didisposisi',
            'disposisi_oleh' => $request->user()->id,
            'disposisi_waktu' => now(),
        ]);

        return back()->with('success', 'Surat telah didisposisi Camat.');
    }

    // SOP langkah 4: mendistribusikan surat sesuai disposisi.
    public function distribusi(Request $request, SuratMasuk $surat)
    {
        abort_unless($request->user()->hasRole('verifikator', 'super_admin'), 403);
        abort_unless($surat->status === 'didisposisi', 400, 'Status tidak sesuai.');

        $surat->update([
            'status' => 'didistribusikan',
            'distribusi_oleh' => $request->user()->id,
            'distribusi_waktu' => now(),
        ]);

        return back()->with('success', 'Surat telah didistribusikan sesuai disposisi.');
    }

    public function unduh(Request $request, SuratMasuk $surat)
    {
        abort_unless($surat->file_path, 404, 'Berkas tidak ada.');

        return Storage::disk('local')->download($surat->file_path);
    }
}
