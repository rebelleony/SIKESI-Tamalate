@extends('layouts.app')
@section('title', ($item->exists ? 'Edit' : 'Tambah') . ' Kode Klasifikasi Arsip')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.klasifikasi.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Daftar Kode Klasifikasi</a>
    <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-2">
        {{ $item->exists ? 'Edit Kode Klasifikasi' : 'Tambah Kode Klasifikasi Baru' }}
    </h1>
</div>

<form method="POST"
      action="{{ $item->exists ? route('admin.klasifikasi.update', $item) : route('admin.klasifikasi.store') }}"
      class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-4 max-w-xl">
    @csrf
    @if($item->exists) @method('PUT') @endif

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Klasifikasi <span class="text-rose-600">*</span></label>
        <input name="kode" value="{{ old('kode', $item->kode) }}" required placeholder="mis. 800.1.11.13" class="w-full font-mono border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Klasifikasi <span class="text-rose-600">*</span></label>
        <input name="nama" value="{{ old('nama', $item->nama) }}" required placeholder="mis. Kenaikan Gaji Berkala (KGB)" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Induk (opsional)</label>
        <select name="induk_kode" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            <option value="">— Kode Utama (Tanpa Induk) —</option>
            @foreach($indukList as $ind)
                <option value="{{ $ind->kode }}" @selected(old('induk_kode', $item->induk_kode) === $ind->kode)>{{ $ind->kode }} — {{ $ind->nama }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan</label>
        <textarea name="keterangan" rows="2" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('keterangan', $item->keterangan) }}</textarea>
    </div>

    <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-card transition">
        Simpan Kode Klasifikasi
    </button>
</form>
@endsection

