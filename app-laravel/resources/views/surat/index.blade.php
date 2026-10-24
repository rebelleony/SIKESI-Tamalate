@extends('layouts.app')
@section('title', 'Agenda Surat Masuk · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Tata Persuratan & Disposisi Camat
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Buku Agenda Surat Masuk
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Alur SOP: Pencatatan agenda → Paraf Sekcam → Disposisi Camat → Distribusi → Arsip.
        </p>
    </div>

    @if(auth()->user()->hasRole('verifikator','super_admin'))
        <a href="{{ route('surat.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Catat Surat Masuk
        </a>
    @endif
</div>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">No. Agenda</th>
                    <th class="py-3.5 px-4">Nomor Berkas Surat</th>
                    <th class="py-3.5 px-4">Perihal</th>
                    <th class="py-3.5 px-4">Asal Pengirim</th>
                    <th class="py-3.5 px-4">Tgl Terima</th>
                    <th class="py-3.5 px-4">Sifat</th>
                    <th class="py-3.5 px-6 text-right">Status Alur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-bold text-xs text-primary-700">
                        <a href="{{ route('surat.show', $s) }}" class="hover:underline flex items-center gap-1">
                            {{ $s->nomor_agenda }}
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </td>
                    <td class="py-3.5 px-4 font-mono text-xs text-slate-800">
                        {{ $s->nomor_surat }}
                    </td>
                    <td class="py-3.5 px-4 font-medium text-slate-900 max-w-xs truncate" title="{{ $s->perihal }}">
                        {{ Str::limit($s->perihal, 45) }}
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-600">
                        {{ $s->asal_surat }}
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-500">
                        {{ optional($s->tanggal_terima)->format('d/m/Y') }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $s->sifat === 'penting' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($s->sifat) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $s->labelStatus() }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Belum ada surat masuk yang tercatat.</p>
                        <p class="text-xs text-slate-400 mt-1">Staf arsip/verifikator dapat mencatat surat masuk baru.</p>
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
