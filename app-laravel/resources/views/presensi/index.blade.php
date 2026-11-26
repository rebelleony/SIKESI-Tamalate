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
                <form id="presensiForm" method="POST" action="{{ route('presensi.store') }}" enctype="multipart/form-data" class="space-y-4 border-t border-slate-100 pt-4">
                    @csrf
                    <div class="flex items-center justify-between bg-blue-50 px-3.5 py-2.5 rounded-xl">
                        <span class="text-xs font-bold text-primary-900">Sesi Aktif: APEL {{ strtoupper($sesiAktif) }}</span>
                        <span class="text-[11px] font-mono text-primary-700">{{ now()->format('H:i') }} WITA</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kehadiran</label>
                        @php $oldKehadiran = old('status_kehadiran', 'hadir'); @endphp
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:text-emerald-900">
                                <input type="radio" name="status_kehadiran" value="hadir" @checked($oldKehadiran === 'hadir') class="sr-only" onchange="togglePresensiMode(this.value)">
                                Hadir (Foto)
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-amber-50 has-[:checked]:border-amber-500 has-[:checked]:text-amber-900">
                                <input type="radio" name="status_kehadiran" value="izin" @checked($oldKehadiran === 'izin') class="sr-only" onchange="togglePresensiMode(this.value)">
                                Izin
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center text-xs font-bold has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:text-blue-900">
                                <input type="radio" name="status_kehadiran" value="dinas_luar" @checked($oldKehadiran === 'dinas_luar') class="sr-only" onchange="togglePresensiMode(this.value)">
                                Dinas Luar
                            </label>
                        </div>
                    </div>

                    <div id="fotoSection" class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <label class="block text-xs font-bold text-slate-700">Foto Barisan Apel (Langsung dari Browser)</label>
                            <button type="button" id="toggleFallbackBtn" onclick="toggleFallbackUpload()"
                                    class="text-[11px] font-semibold text-primary-700 hover:underline">
                                Pilih file / galeri
                            </button>
                        </div>

                        <input type="hidden" name="foto_base64" id="fotoBase64">
                        <canvas id="cameraCanvas" class="hidden"></canvas>

                        {{-- State 1: Tombol Mulai Kamera --}}
                        <div id="cameraStartWrap" class="rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/50 p-5 text-center space-y-3">
                            <div class="mx-auto w-11 h-11 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Ambil foto kehadiran langsung menggunakan kamera browser</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Klik tombol di bawah untuk mengaktifkan webcam atau kamera HP Anda.</p>
                            </div>
                            <button type="button" id="startCameraBtn" onclick="startCamera()"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Buka Kamera Browser
                            </button>
                        </div>

                        {{-- State 2: Live Viewfinder Kamera --}}
                        <div id="cameraActiveWrap" class="hidden space-y-2.5">
                            <div class="relative rounded-xl overflow-hidden bg-slate-900 border border-slate-800">
                                <video id="cameraVideo" autoplay playsinline muted class="w-full h-64 sm:h-72 object-cover"></video>
                                <div class="absolute top-2.5 left-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-950/75 text-white text-[10px] font-bold backdrop-blur-sm">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                    KAMERA AKTIF
                                </div>
                                <button type="button" id="switchCameraBtn" onclick="switchCamera()"
                                        class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-full bg-slate-950/75 hover:bg-slate-900 text-white text-[10px] font-bold backdrop-blur-sm transition">
                                    ⇄ Ganti Kamera
                                </button>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="capturePhoto()"
                                        class="col-span-2 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                        <circle cx="12" cy="12" r="4" fill="currentColor"/>
                                    </svg>
                                    Ambil Foto
                                </button>
                                <button type="button" onclick="stopCamera(true)"
                                        class="py-2.5 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        {{-- State 3: Preview Hasil Foto --}}
                        <div id="previewWrap" class="hidden space-y-2.5">
                            <div class="relative rounded-xl overflow-hidden border border-emerald-200 bg-slate-100">
                                <img id="fotoPreview" alt="Preview Foto Apel" class="w-full max-h-72 object-cover">
                                <span id="fotoStatusBadge" class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full bg-emerald-600/90 text-white text-[10px] font-bold shadow">
                                    ✓ Foto Siap Dikirim
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" onclick="retakePhoto()"
                                        class="py-2 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 font-bold text-xs transition">
                                    ↻ Foto Ulang Kamera
                                </button>
                                <button type="button" onclick="clearPhoto()"
                                        class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                    ✕ Hapus Foto
                                </button>
                            </div>
                        </div>

                        {{-- Pesan Status / Error Kamera --}}
                        <div id="cameraNotice" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-900"></div>
                        <div id="fotoClientError" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-semibold text-rose-800"></div>

                        {{-- Fallback Input File / Kamera Bawaan Perangkat --}}
                        <div id="fotoFallbackWrap" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-1.5">
                            <label for="fotoInput" class="block text-[11px] font-bold text-slate-600">Unggah File Foto / Kamera Perangkat</label>
                            <input type="file" name="foto" id="fotoInput" accept="image/*" capture="environment"
                                   onchange="previewFoto(this)"
                                   class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:bg-primary-700 file:text-white file:font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan <span id="ketRequired" class="hidden text-rose-600">*</span></label>
                        <textarea id="keteranganInput" name="keterangan" rows="2" placeholder="Opsional untuk Hadir; wajib diisi untuk Izin / Dinas Luar (mis. Surat Tugas)"
                                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-sm">{{ old('keterangan') }}</textarea>
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
let activeStream = null;
let currentFacingMode = 'environment';

