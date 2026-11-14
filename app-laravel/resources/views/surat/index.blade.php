@extends('layouts.app')
@section('title', 'Agenda Surat Masuk · Kecamatan Tamalate')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            SOP Pengendalian Surat Masuk · Kecamatan Tamalate
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Buku Agenda Surat Masuk
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Alur SOP: Pencatatan & Lembar Disposisi → Paraf Digital Sekcam → Disposisi Camat → Distribusi → Arsip.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('surat-keluar.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-blue-200 bg-white hover:bg-blue-50 text-primary-700 font-semibold text-sm transition">
            Surat Keluar
        </a>
        <a href="{{ route('surat-mandiri.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-indigo-200 bg-white hover:bg-indigo-50 text-indigo-700 font-semibold text-sm transition">
            Layanan Mandiri
        </a>
        @if(auth()->user()->hasRole('operator'))
            <a href="{{ route('surat.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Surat Masuk
            </a>
        @endif
    </div>
</div>

<!-- Filter & Search -->
<form method="GET" action="{{ route('surat.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari no. agenda, nomor surat, instansi pengirim, atau perihal..."
           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    <select name="status" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        <option value="">Semua Status</option>
        @foreach(\App\Models\SuratMasuk::STATUS as $key => $label)
            <option value="{{ $key }}" @selected(($status ?? '') === $key)>{{ $label }}</option>
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
                    <th class="py-3.5 px-6">No. Agenda</th>
                    <th class="py-3.5 px-4">Nomor Surat</th>
                    <th class="py-3.5 px-4">Instansi Pengirim</th>
                    <th class="py-3.5 px-4">Perihal</th>
                    <th class="py-3.5 px-4">Tgl Diterima</th>
                    <th class="py-3.5 px-4">Sifat</th>
                    <th class="py-3.5 px-6 text-right">Status Alur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-bold text-xs text-primary-700">
                        <a href="{{ route('surat.show', $s) }}" class="hover:underline inline-flex items-center gap-1">
                            <span>{{ $s->nomor_agenda }}</span>
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @if($s->no_agenda)
                            <div class="text-[11px] text-slate-400 font-normal">Urut #{{ $s->no_agenda }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 font-mono text-xs text-slate-800">
                        {{ $s->nomor_surat }}
                        @if($s->kodeKlasifikasi)
                            <div class="text-[11px] text-slate-400">{{ $s->kodeKlasifikasi->kode }}</div>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                        {{ $s->asal_surat }}
                    </td>
                    <td class="py-3.5 px-4 font-medium text-slate-900 max-w-xs truncate" title="{{ $s->perihal }}">
                        {{ Str::limit($s->perihal, 45) }}
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-500">
                        {{ optional($s->tanggal_terima)->format('d/m/Y') }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ in_array($s->sifat, ['penting','segera']) ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($s->sifat) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        @php
                            $badgeClass = match($s->status) {
                                'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'diarsipkan', 'didistribusikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'didisposisi' => 'bg-blue-50 text-blue-700 border-blue-200',
                                default => 'bg-amber-50 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                            {{ $s->labelStatus() }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <p class="font-semibold text-sm text-slate-600">Belum ada surat masuk yang tercatat.</p>
                        <p class="text-xs text-slate-400 mt-1">Operator/Staf Arsip dapat mencatat surat masuk baru.</p>
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
