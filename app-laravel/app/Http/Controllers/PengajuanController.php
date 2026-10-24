<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\JenisCuti;
use App\Models\Pengajuan;
use App\Support\LayananKepegawaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $pengajuan = Pengajuan::with('unitKerja')
            ->where('pegawai_id', $request->user()->id)
            ->latest()->paginate(10);

        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_unless($user->berhakLayanan(), 403, 'PPPK Paruh Waktu tidak berhak mengajukan layanan ini.');

        return view('pengajuan.create', [
            'jenisCuti' => JenisCuti::orderBy('id')->get(),
            'pegawai' => $user,
            'labelJenis' => LayananKepegawaian::JENIS,
            'golongan' => LayananKepegawaian::GOLONGAN,
            'jenisPensiun' => LayananKepegawaian::JENIS_PENSIUN,
            'nextKgb' => LayananKepegawaian::nextKgbDate($user),
            'nextGol' => LayananKepegawaian::nextGolongan($user->pangkat_gol),
            'tmtPensiun' => LayananKepegawaian::tmtPensiun($user),
            'bup' => LayananKepegawaian::bup($user),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->berhakLayanan(), 403, 'Anda tidak berhak mengajukan layanan ini.');

        $base = $request->validate([
            'jenis_layanan' => ['required', 'in:cuti,kgb,kenaikan_pangkat,pensiun'],
            'berkas' => ['required', 'array', 'min:1'],
            'berkas.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $jenis = $base['jenis_layanan'];

        $data = $request->validate(LayananKepegawaian::rulesFor($jenis));

        LayananKepegawaian::assertEligible($jenis, $user);

        $pengajuan = Pengajuan::create([
            'pegawai_id' => $user->id,
            'unit_kerja_id' => $user->unit_kerja_id,
            'jenis_layanan' => $jenis,
            'data_form' => LayananKepegawaian::normalize($jenis, $data, $user),
            'status' => 'menunggu_verifikasi',
            'tanggal_ajuan' => now()->toDateString(),
        ]);

        $pengajuan->update(['nomor_ajuan' => $pengajuan->generateNomor()]);

        foreach ($request->file('berkas') as $i => $file) {
            $path = $file->store("pengajuan/{$pengajuan->id}", 'local');
            Berkas::create([
                'pengajuan_id' => $pengajuan->id,
                'nama_file' => $file->getClientOriginalName(),
                'path' => $path,
                'tipe' => $file->getClientMimeType(),
                'ukuran' => $file->getSize(),
                'kategori' => 'persyaratan_' . ($i + 1),
                'versi' => 1,
                'uploaded_by' => $user->id,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan berhasil dikirim dan menunggu verifikasi.');
    }

    public function show(Request $request, Pengajuan $pengajuan)
    {
        $this->authorizeView($request, $pengajuan);
        $pengajuan->load(['pegawai.unitKerja', 'berkas.uploader', 'riwayat.pejabat']);

        return view('pengajuan.show', compact('pengajuan'));
    }

    public function downloadBerkas(Request $request, Berkas $berkas)
    {
        $this->authorizeView($request, $berkas->pengajuan);

        return Storage::disk('local')->download($berkas->path, $berkas->nama_file);
    }

    private function authorizeView(Request $request, Pengajuan $pengajuan): void
    {
        $user = $request->user();
        $isOwner = $pengajuan->pegawai_id === $user->id;
        $isStaff = $user->hasRole('verifikator', 'kasi_kasubag', 'seksam', 'camat_lurah', 'super_admin');

        abort_unless($isOwner || $isStaff, 403, 'Anda tidak berhak mengakses pengajuan ini.');
    }
}