function getSelectedKehadiran() {
    var checked = document.querySelector('input[name="status_kehadiran"]:checked');
    return checked ? checked.value : 'hadir';
}

function togglePresensiMode(val) {
    var req = document.getElementById('ketRequired');
    var ketInput = document.getElementById('keteranganInput');
    var fotoSection = document.getElementById('fotoSection');

    if (req) req.classList.toggle('hidden', val === 'hadir');
    if (ketInput) ketInput.required = (val !== 'hadir');
    if (fotoSection) fotoSection.classList.toggle('hidden', val !== 'hadir');

    if (val !== 'hadir') {
        stopCamera(false);
        hideClientError();
    }
}

function showCameraNotice(message) {
    var notice = document.getElementById('cameraNotice');
    if (!notice) return;
    notice.textContent = message;
    notice.classList.remove('hidden');
}

function hideCameraNotice() {
    var notice = document.getElementById('cameraNotice');
    if (notice) notice.classList.add('hidden');
}

function showClientError(message) {
    var err = document.getElementById('fotoClientError');
    if (!err) return;
    err.textContent = message;
    err.classList.remove('hidden');
}

function hideClientError() {
    var err = document.getElementById('fotoClientError');
    if (err) err.classList.add('hidden');
}

function toggleFallbackUpload(forceShow) {
    var wrap = document.getElementById('fotoFallbackWrap');
    if (!wrap) return;
    if (typeof forceShow === 'boolean') {
        wrap.classList.toggle('hidden', !forceShow);
    } else {
        wrap.classList.toggle('hidden');
    }
}

async function startCamera() {
    hideCameraNotice();
    hideClientError();

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showCameraNotice('Browser atau koneksi saat ini belum mendukung akses kamera langsung (pastikan menggunakan HTTPS atau localhost). Silakan gunakan unggah foto di bawah.');
        toggleFallbackUpload(true);
        return;
    }

    stopCamera(false);

    var video = document.getElementById('cameraVideo');
    var startWrap = document.getElementById('cameraStartWrap');
    var activeWrap = document.getElementById('cameraActiveWrap');
    var previewWrap = document.getElementById('previewWrap');

    try {
        try {
            activeStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: currentFacingMode }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
        } catch (firstErr) {
            activeStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        }

        video.srcObject = activeStream;
        await video.play();

        if (startWrap) startWrap.classList.add('hidden');
        if (previewWrap) previewWrap.classList.add('hidden');
        if (activeWrap) activeWrap.classList.remove('hidden');
    } catch (err) {
        var msg = 'Tidak dapat mengakses kamera browser. Pastikan izin kamera diizinkan, atau gunakan unggah foto di bawah.';
        if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
            msg = 'Izin kamera ditolak oleh browser. Aktifkan izin kamera pada ikon gembok di address bar, atau gunakan unggah foto di bawah.';
        } else if (err && err.name === 'NotFoundError') {
            msg = 'Kamera tidak ditemukan pada perangkat ini. Silakan unggah foto dari file.';
        }
        showCameraNotice(msg);
        toggleFallbackUpload(true);
    }
}

