<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\Pengajuan;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $arsip = Arsip::with(['suratMasuk', 'pengajuan', 'pencatat', 'penandatangan'])
            ->latest()->paginate(15);

        return view('arsip.index', compact('arsip'));
    }

    public function create()
    {
        return view('arsip.create', [
            'suratMasuk' => SuratMasuk::whereIn('status', ['didistribusikan', 'diarsipkan'])
                ->orderByDesc('tanggal_terima')->get(),
            'pengajuan' => Pengajuan::whereIn('status', Pengajuan::STATUS_SELESAI)
                ->orderByDesc('id')->get(),
            'penandatangan' => User::whereHas('role', fn ($q) => $q->whereIn('nama_role', ['pimpinan', 'verifikator_pimpinan']))
                ->orderBy('nama')->get(),
            'kodeKlasifikasi' => KodeKlasifikasi::orderBy('kode')->get(),
            'statusDokumen' => Arsip::STATUS_DOKUMEN,
            'lokasi' => Arsip::LOKASI,
        ]);
    }

    // SOP pengarsipan: data dokumen -> daftar arsip (ttd Sekcam/Camat) -> kode tata naskah -> simpan.
    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis_dokumen' => ['required', 'in:surat_masuk,surat_keluar,dokumen_lain'],
            'surat_masuk_id' => ['nullable', 'required_if:jenis_dokumen,surat_masuk', 'exists:surat_masuk,id'],
            'pengajuan_id' => ['nullable', 'required_if:jenis_dokumen,surat_keluar', 'exists:pengajuan,id'],
            'judul' => ['required', 'string', 'max:160'],
            'kode_arsip' => ['required', 'string', 'max:60'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'status_dokumen' => ['required', 'in:aktif,tidak_aktif,penting'],
            'lokasi' => ['required', 'in:lemari_arsip,gudang_arsip,lurah'],
            'nomor_daftar_arsip' => ['nullable', 'string', 'max:60'],
            'ditandatangai_oleh' => ['nullable', 'exists:user,id'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $arsip = Arsip::create($data + ['dicatat_oleh' => $request->user()->id]);

            // SOP surat masuk langkah 5: arsipkan surat sesuai nomor surat.
            if ($arsip->surat_masuk_id) {
                SuratMasuk::whereKey($arsip->surat_masuk_id)->update(['status' => 'diarsipkan']);
            }
        });

        return redirect()->route('arsip.index')
            ->with('success', 'Dokumen terarsip dengan kode ' . $data['kode_arsip'] . '.');
    }
}
