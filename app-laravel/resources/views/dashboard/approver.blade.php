@extends('layouts.app')
@section('title', 'Dashboard Persetujuan · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            Alur Persetujuan Berjenjang (SOP)
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Antrean Persetujuan & Disposisi
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Sebagai: <span class="font-bold text-slate-700">{{ auth()->user()->role->nama_role ?? 'Pejabat Penilai' }}</span> · Kecamatan Tamalate
        </p>
    </div>
</div>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-display font-extrabold text-base text-primary-950">Menunggu Persetujuan Anda ({{ $antrean->count() }})</h2>
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
@endsection
