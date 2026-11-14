@extends('layouts.app')
@section('title', 'Laporan & Rekapitulasi · Kecamatan Tamalate')

@section('content')
<div class="mb-8">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200 mb-2">
        <span class="w-2 h-2 rounded-full bg-teal-600"></span>
        Rekapitulasi Layanan, Presensi & Persuratan (UC-10)
    </div>
    <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
        Laporan & Rekapitulasi Terpadu
    </h1>
    <p class="text-sm text-slate-500 mt-1">
        Saring data kepegawaian, cetak format PDF resmi, atau ekspor laporan ke format spreadsheet (CSV).
    </p>
</div>

<!-- Filter Box -->
<form method="GET" action="{{ route('laporan.index') }}" 
      class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card mb-6 grid md:grid-cols-5 gap-4 items-end">
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jenis Laporan</label>
        <select name="jenis" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
            @foreach($jenisList as $key => $label)
                <option value="{{ $key }}" @selected($jenis === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
        <input type="date" name="dari" value="{{ $dari }}" 
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
        <input type="date" name="sampai" value="{{ $sampai }}" 
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Unit Kerja</label>
        <select name="unit_kerja_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
            <option value="">Semua Unit Kerja</option>
            @foreach($units as $u)
                <option value="{{ $u->id }}" @selected($unitId === $u->id)>{{ $u->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">NIP (Opsional)</label>
        <input name="nip" value="{{ $nip }}" placeholder="Cari per NIP" 
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
    </div>
    <div class="md:col-span-5 flex flex-wrap gap-2.5 pt-2 border-t border-slate-100">
        <button type="submit" 
                class="px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Tampilkan Laporan
        </button>
        <a href="{{ route('laporan.cetak', request()->query()) }}" target="_blank"
           class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak PDF Resmi
        </a>
        <a href="{{ route('laporan.ekspor', request()->query()) }}" 
           class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Ekspor Data (CSV)
        </a>
    </div>
</form>

<!-- Summary Stats -->
<div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-extrabold text-base text-primary-950">
            {{ $jenisList[$jenis] }}
        </h2>
        <span class="text-xs font-semibold text-slate-500">
            Periode: {{ \Illuminate\Support\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Illuminate\Support\Carbon::parse($sampai)->format('d/m/Y') }}
        </span>
    </div>
    <div class="flex flex-wrap gap-3">
        @forelse($summary as $label => $nilai)
            <div class="px-4 py-2.5 rounded-xl bg-blue-50/70 border border-blue-100">
                <span class="text-xs text-slate-500 font-medium">{{ $label }}:</span>
                <span class="font-black text-sm text-primary-900 ml-1">{{ $nilai }}</span>
            </div>
        @empty
            <div class="text-xs text-slate-400">Tidak ada rekaman data rekapitulasi pada parameter ini.</div>
        @endforelse
    </div>
</div>

<!-- Data Table -->
<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    @foreach($columns as $c)
                        <th class="py-3.5 px-4">{{ $c }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $r)
                <tr class="hover:bg-blue-50/30 transition text-xs">
                    @foreach($r as $cell)
                        <td class="py-3.5 px-4 text-slate-700">{{ $cell }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="py-12 text-center text-slate-400">
                        <p class="font-semibold text-sm text-slate-600">Tidak ada data untuk kriteria filter yang dipilih.</p>
                        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan rentang tanggal atau pilihan unit kerja.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(is_object($rows) && method_exists($rows, 'links'))
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $rows->links() }}
        </div>
    @endif
</div>
@endsection
