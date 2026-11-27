<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\JenisCuti;
use App\Models\Pengajuan;
use App\Models\PengajuanKepegawaian;
use App\Models\PengajuanSuratMandiri;
use App\Models\RiwayatApproval;
use App\Support\LayananKepegawaian;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $qSop = Pengajuan::with(['pegawai.unitKerja', 'unitKerja'])->latest();
        $qKep = PengajuanKepegawaian::with(['pegawai.unitKerja', 'unitKerja'])->latest();
        $qMandiri = PengajuanSuratMandiri::latest();

        if ($user->hasRole('pegawai')) {
            $qSop->where('pegawai_id', $user->id);
            $qKep->where('pegawai_id', $user->id);
            $qMandiri->where('user_id', $user->id);
        } elseif ($user->hasRole('verifikator_pimpinan')) {
            $qSop->whereIn('status', ['menunggu_approval_tier1', 'menunggu_approval_final', 'disetujui', 'ditolak_tier1']);
            $qMandiri->where('id', 0); // Surat mandiri tidak melalui Kasi/Kasubag
        } elseif ($user->hasRole('pimpinan') && $user->unitKerja && $user->unitKerja->tipe === 'kelurahan') {
            $qSop->where('unit_kerja_id', $user->unit_kerja_id);
            $qKep->where('unit_kerja_id', $user->unit_kerja_id);
            $qMandiri->where('unit_asal', $user->unitKerja->nama);
        }

        $items = collect()
            ->merge($qSop->get()->map(fn ($p) => [
                'nomor'        => $p->nomor_ajuan ?? '-',
                'pemohon'      => $p->pegawai->nama ?? '-',
                'unit'         => $p->unitKerja->nama ?? ($p->pegawai->unitKerja->nama ?? '-'),
                'layanan'      => ucfirst(str_replace('_', ' ', $p->jenis_layanan)),
                'sumber'       => 'Pengajuan SOP',
                'status_label' => $p->labelStatus(),
                'tanggal'      => optional($p->tanggal_ajuan)->format('d/m/Y') ?? optional($p->created_at)->format('d/m/Y'),
                'url'          => route('pengajuan.show', $p),
                'sort_at'      => $p->created_at?->timestamp ?? 0,
            ]))
            ->merge($qKep->get()->map(fn ($k) => [
                'nomor'        => $k->nomor_pengajuan,
                'pemohon'      => $k->pegawai->nama ?? '-',
                'unit'         => $k->unitKerja->nama ?? ($k->pegawai->unitKerja->nama ?? '-'),
                'layanan'      => $k->labelJenis(),
                'sumber'       => 'Kepegawaian',
                'status_label' => $k->labelStatus(),
                'tanggal'      => optional($k->created_at)->format('d/m/Y'),
                'url'          => route('kepegawaian.show', $k),
                'sort_at'      => $k->created_at?->timestamp ?? 0,
            ]))
            ->merge($qMandiri->get()->map(fn ($m) => [
                'nomor'        => $m->kode_tracking,
                'pemohon'      => $m->nama_pemohon,
                'unit'         => $m->unit_asal,
                'layanan'      => $m->jenis_surat . ' — ' . $m->perihal,
                'sumber'       => 'Surat Mandiri',
                'status_label' => $m->labelStatus(),
                'tanggal'      => optional($m->created_at)->format('d/m/Y'),
                'url'          => $user->hasRole('pegawai')
                    ? route('surat-mandiri.tracking', ['kode' => $m->kode_tracking])
                    : route('surat-mandiri.index', ['q' => $m->kode_tracking]),
                'sort_at'      => $m->created_at?->timestamp ?? 0,
            ]))
            ->sortByDesc('sort_at')
            ->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pengajuan = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        // Super admin dan role pimpinan tidak bertindak sebagai pemohon layanan
        abort_if($user->hasRole('super_admin'), 403, 'Super Admin tidak bertindak sebagai pemohon layanan.');
        abort_unless($user->hasRole('pegawai'), 403, 'Hanya Pegawai/Staf yang dapat mengajukan layanan.');
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

        abort_if($user->hasRole('super_admin'), 403, 'Super Admin tidak bertindak sebagai pemohon layanan.');
        abort_unless($user->hasRole('pegawai'), 403, 'Hanya Pegawai/Staf yang dapat mengajukan layanan.');
        abort_unless($user->berhakLayanan(), 403, 'Anda tidak berhak mengajukan layanan ini.');

        $base = $request->validate([
            'jenis_layanan' => ['required', 'in:cuti,kgb,kenaikan_pangkat,pensiun'],
            'berkas' => ['required', 'array', 'min:1'],
            'berkas.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
        ]);

        $jenis = $base['jenis_layanan'];

        $data = $request->validate(LayananKepegawaian::rulesFor($jenis));

        LayananKepegawaian::assertEligible($jenis, $user);

        $pengajuan = null;

        DB::transaction(function () use ($user, $jenis, $data, $request, &$pengajuan) {
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

            RiwayatApproval::create([
                'pengajuan_id' => $pengajuan->id,
                'pejabat_id' => $user->id,
                'tahap' => 'pengajuan_awal',
                'keputusan' => 'diajukan',
                'catatan' => 'Permohonan layanan diajukan oleh pegawai.',
                'waktu' => now(),
            ]);

            AuditLog::catat(
                $user->id,
                'buat_pengajuan',
                'pengajuan',
                $pengajuan->id,
                "Pengajuan {$jenis} dibuat dengan nomor {$pengajuan->nomor_ajuan}"
            );
        });

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan berhasil dikirim dan menunggu verifikasi berkas oleh Verifikator.');
    }

    /** Pegawai mengunggah berkas perbaikan apabila status pengajuan 'perlu_revisi'. */
    public function resubmitRevisi(Request $request, Pengajuan $pengajuan)
    {
        $user = $request->user();

        abort_unless($pengajuan->pegawai_id === $user->id, 403, 'Hanya pemohon yang berwenang mengunggah berkas revisi.');
        abort_unless($pengajuan->status === 'perlu_revisi', 400, 'Pengajuan ini tidak berstatus perlu revisi.');

        $request->validate([
            'berkas' => ['required', 'array', 'min:1'],
            'berkas.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $pengajuan, $user) {
            $maxVersi = (int) $pengajuan->berkas()->max('versi');
            $versiBaru = $maxVersi + 1;

            foreach ($request->file('berkas') as $i => $file) {
                $path = $file->store("pengajuan/{$pengajuan->id}", 'local');
                Berkas::create([
                    'pengajuan_id' => $pengajuan->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'path' => $path,
                    'tipe' => $file->getClientMimeType(),
                    'ukuran' => $file->getSize(),
                    'kategori' => 'revisi_' . ($i + 1),
                    'versi' => $versiBaru,
                    'uploaded_by' => $user->id,
                    'created_at' => now(),
                ]);
            }

            $pengajuan->update([
                'status' => 'menunggu_verifikasi',
                'catatan' => $request->filled('catatan') ? $request->catatan : $pengajuan->catatan,
            ]);

            AuditLog::catat(
                $user->id,
                'revisi_pengajuan',
                'pengajuan',
                $pengajuan->id,
                "Berkas revisi versi {$versiBaru} diunggah oleh {$user->nama} untuk {$pengajuan->nomor_ajuan}" . ($request->filled('catatan') ? ": {$request->catatan}" : "")
            );
        });

        return back()->with('success', 'Berkas perbaikan berhasil diunggah. Pengajuan dikembalikan ke antrean Verifikator.');
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
        $isStaff = $user->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin');

        // Pegawai lain tidak dapat mengakses data privat milik pegawai ini
        abort_unless($isOwner || $isStaff, 403, 'Anda tidak berhak mengakses data pengajuan ini.');

        // Lurah / Pejabat kelurahan hanya dapat mengakses pengajuan pada unit kelurahannya
        if (! $isOwner && $user->hasRole('pimpinan', 'verifikator_pimpinan') && $user->unitKerja && $user->unitKerja->tipe === 'kelurahan') {
            abort_unless($pengajuan->unit_kerja_id === $user->unit_kerja_id, 403, 
                'Anda hanya berhak mengakses pengajuan di kelurahan Anda.');
        }
    }
}

