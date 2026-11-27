@extends('layouts.app')
@section('title', 'Dashboard Pegawai · Kecamatan Tamalate')

@section('content')
<!-- Page Header with Greeting -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Area Mandiri Pegawai
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Selamat Datang, {{ auth()->user()?->nama ?? 'Pegawai' }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            NIP: {{ auth()->user()?->nip ?: '-' }} · Unit: {{ optional(auth()->user()?->unitKerja)->nama ?? 'Kecamatan Tamalate' }}
        </p>
    </div>

    @if(auth()->user()?->berhakCuti())
        <div class="flex items-center gap-2">
            <a href="{{ route('kepegawaian.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Pengajuan Baru
            </a>
        </div>
    @endif
</div>

<!-- Highlight Stat Cards (Turikale Style) -->
<div class="grid md:grid-cols-2 gap-5 mb-8">
    @php
        $pagi = $presensiHariIni->get('pagi');
        $sore = $presensiHariIni->get('sore');
        $sudahPresensi = $presensiHariIni->isNotEmpty();
    @endphp
    <!-- Presensi Apel Card -->
    <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all border-l-4 {{ $sudahPresensi ? 'border-l-emerald-500' : 'border-l-amber-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Presensi Apel Hari Ini</p>
                <div class="mt-2.5 flex items-center gap-2 flex-wrap">
                    @if($pagi)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Pagi: {{ ucfirst($pagi->status_validasi) }} ({{ \Carbon\Carbon::parse($pagi->jam)->format('H:i') }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Pagi: Belum
                        </span>
                    @endif

                    @if($sore)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Sore: {{ ucfirst($sore->status_validasi) }} ({{ \Carbon\Carbon::parse($sore->jam)->format('H:i') }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Sore: Belum
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-2">
                    Apel Pagi (07.00–08.30) & Apel Sore (15.30–17.00) WITA
                </p>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-700 hover:text-primary-800 hover:underline">
                Buka Kamera / Presensi Apel
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <!-- Total Pengajuan Card -->
    <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all border-l-4 border-l-primary-600">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengajuan Saya</p>
                <div class="mt-2 text-3xl font-display font-black text-primary-950">
                    {{ $totalPengajuanSaya ?? $pengajuanSaya->count() }}
                    <span class="text-xs font-semibold text-slate-400 ml-1">berkas terdaftar</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">
                    {{ auth()->user()?->isPppkParuhWaktu() ? 'Pengajuan cuti' : 'Cuti, KGB, Kenaikan Pangkat, dan Pensiun' }}
                </p>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
            @if(auth()->user()?->berhakLayanan())
                <a href="{{ route('pengajuan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-700 hover:text-primary-800 hover:underline">
                    Lihat Semua Riwayat Pengajuan
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @elseif(auth()->user()?->isPppkParuhWaktu())
                <a href="{{ route('kepegawaian.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-700 hover:text-primary-800 hover:underline">
                    Lihat Pengajuan Cuti
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="text-xs text-slate-400">Akun ini tidak memiliki hak pengajuan layanan mandiri.</span>
            @endif
        </div>
    </div>
</div>

<!-- Recent Submissions Table Card -->
<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-display font-extrabold text-base text-primary-950">{{ auth()->user()?->isPppkParuhWaktu() ? 'Pengajuan Cuti Terbaru' : 'Pengajuan Terbaru' }}</h2>
            <p class="text-xs text-slate-500">Daftar berkas permohonan kepegawaian yang baru diajukan</p>
        </div>
        @if(auth()->user()?->berhakLayanan())
            <a href="{{ route('pengajuan.index') }}" class="text-xs font-bold text-primary-700 hover:underline">
                Lihat Semua
            </a>
        @elseif(auth()->user()?->isPppkParuhWaktu())
            <a href="{{ route('kepegawaian.index') }}" class="text-xs font-bold text-primary-700 hover:underline">
                Lihat Semua
            </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3 px-6">Nomor Berkas</th>
                    <th class="py-3 px-4">Jenis Layanan</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Tanggal Pengajuan</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse(($pengajuanTerbaru ?? collect()) as $item)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-semibold text-xs text-slate-700">
                        {{ $item['nomor'] }}
                        <span class="ml-1.5 inline-flex px-2 py-0.5 rounded text-[10px] font-sans font-bold bg-slate-100 text-slate-600">{{ $item['sumber'] }}</span>
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                        {{ $item['layanan'] }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $item['status_label'] }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-500">
                        {{ $item['tanggal'] ?? '-' }}
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <a href="{{ $item['url'] }}" 
                           class="inline-flex items-center gap-1 text-xs font-bold text-primary-700 hover:text-primary-800 hover:underline">
                            Lihat Detail →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Belum ada permohonan pengajuan.</p>
                        <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Buat Pengajuan Baru" di atas untuk memulai pengajuan cuti, KGB, kenaikan pangkat, atau pensiun.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
