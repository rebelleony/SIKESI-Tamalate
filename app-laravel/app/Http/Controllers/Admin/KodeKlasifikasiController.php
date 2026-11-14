<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KodeKlasifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KodeKlasifikasiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $klasifikasi = KodeKlasifikasi::when($q, function ($builder) use ($q) {
            $builder->where('kode', 'like', "%{$q}%")
                ->orWhere('nama', 'like', "%{$q}%");
        })->orderBy('kode')->paginate(20)->withQueryString();

        return view('admin.klasifikasi.index', compact('klasifikasi', 'q'));
    }

    public function create()
    {
        return view('admin.klasifikasi.form', [
            'item' => new KodeKlasifikasi(),
            'indukList' => KodeKlasifikasi::whereNull('induk_kode')->orderBy('kode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'       => ['required', 'string', 'max:30', 'unique:kode_klasifikasi,kode'],
            'nama'       => ['required', 'string', 'max:180'],
            'induk_kode' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        KodeKlasifikasi::create($data);

        return redirect()->route('admin.klasifikasi.index')
            ->with('success', "Kode klasifikasi {$data['kode']} berhasil ditambahkan.");
    }

    public function edit(KodeKlasifikasi $klasifikasi)
    {
        return view('admin.klasifikasi.form', [
            'item' => $klasifikasi,
            'indukList' => KodeKlasifikasi::whereNull('induk_kode')
                ->whereKeyNot($klasifikasi->id)
                ->orderBy('kode')->get(),
        ]);
    }

    public function update(Request $request, KodeKlasifikasi $klasifikasi)
    {
        $data = $request->validate([
            'kode'       => ['required', 'string', 'max:30', Rule::unique('kode_klasifikasi', 'kode')->ignore($klasifikasi->id)],
            'nama'       => ['required', 'string', 'max:180'],
            'induk_kode' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $klasifikasi->update($data);

        return redirect()->route('admin.klasifikasi.index')
            ->with('success', "Kode klasifikasi {$klasifikasi->kode} berhasil diperbarui.");
    }

    public function destroy(KodeKlasifikasi $klasifikasi)
    {
        abort_if(
            $klasifikasi->suratKeluar()->exists() || $klasifikasi->suratMasuk()->exists(),
            400,
            'Kode klasifikasi masih digunakan pada data surat masuk/keluar.'
        );

        $klasifikasi->delete();

        return back()->with('success', 'Kode klasifikasi berhasil dihapus.');
    }
}

