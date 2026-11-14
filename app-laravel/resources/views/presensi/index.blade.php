@extends('layouts.app')
@section('title', 'Presensi Apel Pagi-Sore · Kecamatan Tamalate')

@section('content')
@php $u = auth()->user(); @endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Presensi Kamera Langsung &amp; Pembinaan Pegawai
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Presensi Apel Pagi &amp; Sore
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Jadwal: Apel Pagi ({{ $pengaturan->pagi_mulai }}–{{ $pengaturan->pagi_selesai }} WITA) &amp; Apel Sore ({{ $pengaturan->sore_mulai }}–{{ $pengaturan->sore_selesai }} WITA)
            @if($pengaturan->mode_testing)
                · <span class="font-bold text-amber-700">Mode Testing Aktif</span>
            @endif
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @if($u->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'))
            <a href="{{ route('presensi.rekap') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
                Rekap Bulanan &amp; Peringatan Pembinaan
            </a>
        @endif
    </div>
</div>

{{-- PANEL VALIDASI VISUAL FOTO APEL HARIAN (GRID FOTO) UNTUK OPERATOR / ADMIN --}}
@if($u->hasRole('operator', 'super_admin'))
<div class="mb-8 rounded-2xl border border-blue-200 bg-white p-6 shadow-card border-t-4 border-t-amber-500 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-display font-extrabold text-primary-950">
                Validasi Visual Foto Apel Harian ({{ $antreanValidasi->count() }} Pending)
            </h2>
            <p class="text-xs text-slate-500">Periksa foto kehadiran pegawai di barisan apel atau keterangan Izin/Dinas Luar, lalu klik Validasi / Tolak.</p>
        </div>

        <form method="GET" action="{{ route('presensi.index') }}" class="flex items-center gap-2">
            <input type="date" name="tanggal" value="{{ $tanggalFilter ?? '' }}" class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
            <button class="px-3 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-semibold">Filter Tanggal</button>
            @if($tanggalFilter)
                <a href="{{ route('presensi.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    @if($antreanValidasi->isEmpty())
        <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-100 text-sm">
            Tidak ada antrean validasi presensi yang pending.
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($antreanValidasi as $item)
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 overflow-hidden flex flex-col">
                @if($item->fotoUrl())
                    <a href="{{ $item->fotoUrl() }}" target="_blank" class="block h-44 bg-slate-200 overflow-hidden">
                        <img src="{{ $item->fotoUrl() }}" alt="Foto Apel" class="w-full h-full object-cover hover:scale-105 transition">
                    </a>
                @else
                    <div class="h-44 bg-amber-50 flex flex-col items-center justify-center p-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-200 text-amber-900 mb-2">{{ $item->labelKehadiran() }}</span>
                        <p class="text-xs text-slate-600">{{ $item->keterangan ?? 'Tanpa lampiran foto' }}</p>
                    </div>
                @endif

                <div class="p-4 flex-1 flex flex-col justify-between space-y-3 bg-white">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase">Apel {{ $item->sesi }}</span>
                            <span class="text-[11px] font-mono text-slate-500">{{ optional($item->tanggal)->format('d/m/Y') }} · {{ $item->jam }}</span>
                        </div>
                        <div class="font-bold text-sm text-slate-900 mt-1.5">{{ $item->pegawai->nama ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $item->pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}</div>
                        @if($item->keterangan)
                            <div class="text-xs text-slate-600 mt-1 italic">"{{ $item->keterangan }}"</div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('presensi.validasi') }}" class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        @csrf
                        <input type="hidden" name="presensi_id" value="{{ $item->id }}">
                        <button type="submit" name="status_validasi" value="valid"
                                class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                            ✓ Valid
                        </button>
                        <button type="submit" name="status_validasi" value="ditolak"
                                class="py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">
                            ✕ Tolak (TK)
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Konfigurasi Jam Sesi Apel & Mode Testing --}}
    <details class="border-t border-slate-100 pt-4">
        <summary class="cursor-pointer text-xs font-bold text-primary-700 hover:underline">
            ⚙ Pengaturan Jam Sesi Apel, Ambang Batas TK &amp; Mode Testing
        </summary>
        <form method="POST" action="{{ route('presensi.pengaturan') }}" class="mt-4 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end bg-slate-50 p-4 rounded-xl">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Pagi Mulai</label>
                <input type="time" name="pagi_mulai" value="{{ $pengaturan->pagi_mulai }}" class="w-full border rounded-lg px-2.5 py-1.5 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Pagi Selesai</label>
                <input type="time" name="pagi_selesai" value="{{ $pengaturan->pagi_selesai }}" class="w-full border rounded-lg px-2.5 py-1.5 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sore Mulai</label>
                <input type="time" name="sore_mulai" value="{{ $pengaturan->sore_mulai }}" class="w-full border rounded-lg px-2.5 py-1.5 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sore Selesai</label>
                <input type="time" name="sore_selesai" value="{{ $pengaturan->sore_selesai }}" class="w-full border rounded-lg px-2.5 py-1.5 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Ambang TK / Bulan</label>
                <input type="number" name="ambang_batas_tk" min="1" max="31" value="{{ $pengaturan->ambang_batas_tk }}" class="w-full border rounded-lg px-2.5 py-1.5 text-xs">
            </div>
            <div class="flex flex-col gap-2">
                <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="mode_testing" value="1" @checked($pengaturan->mode_testing)> Mode Testing
                </label>
                <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-3 py-2 rounded-lg text-xs">Simpan</button>
            </div>
        </form>
    </details>
