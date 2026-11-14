@extends('layouts.app')
@section('title', 'Layanan Kepegawaian · Kecamatan Tamalate')

@section('content')
@php $u = auth()->user(); @endphp
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200 mb-2">
            SOP Layanan Kepegawaian · Kecamatan & 11 Kelurahan
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Layanan Kepegawaian (Cuti, KGB, Pangkat & Pensiun)
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pantau posisi berkas secara real-time dari meja Operator Kelurahan/Kecamatan hingga Camat & BKPSDMD.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @if($u->hasRole('operator', 'verifikator_pimpinan', 'pimpinan', 'super_admin'))
            <a href="{{ route('kepegawaian.ekspor-kgb') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-sm transition">
                Ekspor Rekap KGB (.xlsx)
            </a>
            <a href="{{ route('kepegawaian.nominatif') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-blue-200 bg-white hover:bg-blue-50 text-primary-700 font-bold text-sm transition">
                Daftar Nominatif BKPSDMD
            </a>
        @endif

        @if($u->hasRole('pegawai') && $u->berhakLayanan())
            <a href="{{ route('kepegawaian.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
                + Ajukan Layanan Baru
            </a>
        @endif
    </div>
</div>

<form method="GET" action="{{ route('kepegawaian.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nomor pengajuan, nama pegawai, atau NIP..."
           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    <select name="jenis" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
        <option value="">Semua Jenis Layanan</option>
        @foreach($jenisList as $k => $lbl)
            <option value="{{ $k }}" @selected(($jenis ?? '') === $k)>{{ $lbl }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
        <option value="">Semua Status</option>
        @foreach($statusList as $k => $lbl)
            <option value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $lbl }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold">Filter</button>
</form>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">Nomor Pengajuan</th>
                    <th class="py-3.5 px-4">Pegawai / Unit Asal</th>
                    <th class="py-3.5 px-4">Layanan & Kode</th>
                    <th class="py-3.5 px-4">Jalur SOP</th>
                    <th class="py-3.5 px-4">Posisi Meja Saat Ini</th>
                    <th class="py-3.5 px-6 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengajuan as $item)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-bold text-xs text-primary-700">
                        <a href="{{ route('kepegawaian.show', $item) }}" class="hover:underline">
                            {{ $item->nomor_pengajuan }}
                        </a>
                        <div class="text-[11px] text-slate-400 font-normal">{{ $item->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-semibold text-slate-900">{{ $item->pegawai->nama ?? '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $item->unitKerja->nama ?? '-' }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-semibold text-slate-800">{{ $item->labelJenis() }}</div>
                        <div class="font-mono text-[11px] text-slate-400">{{ $item->kode_klasifikasi }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $item->asal_wilayah === 'kelurahan' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                            {{ ucfirst($item->asal_wilayah) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-semibold text-primary-800">
                        {{ $item->meja_saat_ini }}
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        @php
                            $badge = match($item->status) {
                                'selesai', 'diteruskan_bkpsdm' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'perlu_revisi' => 'bg-amber-50 text-amber-800 border-amber-200',
                                default => 'bg-blue-50 text-blue-700 border-blue-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge }}">
                            {{ $item->labelStatus() }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400">
                        Belum ada pengajuan layanan kepegawaian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100">{{ $pengajuan->links() }}</div>
</div>
@endsection

