@extends('layouts.app')
@section('title', 'Kode Klasifikasi Arsip · Kecamatan Tamalate')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('admin.master') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Data Master</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">Master Kode Klasifikasi Arsip</h1>
        <p class="text-sm text-slate-500">Standar Penomoran Naskah Dinas Pemkot Makassar (Permendagri No. 83 Tahun 2022).</p>
    </div>

    <a href="{{ route('admin.klasifikasi.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
        + Tambah Kode Klasifikasi
    </a>
</div>

<form method="GET" action="{{ route('admin.klasifikasi.index') }}" class="mb-5 flex gap-3 max-w-xl">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari kode atau nama klasifikasi..."
           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold">Cari</button>
</form>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
            <tr>
                <th class="py-3.5 px-6">Kode</th>
                <th class="py-3.5 px-4">Nama Klasifikasi</th>
                <th class="py-3.5 px-4">Induk</th>
                <th class="py-3.5 px-4">Keterangan</th>
                <th class="py-3.5 px-6 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($klasifikasi as $item)
            <tr class="hover:bg-blue-50/30 transition">
                <td class="py-3.5 px-6 font-mono font-bold text-primary-700">{{ $item->kode }}</td>
                <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $item->nama }}</td>
                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ $item->induk_kode ?? '-' }}</td>
                <td class="py-3.5 px-4 text-xs text-slate-600">{{ $item->keterangan ?? '-' }}</td>
                <td class="py-3.5 px-6 text-right space-x-2">
                    <a href="{{ route('admin.klasifikasi.edit', $item) }}" class="text-xs font-semibold text-primary-700 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.klasifikasi.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus kode klasifikasi ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada kode klasifikasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-100">{{ $klasifikasi->links() }}</div>
</div>
@endsection

