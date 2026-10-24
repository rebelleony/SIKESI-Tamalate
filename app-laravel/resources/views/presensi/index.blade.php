@extends('layouts.app')
@section('title', 'Presensi Apel · Kecamatan Tamalate')

@section('content')
<div class="mb-8">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        Presensi Geolocation & Foto Kamera
    </div>
    <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
        Presensi Apel Pegawai
    </h1>
    <p class="text-sm text-slate-500 mt-1">
        Jadwal: Apel Pagi (07.00–08.30 WITA) & Apel Sore (15.30–17.00 WITA) · Kecamatan Tamalate
    </p>
</div>

<div class="grid lg:grid-cols-12 gap-6">
    <!-- Kolom Kiri: Form Absensi Hari Ini -->
    <div class="lg:col-span-6 space-y-6">
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card border-l-4 border-l-primary-600">
            <h2 class="font-display font-bold text-base text-primary-950 mb-3">Status Apel Hari Ini</h2>

            <div class="grid grid-cols-2 gap-3 mb-5">
                @foreach(['pagi' => 'Apel Pagi (07.00–08.30)', 'sore' => 'Apel Sore (15.30–17.00)'] as $key => $label)
                    @php $rec = $hariIni[$key] ?? null; @endphp
                    <div class="p-3.5 rounded-xl border {{ $rec ? 'bg-emerald-50/70 border-emerald-200' : 'bg-slate-50 border-slate-200/80' }}">
                        <p class="text-[11px] font-bold uppercase tracking-wider {{ $rec ? 'text-emerald-800' : 'text-slate-500' }}">{{ $label }}</p>
                        @if($rec)
                            <p class="text-sm font-extrabold text-emerald-900 mt-1">
                                Hadir {{ \Carbon\Carbon::parse($rec->jam)->format('H:i') }} WITA
                            </p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200/80 text-emerald-900">
                                {{ $rec->status_validasi }}
                            </span>
                        @else
                            <p class="text-xs font-semibold text-slate-400 mt-1.5 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                Belum presensi
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Panel Input Absensi -->
            <div class="pt-4 border-t border-slate-100">
                @if(!$sesiAktif)
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <span class="font-bold block">Di Luar Waktu Apel</span>
                            Presensi apel hanya dibuka pada sesi pagi (07.00–08.30) atau sesi sore (15.30–17.00) WITA.
                        </div>
                    </div>
                @elseif(isset($hariIni[$sesiAktif]))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs">
                        <div class="flex items-center gap-2 font-bold mb-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Presensi Apel {{ ucfirst($sesiAktif) }} Selesai
                        </div>
                        <p class="mb-3">Anda telah berhasil mengirimkan presensi apel sesi ini.</p>
                        @if(!empty($hariIni[$sesiAktif]->foto_path))
                            <img src="{{ asset('storage/' . $hariIni[$sesiAktif]->foto_path) }}" alt="Foto apel" class="rounded-xl border border-emerald-300 max-h-56 shadow-sm">
                        @endif
                    </div>
                @else
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                Sesi Aktif: Apel {{ strtoupper($sesiAktif) }}
                            </span>
                            <span class="text-xs text-slate-500">WITA</span>
                        </div>

                        <video id="video" width="100%" autoplay playsinline class="rounded-xl border border-slate-300 bg-black hidden aspect-video object-cover shadow-inner"></video>
                        <canvas id="canvas" class="hidden"></canvas>
                        <img id="preview" class="hidden rounded-xl border border-slate-200 mb-2 w-full max-h-64 object-cover shadow-sm">

                        <form method="POST" action="{{ route('presensi.store') }}" enctype="multipart/form-data" id="formPresensi" class="space-y-3">
                            @csrf
                            <input type="file" name="foto" id="fotoInput" accept="image/*" class="hidden">
                            <div class="flex gap-2 flex-wrap">
                                <button type="button" id="btnMulai" class="px-4 py-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Aktifkan Kamera
                                </button>
                                <button type="button" id="btnAmbil" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition hidden">
                                    Ambil Foto
                                </button>
                                <label for="fotoInput" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs cursor-pointer transition">
                                    Unggah dari Galeri
                                </label>
                                <button type="submit" id="btnKirim" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition hidden flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Kirim Presensi
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Rekap Pekan Ini -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Rekapitulasi Pekan Ini</p>
                <p class="text-xs text-slate-500 mb-3">{{ $rekapPekan['periode'] }}</p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-lg font-black text-primary-950">{{ $rekapPekan['pagi'] }}</span>
                        <span class="text-[10px] font-semibold text-slate-500">Apel Pagi</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-lg font-black text-primary-950">{{ $rekapPekan['sore'] }}</span>
                        <span class="text-[10px] font-semibold text-slate-500">Apel Sore</span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-100">
                        <span class="block text-lg font-black text-primary-700">{{ $rekapPekan['total'] }}</span>
                        <span class="text-[10px] font-bold text-primary-800">Total Hadir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Riwayat Presensi -->
    <div class="lg:col-span-6">
        <div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-display font-extrabold text-base text-primary-950">Riwayat Presensi</h2>
                <p class="text-xs text-slate-500">Log kehadiran apel mandiri Anda</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                        <tr>
                            <th class="py-3 px-6">Tanggal</th>
                            <th class="py-3 px-4">Sesi</th>
                            <th class="py-3 px-4">Waktu WITA</th>
                            <th class="py-3 px-6 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayat as $r)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3.5 px-6 font-semibold text-slate-800 text-xs">
                                {{ $r->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                                Apel {{ ucfirst($r->sesi) }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-mono text-slate-500">
                                {{ \Carbon\Carbon::parse($r->jam)->format('H:i') }} WITA
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $r->status_validasi }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                <p class="font-semibold text-sm text-slate-600">Belum ada riwayat presensi.</p>
                                <p class="text-xs text-slate-400 mt-1">Lakukan absensi saat jam apel dibuka.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $riwayat->links() }}
            </div>
        </div>
    </div>
</div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const fotoInput = document.getElementById('fotoInput');
const btnMulai = document.getElementById('btnMulai');
const btnAmbil = document.getElementById('btnAmbil');
const btnKirim = document.getElementById('btnKirim');
let stream;

btnMulai?.addEventListener('click', async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        video.srcObject = stream;
        video.classList.remove('hidden');
        btnMulai.classList.add('hidden');
        btnAmbil.classList.remove('hidden');
    } catch (e) {
        alert('Gagal mengakses kamera. Silakan pilih opsi "Unggah dari Galeri".');
    }
});

btnAmbil?.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    canvas.toBlob(blob => {
        const file = new File([blob], 'apel.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        fotoInput.files = dt.files;
        preview.src = canvas.toDataURL('image/jpeg');
        preview.classList.remove('hidden');
        btnKirim.classList.remove('hidden');
        if (stream) stream.getTracks().forEach(t => t.stop());
        video.classList.add('hidden');
        btnAmbil.classList.add('hidden');
    }, 'image/jpeg', 0.8);
});

fotoInput?.addEventListener('change', () => {
    if (fotoInput.files.length) {
        preview.src = URL.createObjectURL(fotoInput.files[0]);
        preview.classList.remove('hidden');
        btnKirim.classList.remove('hidden');
    }
});
</script>
@endsection
