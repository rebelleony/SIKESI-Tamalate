@extends('layouts.app')
@section('title', 'Arsip Dokumen · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
            Tata Naskah Dinas & Retensi Dokumen
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Daftar Arsip Surat & Dokumen
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pengelolaan kode tata naskah, status keaktifan dokumen, lokasi penyimpanan fisik, dan nomor Daftar Arsip.
        </p>
    </div>

    @if(auth()->user()->hasRole('operator','super_admin'))
        <a href="{{ route('arsip.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Tambah Arsip Baru
        </a>
    @endif
</div>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">Kode Arsip</th>
                    <th class="py-3.5 px-4">Judul Dokumen</th>
                    <th class="py-3.5 px-4">Jenis</th>
                    <th class="py-3.5 px-4">Status Retensi</th>
                    <th class="py-3.5 px-4">Lokasi Fisik</th>
                    <th class="py-3.5 px-4">Daftar Arsip</th>
                    <th class="py-3.5 px-6 text-right">Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($arsip as $a)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-bold text-xs text-primary-700">
                        {{ $a->kode_arsip }}
                    </td>
                    <td class="py-3.5 px-4 font-semibold text-slate-900 max-w-xs truncate" title="{{ $a->judul }}">
                        {{ Str::limit($a->judul, 45) }}
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-600">
                        {{ ucfirst(str_replace('_', ' ', $a->jenis_dokumen)) }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $a->status_dokumen === 'penting' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $a->status_dokumen)) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                        {{ $a->labelLokasi() }}
                    </td>
                    <td class="py-3.5 px-4 font-mono text-xs text-slate-500">
                        {{ $a->nomor_daftar_arsip ?? '-' }}
                    </td>
                    <td class="py-3.5 px-6 text-right text-xs text-slate-600">
                        {{ optional($a->pencatat)->nama ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Belum ada dokumen yang diarsipkan.</p>
                        <p class="text-xs text-slate-400 mt-1">Staf arsip dapat menambahkan catatan arsip dokumen baru.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $arsip->links() }}
    </div>
</div>
@endsection
