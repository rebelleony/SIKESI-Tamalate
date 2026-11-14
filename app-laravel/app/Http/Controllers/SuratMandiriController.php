<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PengajuanSuratMandiri;
use App\Models\SuratKeluar;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratMandiriController extends Controller
{
    /**
     * Halaman publik Portal Layanan Surat Mandiri (gaya portal layanan kelurahan).
     * Bisa diakses oleh pegawai/staf kelurahan tanpa harus login berulang kali.
     */
    public function portal(Request $request)
    {
        $units = UnitKerja::orderBy('tipe')->orderBy('nama')->get();
        $jenisSurat = PengajuanSuratMandiri::JENIS_SURAT;

        return view('surat_mandiri.portal', compact('units', 'jenisSurat'));
    }

    /**
     * Simpan pengajuan surat mandiri & hasilkan kode tracking unik.
     */
    public function submitPortal(Request $request)
    {
        $data = $request->validate([
            'jenis_surat'    => ['required', 'in:' . implode(',', PengajuanSuratMandiri::JENIS_SURAT)],
            'nama_pemohon'   => ['required', 'string', 'max:160'],
            'nip_nik'        => ['nullable', 'string', 'max:30'],
            'unit_asal'      => ['required', 'string', 'max:160'],
            'no_hp'          => ['nullable', 'string', 'max:25'],
            'perihal'        => ['required', 'string', 'max:255'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
            'file_pendukung' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
        ]);

        $path = null;
        if ($request->hasFile('file_pendukung')) {
            $path = $request->file('file_pendukung')->store('surat_mandiri', 'local');
        }

        $kodeTracking = PengajuanSuratMandiri::generateKodeTracking();

        $pengajuan = PengajuanSuratMandiri::create($data + [
            'kode_tracking'  => $kodeTracking,
            'file_pendukung' => $path,
            'status'         => 'diajukan',
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('surat-mandiri.tracking', ['kode' => $pengajuan->kode_tracking])
            ->with('success', "Pengajuan surat berhasil dikirim! Simpan Kode Tracking Anda: {$pengajuan->kode_tracking}");
    }

    /**
     * Halaman publik Tracking Surat Mandiri secara real-time tanpa login.
     */
    public function tracking(Request $request)
    {
        $kode = trim((string) $request->query('kode'));
        $hasil = null;

        if ($kode !== '') {
            $hasil = PengajuanSuratMandiri::with(['suratKeluar.kodeKlasifikasi', 'pemroses'])
                ->where('kode_tracking', $kode)
                ->first();
        }

        return view('surat_mandiri.tracking', compact('kode', 'hasil'));
    }

    /**
     * Daftar pengajuan surat mandiri untuk Operator / Pimpinan / Super Admin.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        $daftar = PengajuanSuratMandiri::with(['suratKeluar', 'pemroses'])
            ->when($status, fn ($b) => $b->where('status', $status))
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('kode_tracking', 'like', "%{$q}%")
                        ->orWhere('nama_pemohon', 'like', "%{$q}%")
                        ->orWhere('unit_asal', 'like', "%{$q}%")
                        ->orWhere('perihal', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $suratKeluarList = SuratKeluar::orderByDesc('id')->take(50)->get();

        return view('surat_mandiri.index', compact('daftar', 'status', 'q', 'suratKeluarList'));
    }

    /**
     * Proses / perbarui status pengajuan surat mandiri oleh Operator / Pimpinan / Super Admin.
     */
    public function updateStatus(Request $request, PengajuanSuratMandiri $pengajuan)
    {
        $data = $request->validate([
            'status'          => ['required', 'in:diajukan,diproses_operator,menunggu_pimpinan,selesai,ditolak'],
            'catatan_admin'   => ['nullable', 'string', 'max:1000'],
            'surat_keluar_id' => ['nullable', 'exists:surat_keluar,id'],
        ]);

        if ($data['status'] === 'ditolak' && blank($data['catatan_admin'])) {
            return back()->withErrors(['catatan_admin' => 'Alasan penolakan wajib diisi.']);
        }

        $update = [
            'status'          => $data['status'],
            'catatan_admin'   => $data['catatan_admin'] ?? $pengajuan->catatan_admin,
            'surat_keluar_id' => $data['surat_keluar_id'] ?? $pengajuan->surat_keluar_id,
            'diproses_oleh'   => $request->user()->id,
        ];

        if ($data['status'] === 'diproses_operator' && ! $pengajuan->waktu_diproses) {
            $update['waktu_diproses'] = now();
        }
        if (in_array($data['status'], ['selesai', 'ditolak'], true)) {
            $update['waktu_selesai'] = now();
        }

        $pengajuan->update($update);

        AuditLog::catat(
            $request->user()->id,
            'proses_surat_mandiri',
            'pengajuan_surat_mandiri',
            $pengajuan->id,
            "Status pengajuan {$pengajuan->kode_tracking} diubah menjadi {$data['status']}"
        );

        return back()->with('success', "Status pengajuan {$pengajuan->kode_tracking} berhasil diperbarui.");
    }

    public function unduh(PengajuanSuratMandiri $pengajuan)
    {
        abort_unless($pengajuan->file_pendukung, 404, 'Lampiran tidak ditemukan.');

        return Storage::disk('local')->download($pengajuan->file_pendukung);
    }
}

