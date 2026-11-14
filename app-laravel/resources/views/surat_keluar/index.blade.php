@extends('layouts.app')
@section('title', 'Buku Agenda Surat Keluar · Kecamatan Tamalate')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Permendagri No. 83 Tahun 2022 · Penomoran Naskah Dinas
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Buku Agenda Surat Keluar
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Format penomoran otomatis: <span class="font-mono font-semibold text-primary-700">[Kode Klasifikasi]/[No. Urut]/KT/[Bulan Romawi]/[Tahun]</span>
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('surat.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">
            Surat Masuk
        </a>
        @if(auth()->user()->hasRole('operator'))
            <a href="{{ route('surat-keluar.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Surat Keluar
            </a>
        @endif
    </div>
</div>

<!-- Filter -->
<form method="GET" action="{{ route('surat-keluar.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nomor surat, instansi tujuan, perihal, atau penanggung jawab..."
           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    <select name="kode_klasifikasi_id" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        <option value="">Semua Klasifikasi</option>
        @foreach($klasifikasiList as $k)
            <option value="{{ $k->id }}" @selected((string) ($klasifikasiId ?? '') === (string) $k->id)>{{ $k->kode }} — {{ $k->nama }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold transition">
        Filter
    </button>
</form>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">No. Urut</th>
                    <th class="py-3.5 px-4">Nomor Surat</th>
                    <th class="py-3.5 px-4">Klasifikasi</th>
                    <th class="py-3.5 px-4">Instansi Tujuan</th>
                    <th class="py-3.5 px-4">Perihal</th>
                    <th class="py-3.5 px-4">Tgl Surat</th>
                    <th class="py-3.5 px-6 text-right">Penanggung Jawab</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-bold text-xs text-slate-700">
                        #{{ $s->no_urut }}
                    </td>
                    <td class="py-3.5 px-4 font-mono font-bold text-xs text-primary-700">
                        <a href="{{ route('surat-keluar.show', $s) }}" class="hover:underline">
                            {{ $s->nomor_surat }}
                        </a>
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-600">
                        {{ optional($s->kodeKlasifikasi)->kode }} — {{ Str::limit(optional($s->kodeKlasifikasi)->nama, 24) }}
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-800">
                        {{ $s->instansi_tujuan }}
                    </td>
                    <td class="py-3.5 px-4 text-slate-900 max-w-xs truncate" title="{{ $s->perihal }}">
                        {{ Str::limit($s->perihal, 45) }}
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-500">
                        {{ optional($s->tgl_surat)->format('d/m/Y') }}
                    </td>
                    <td class="py-3.5 px-6 text-right text-xs font-medium text-slate-700">
                        {{ $s->penanggung_jawab }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <p class="font-semibold text-sm text-slate-600">Belum ada surat keluar yang tercatat.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $surat->links() }}
    </div>
</div>
@endsection