</div>
@endif

{{-- ANTARMUKA PEGAWAI RAMAH MOBILE (PNS, PPPK PENUH WAKTU, PPPK PARUH WAKTU) --}}
<div class="grid lg:grid-cols-12 gap-6">
    <div class="lg:col-span-6 space-y-6">
        <div class="rounded-2xl border border-blue-100 bg-white p-5 sm:p-6 shadow-card border-l-4 border-l-primary-600">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h2 class="font-display font-bold text-base text-primary-950">Absensi Apel Hari Ini</h2>
                @if($pengaturan->mode_testing || config('app.debug'))
                    <div class="flex items-center gap-1 text-[11px]">
                        <a href="{{ route('presensi.index', ['sesi_demo' => 'pagi']) }}" class="px-2 py-1 rounded bg-blue-50 text-blue-700 font-bold">Demo Pagi</a>
                        <a href="{{ route('presensi.index', ['sesi_demo' => 'sore']) }}" class="px-2 py-1 rounded bg-amber-50 text-amber-800 font-bold">Demo Sore</a>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3 mb-5">
                @foreach(['pagi' => 'Apel Pagi', 'sore' => 'Apel Sore'] as $key => $label)
                    @php $rec = $hariIni[$key] ?? null; @endphp
                    <div class="p-3.5 rounded-xl border {{ $rec ? 'bg-emerald-50/70 border-emerald-200' : 'bg-slate-50 border-slate-200' }}">
                        <p class="text-[11px] font-bold uppercase tracking-wider {{ $rec ? 'text-emerald-800' : 'text-slate-500' }}">{{ $label }}</p>
                        @if($rec)
                            <p class="text-sm font-extrabold text-emerald-900 mt-1">{{ $rec->labelKehadiran() }}</p>
                            <p class="text-[11px] text-emerald-700">{{ $rec->jam }} WITA · {{ ucfirst($rec->status_validasi) }}</p>
                        @else
                            <p class="text-xs font-semibold text-slate-400 mt-1">Belum absen</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($sesiAktif && !isset($hariIni[$sesiAktif]))
                <form method="POST" action="{{ route('presensi.store') }}" enctype="multipart/form-data" class="space-y-4 border-t border-slate-100 pt-4">
                    @csrf
                    <div class="flex items-center justify-between bg-blue-50 px-3.5 py-2.5 rounded-xl">
                        <span class="text-xs font-bold text-primary-900">Sesi Aktif: APEL {{ strtoupper($sesiAktif) }}</span>
                        <span class="text-[11px] font-mono text-primary-700">{{ now()->format('H:i') }} WITA</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kehadiran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:text-emerald-900">
                                <input type="radio" name="status_kehadiran" value="hadir" checked class="sr-only" onchange="togglePresensiMode(this.value)">
                                Hadir (Foto)
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-amber-50 has-[:checked]:border-amber-500 has-[:checked]:text-amber-900">
                                <input type="radio" name="status_kehadiran" value="izin" class="sr-only" onchange="togglePresensiMode(this.value)">
                                Izin
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:text-blue-900">
                                <input type="radio" name="status_kehadiran" value="dinas_luar" class="sr-only" onchange="togglePresensiMode(this.value)">
                                Dinas Luar
                            </label>
                        </div>
                    </div>

                    <div id="fotoSection">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Ambil Foto Barisan Apel (Kamera Langsung)</label>
                        <input type="file" name="foto" id="fotoInput" accept="image/*" capture="environment"
                               onchange="previewFoto(this)"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-700 file:text-white file:font-bold">
                        <img id="fotoPreview" class="hidden mt-3 w-full max-h-56 object-cover rounded-xl border border-slate-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan <span id="ketRequired" class="hidden text-rose-600">*</span></label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional untuk Hadir; wajib diisi untuk Izin / Dinas Luar (mis. Surat Tugas)"
                                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-sm"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-primary-700 hover:bg-primary-800 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-card transition">
                        Kirim Presensi Apel {{ ucfirst($sesiAktif) }}
                    </button>
                </form>
            @elseif($sesiAktif && isset($hariIni[$sesiAktif]))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-center text-sm font-semibold text-emerald-900">
                    Anda sudah mengisi presensi Apel {{ ucfirst($sesiAktif) }} hari ini.
                </div>
            @else
                <div class="rounded-xl bg-slate-100 p-4 text-center text-xs text-slate-600">
                    Di luar jam sesi apel ({{ $pengaturan->pagi_mulai }}–{{ $pengaturan->pagi_selesai }} / {{ $pengaturan->sore_mulai }}–{{ $pengaturan->sore_selesai }} WITA).
                </div>
            @endif
        </div>
    </div>

    {{-- Kolom Kanan: Rekap Pekan & Riwayat --}}
    <div class="lg:col-span-6 space-y-6">
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-1">Rekap Kehadiran Pekan Ini</h2>
            <p class="text-xs text-slate-500 mb-4">{{ $rekapPekan['periode'] }}</p>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-3 rounded-xl bg-blue-50">
                    <div class="text-2xl font-black text-primary-800">{{ $rekapPekan['pagi'] }}</div>
                    <div class="text-[11px] font-bold text-slate-600">Apel Pagi</div>
                </div>
                <div class="p-3 rounded-xl bg-amber-50">
                    <div class="text-2xl font-black text-amber-800">{{ $rekapPekan['sore'] }}</div>
                    <div class="text-[11px] font-bold text-slate-600">Apel Sore</div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50">
                    <div class="text-2xl font-black text-emerald-800">{{ $rekapPekan['total'] }}</div>
                    <div class="text-[11px] font-bold text-slate-600">Total Hadir</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 font-display font-bold text-sm text-primary-950">Riwayat Presensi Saya</div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase">
                        <tr>
                            <th class="py-2.5 px-4">Tanggal / Sesi</th>
                            <th class="py-2.5 px-4">Kehadiran</th>
                            <th class="py-2.5 px-4 text-right">Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayat as $r)
                        <tr>
                            <td class="py-2.5 px-4">
                                <span class="font-semibold">{{ optional($r->tanggal)->format('d/m/Y') }}</span>
                                <span class="uppercase text-[10px] font-bold text-primary-700 ml-1">{{ $r->sesi }}</span>
                            </td>
                            <td class="py-2.5 px-4 font-medium">{{ $r->labelKehadiran() }}</td>
                            <td class="py-2.5 px-4 text-right font-bold">{{ ucfirst($r->status_validasi) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-6 text-center text-slate-400">Belum ada riwayat presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100">{{ $riwayat->links() }}</div>
        </div>
    </div>
</div>

<script>
function togglePresensiMode(val) {
    var req = document.getElementById('ketRequired');
    if (req) req.classList.toggle('hidden', val === 'hadir');
}
function previewFoto(input) {
    var img = document.getElementById('fotoPreview');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