function stopCamera(showStartBox) {
    if (activeStream) {
        activeStream.getTracks().forEach(function (track) { track.stop(); });
        activeStream = null;
    }
    var video = document.getElementById('cameraVideo');
    if (video) video.srcObject = null;

    var activeWrap = document.getElementById('cameraActiveWrap');
    var startWrap = document.getElementById('cameraStartWrap');
    var previewWrap = document.getElementById('previewWrap');

    if (activeWrap) activeWrap.classList.add('hidden');
    if (showStartBox && startWrap && previewWrap && previewWrap.classList.contains('hidden')) {
        startWrap.classList.remove('hidden');
    }
}

async function switchCamera() {
    currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
    await startCamera();
}

function capturePhoto() {
    var video = document.getElementById('cameraVideo');
    var canvas = document.getElementById('cameraCanvas');
    var preview = document.getElementById('fotoPreview');
    var previewWrap = document.getElementById('previewWrap');
    var startWrap = document.getElementById('cameraStartWrap');
    var fotoInput = document.getElementById('fotoInput');
    var fotoBase64 = document.getElementById('fotoBase64');

    if (!video || !canvas || !video.videoWidth || !video.videoHeight) {
        showClientError('Kamera belum siap. Tunggu sebentar lalu coba ambil foto lagi.');
        return;
    }

    var maxWidth = 1280;
    var scale = video.videoWidth > maxWidth ? (maxWidth / video.videoWidth) : 1;
    canvas.width = Math.round(video.videoWidth * scale);
    canvas.height = Math.round(video.videoHeight * scale);

    var ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
    if (preview) preview.src = dataUrl;
    if (fotoBase64) fotoBase64.value = dataUrl;

    if (typeof DataTransfer !== 'undefined' && fotoInput) {
        canvas.toBlob(function (blob) {
            if (!blob) return;
            try {
                var file = new File([blob], 'presensi-apel-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                fotoInput.files = dt.files;
                if (fotoBase64 && fotoInput.files && fotoInput.files.length > 0) {
                    fotoBase64.value = '';
                }
            } catch (e) {
                // Tetap gunakan fotoBase64 sebagai fallback
            }
        }, 'image/jpeg', 0.9);
    }

    stopCamera(false);
    if (startWrap) startWrap.classList.add('hidden');
    if (previewWrap) previewWrap.classList.remove('hidden');
    hideClientError();
}

function retakePhoto() {
    clearPhoto(false);
    startCamera();
}

function clearPhoto(showStart = true) {
    var fotoInput = document.getElementById('fotoInput');
    var fotoBase64 = document.getElementById('fotoBase64');
    var preview = document.getElementById('fotoPreview');
    var previewWrap = document.getElementById('previewWrap');
    var startWrap = document.getElementById('cameraStartWrap');

    if (fotoInput) fotoInput.value = '';
    if (fotoBase64) fotoBase64.value = '';
    if (preview) preview.removeAttribute('src');
    if (previewWrap) previewWrap.classList.add('hidden');
    if (showStart && startWrap) startWrap.classList.remove('hidden');
}

function previewFoto(input) {
    var preview = document.getElementById('fotoPreview');
    var previewWrap = document.getElementById('previewWrap');
    var startWrap = document.getElementById('cameraStartWrap');
    var fotoBase64 = document.getElementById('fotoBase64');

    if (input.files && input.files[0]) {
        if (fotoBase64) fotoBase64.value = '';
        stopCamera(false);
        hideClientError();
        var reader = new FileReader();
        reader.onload = function (e) {
            if (preview) preview.src = e.target.result;
            if (startWrap) startWrap.classList.add('hidden');
            if (previewWrap) previewWrap.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    togglePresensiMode(getSelectedKehadiran());

    var form = document.getElementById('presensiForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (getSelectedKehadiran() === 'hadir') {
                var fotoInput = document.getElementById('fotoInput');
                var fotoBase64 = document.getElementById('fotoBase64');
                var hasFile = fotoInput && fotoInput.files && fotoInput.files.length > 0;
                var hasBase64 = fotoBase64 && fotoBase64.value.trim().length > 0;

                if (!hasFile && !hasBase64) {
                    e.preventDefault();
                    showClientError('Silakan ambil foto langsung dari kamera browser atau unggah file foto terlebih dahulu.');
                    var section = document.getElementById('fotoSection');
                    if (section) section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
            stopCamera(false);
        });
    }
});

window.addEventListener('pagehide', function () {
    stopCamera(false);
});
</script>
@endsection
