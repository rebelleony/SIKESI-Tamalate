@extends('layouts.app')
@section('title', 'Dashboard Persetujuan · Kecamatan Tamalate')

@section('content')
@php
    $isCamatLurah = auth()->user()->hasRole('pimpinan');
    $isKasiKasubag = auth()->user()->hasRole('verifikator_pimpinan');
@endphp
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ $tahap ?? 'Alur Persetujuan Berjenjang' }}
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            {{ $isCamatLurah ? 'Persetujuan Akhir & Tanda Tangan Digital' : 'Antrean Persetujuan Tingkat Pertama' }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pejabat Berwenang: <span class="font-bold text-slate-700">{{ auth()->user()->nama }}</span> ({{ auth()->user()->role->nama_role ?? 'Pejabat' }}) · {{ optional(auth()->user()->unitKerja)->nama ?? 'Kecamatan Tamalate' }}
        </p>
    </div>
</div>

{{-- Kartu Ringkasan Tugas & Batasan Role Pejabat --}}
@if($isCamatLurah)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-5 rounded-2xl bg-white border border-blue-100 shadow-card md:col-span-2">
            <h2 class="font-display font-bold text-sm text-primary-950 flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                Tugas & SOP Persetujuan Camat / Lurah
            </h2>
            <ul class="text-xs text-slate-600 space-y-1.5 list-disc pl-4">
                <li>Meninjau berkas yang <strong>telah lolos verifikasi</strong> dokumen dan <strong>persetujuan Verifikator Pimpinan</strong>.</li>
                <li>Memberikan <strong>Tanda Tangan Digital (HMAC Cryptographic Token)</strong> saat menyetujui pengajuan.</li>
                <li>Setelah disetujui & ditandatangani, berkas <strong>diteruskan ke Super Admin</strong> untuk pemeriksaan kelengkapan administrasi dan distribusi kepada pegawai.</li>
                <li>Jika ditolak, alasan penolakan dicatat dan <strong>diteruskan ke Super Admin</strong> untuk ditindaklanjuti (bukan dikembalikan langsung kepada staf).</li>
            </ul>
        </div>
        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lift flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider block">Total Disetujui & Ditandatangani</span>
                <span class="text-3xl font-display font-extrabold mt-1 block">{{ $totalDitandatangani ?? 0 }}</span>
            </div>
            <div class="text-[11px] text-emerald-100/90 mt-3 pt-3 border-t border-emerald-500/40">
                Otentikasi sah terikat kredensial resmi pejabat bersangkutan.
            </div>
        </div>
    </div>
@elseif($isKasiKasubag)
    <div class="p-5 rounded-2xl bg-white border border-blue-100 shadow-card mb-6">
        <h2 class="font-display font-bold text-sm text-primary-950 flex items-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
            Tugas & SOP Persetujuan Kasi / Kasubag (Tahap 1)
        </h2>
        <p class="text-xs text-slate-600">
            Memeriksa berkas permohonan yang telah diverifikasi kelengkapannya oleh Verifikator. Jika disetujui, berkas diteruskan ke Pimpinan untuk persetujuan akhir & tanda tangan digital. Jika ditolak, alasan penolakan dicatat dan diteruskan ke Super Admin.
        </p>
    </div>
@endif

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            @php $totalAntreanPimpinan = $antrean->count() + ($antreanKepegawaian ?? collect())->count() + ($antreanSuratMandiri ?? collect())->count(); @endphp
            <h2 class="font-display font-extrabold text-base text-primary-950">Menunggu Persetujuan Anda ({{ $totalAntreanPimpinan }})</h2>
            <p class="text-xs text-slate-500">Berkas permohonan yang memerlukan paraf / persetujuan Anda</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3 px-6">Nomor Berkas</th>
                    <th class="py-3 px-4">Nama Pemohon</th>
                    <th class="py-3 px-4">Layanan</th>
                    <th class="py-3 px-4">Status Terkini</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($antrean as $p)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-semibold text-xs text-slate-700">
                        {{ $p->nomor_ajuan }}
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                        {{ $p->pegawai->nama ?? '-' }}
                    </td>
                    <td class="py-3.5 px-4 text-slate-600">
                        {{ ucfirst(str_replace('_', ' ', $p->jenis_layanan)) }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $p->labelStatus() }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <a href="{{ route('pengajuan.show', $p) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition">
                            Proses Persetujuan →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Tidak ada pengajuan yang menunggu persetujuan Anda.</p>
                        <p class="text-xs text-slate-400 mt-1">Semua berkas pada tingkatan Anda telah diproses.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(($antreanKepegawaian ?? collect())->isNotEmpty())
<div class="mt-6 rounded-2xl border border-emerald-200 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-display font-extrabold text-base text-primary-950">Antrean Persetujuan Kepegawaian ({{ $antreanKepegawaian->count() }})</h2>
            <p class="text-xs text-slate-500">Cuti, KGB, Kenaikan Pangkat, dan Pensiun pada meja persetujuan Anda</p>
        </div>
        <a href="{{ route('kepegawaian.index') }}" class="text-xs font-bold text-primary-700 hover:underline">Lihat Semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-emerald-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-emerald-100">
                <tr>
                    <th class="py-3 px-6">Nomor</th>
                    <th class="py-3 px-4">Pegawai / Unit</th>
                    <th class="py-3 px-4">Layanan</th>
                    <th class="py-3 px-4">Meja Saat Ini</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($antreanKepegawaian as $k)
                <tr class="hover:bg-emerald-50/30 transition">
                    <td class="py-3 px-6 font-mono font-semibold text-xs text-primary-700">{{ $k->nomor_pengajuan }}</td>
                    <td class="py-3 px-4">
                        <div class="font-semibold text-slate-800">{{ $k->pegawai->nama ?? '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $k->unitKerja->nama ?? '-' }}</div>
                    </td>
                    <td class="py-3 px-4 font-medium text-slate-700">{{ $k->labelJenis() }}</td>
                    <td class="py-3 px-4 text-xs font-semibold text-amber-800">{{ $k->meja_saat_ini }}</td>
                    <td class="py-3 px-6 text-right">
                        <a href="{{ route('kepegawaian.show', $k) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs">
                            Proses Persetujuan →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(($antreanSuratMandiri ?? collect())->isNotEmpty())
<div class="mt-6 rounded-2xl border border-amber-200 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-display font-extrabold text-base text-primary-950">Surat Mandiri Menunggu Pimpinan ({{ $antreanSuratMandiri->count() }})</h2>
            <p class="text-xs text-slate-500">Permohonan Surat Pengantar / Undangan / Fasilitasi menunggu tanda tangan pimpinan</p>
        </div>
        <a href="{{ route('surat-mandiri.index') }}" class="text-xs font-bold text-primary-700 hover:underline">Kelola →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach($antreanSuratMandiri as $m)
                <tr class="hover:bg-amber-50/30 transition">
                    <td class="py-3 px-6 font-mono font-bold text-xs text-primary-700">{{ $m->kode_tracking }}</td>
                    <td class="py-3 px-4 font-semibold text-slate-800">{{ $m->nama_pemohon }} ({{ $m->unit_asal }})</td>
                    <td class="py-3 px-4 text-slate-700">{{ $m->jenis_surat }} — {{ $m->perihal }}</td>
                    <td class="py-3 px-6 text-right">
                        <a href="{{ route('surat-mandiri.index', ['q' => $m->kode_tracking]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs">
                            Proses →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
