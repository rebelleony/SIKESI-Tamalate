@extends('layouts.app')
@section('title', 'Dashboard Operator · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Staf Kepegawaian & Operator Berkas
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Antrean Verifikasi Berkas SOP
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pemeriksaan kelengkapan dokumen persyaratan sebelum dilanjutkan ke alur persetujuan struktural.
        </p>
    </div>
</div>

<!-- 3 Stat Cards Operator -->
<div class="grid sm:grid-cols-3 gap-5 mb-8">
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card border-l-4 border-l-amber-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Verifikasi Dokumen</p>
                <div class="mt-2 text-3xl font-display font-black text-amber-700">{{ $antrean->count() }}</div>
                <p class="text-xs text-slate-500 mt-1">Pengajuan menunggu diteliti</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card border-l-4 border-l-blue-600">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Validasi Presensi Apel</p>
                <div class="mt-2 text-3xl font-display font-black text-blue-700">{{ $presensiAntrean->count() }}</div>
                <p class="text-xs text-slate-500 mt-1">Bukti foto menunggu validasi</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card border-l-4 border-l-emerald-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dokumen Telah Diproses</p>
                <div class="mt-2 text-3xl font-display font-black text-emerald-700">{{ $totalDiverifikasi }}</div>
                <p class="text-xs text-slate-500 mt-1">Diteruskan ke alur pimpinan</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-display font-extrabold text-base text-primary-950">Berkas Menunggu Verifikasi ({{ $antrean->count() }})</h2>
            <p class="text-xs text-slate-500">Berkas baru yang diajukan oleh pegawai dan siap diteliti keabsahannya</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3 px-6">Nomor Berkas</th>
                    <th class="py-3 px-4">Nama Pemohon</th>
                    <th class="py-3 px-4">Jenis Layanan</th>
                    <th class="py-3 px-4">Tanggal Pengajuan</th>
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
                    <td class="py-3.5 px-4 text-xs text-slate-500">
                        {{ optional($p->tanggal_ajuan)->format('d/m/Y') }}
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <a href="{{ route('pengajuan.show', $p) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition">
                            Periksa & Verifikasi →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Tidak ada berkas yang menunggu verifikasi.</p>
                        <p class="text-xs text-slate-400 mt-1">Seluruh berkas masuk telah diverifikasi.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($presensiAntrean->isNotEmpty())
<div class="mt-8 rounded-2xl border border-blue-200 bg-white p-6 shadow-card border-l-4 border-l-blue-600">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-1">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                Presensi Apel Pegawai
            </span>
            <h3 class="font-display font-extrabold text-base text-primary-950">Terdapat {{ $presensiAntrean->count() }} Presensi Apel Menunggu Validasi</h3>
            <p class="text-xs text-slate-500">Periksa bukti foto kehadiran pegawai sebelum apel berakhir.</p>
        </div>
        <a href="{{ route('presensi.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            Validasi Presensi Sekarang →
        </a>
    </div>
</div>
@endif
@endsection
