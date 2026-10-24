<?php

namespace App\Http\Controllers;

use App\Models\PresensiApel;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    // Jendela apel sesuai SOP Pembinaan Pegawai (zona waktu aplikasi: Asia/Makassar / WITA).
    private const JENDELA = [
        'pagi' => ['mulai' => [7, 0], 'selesai' => [8, 30]],
        'sore' => ['mulai' => [15, 30], 'selesai' => [17, 0]],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $sesiAktif = $this->sesiAktif();

        $hariIni = PresensiApel::where('pegawai_id', $user->id)
            ->whereDate('tanggal', today())
            ->get()
            ->keyBy('sesi');

        $riwayat = PresensiApel::where('pegawai_id', $user->id)
            ->latest('tanggal')->latest('id')
            ->paginate(15);

        return view('presensi.index', [
            'hariIni' => $hariIni,
            'riwayat' => $riwayat,
            'sesiAktif' => $sesiAktif,
            'rekapPekan' => $this->rekapPekan($user->id),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $sesi = $this->sesiAktif();
        abort_if($sesi === null, 400, 'Presensi hanya dapat dilakukan pada jendela apel pagi (07.00-08.30) atau sore (15.30-17.00) WITA.');

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $sudah = PresensiApel::where('pegawai_id', $user->id)
            ->whereDate('tanggal', today())
            ->where('sesi', $sesi)
            ->exists();
        abort_if($sudah, 400, "Anda sudah melakukan presensi apel {$sesi} hari ini.");

        $path = $request->file('foto')->store('presensi/' . now()->format('Y/m/d'), 'public');

        PresensiApel::create([
            'pegawai_id' => $user->id,
            'tanggal' => today()->toDateString(),
            'sesi' => $sesi,
            'jam' => now()->format('H:i:s'),
            'foto_path' => $path,
            'status_validasi' => 'menunggu',
            'created_at' => now(),
        ]);

        return back()->with('success', "Presensi apel {$sesi} berhasil dicatat (Menunggu Validasi).");
    }

    public function validasi(Request $request)
    {
        abort_unless($request->user()->hasRole('verifikator', 'super_admin'), 403);

        $data = $request->validate([
            'presensi_id' => ['required', 'exists:presensi_apel,id'],
            'status_validasi' => ['required', 'in:hadir,izin,sakit,alpa'],
        ]);

        PresensiApel::where('id', $data['presensi_id'])->update([
            'status_validasi' => $data['status_validasi'],
            'validasi_oleh' => $request->user()->id,
            'waktu_validasi' => now(),
        ]);

        return back()->with('success', 'Status kehadiran diperbarui.');
    }

    // Rekapitulasi daftar hadir per pekan (ISO week) sesuai SOP.
    private function rekapPekan(int $pegawaiId): array
    {
        $mulaiPekan = today()->startOfWeek();
        $rows = PresensiApel::where('pegawai_id', $pegawaiId)
            ->whereBetween('tanggal', [$mulaiPekan, $mulaiPekan->copy()->endOfWeek()])
            ->get();

        $rekap = ['pagi' => 0, 'sore' => 0, 'total' => 0];
        foreach ($rows as $r) {
            if (in_array($r->status_validasi, ['hadir', 'menunggu'], true)) {
                $rekap[$r->sesi] = ($rekap[$r->sesi] ?? 0) + 1;
                $rekap['total']++;
            }
        }
        $rekap['periode'] = $mulaiPekan->format('d M Y') . ' - ' . $mulaiPekan->copy()->endOfWeek()->format('d M Y');

        return $rekap;
    }

    // Mengembalikan 'pagi' | 'sore' | null bila di luar jendela.
    private function sesiAktif(): ?string
    {
        $now = now();
        foreach (self::JENDELA as $sesi => $j) {
            $mulai = $now->copy()->setTime($j['mulai'][0], $j['mulai'][1], 0);
            $selesai = $now->copy()->setTime($j['selesai'][0], $j['selesai'][1], 0);
            if ($now->betweenIncluded($mulai, $selesai)) {
                return $sesi;
            }
        }

        return null;
    }
}
