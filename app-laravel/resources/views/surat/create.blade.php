@extends('layouts.app')
@section('title', 'Catat Surat Masuk · Kecamatan Tamalate')

@section('content')
<div class="mb-6">
    <a href="{{ route('surat.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Buku Agenda Surat Masuk</a>
    <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-2">Catat Buku Agenda Surat Masuk</h1>
    <p class="text-sm text-slate-500">Nomor Agenda berikutnya (auto-increment): <span class="font-mono font-bold text-primary-700">#{{ $nextNoAgenda }} (ASM/{{ now()->year }}/{{ sprintf('%04d', $nextNoAgenda) }})</span></p>
</div>

<form method="POST" action="{{ route('surat.store') }}" enctype="multipart/form-data" class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-4 max-w-3xl">
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Instansi Pengirim <span class="text-rose-600">*</span></label>
            <input name="asal_surat" value="{{ old('asal_surat') }}" required placeholder="mis. BKPSDM Kota Makassar" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Diterima <span class="text-rose-600">*</span></label>
            <input type="date" name="tanggal_terima" value="{{ old('tanggal_terima', now()->toDateString()) }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nomor Surat <span class="text-rose-600">*</span></label>
            <input name="nomor_surat" value="{{ old('nomor_surat') }}" required placeholder="mis. 800/123/BKPSDM/2026" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-600">*</span></label>
            <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Klasifikasi Arsip (Permendagri 83/2022)</label>
            <select name="kode_klasifikasi_id" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
                <option value="">— Pilih Kode Klasifikasi —</option>
                @foreach($klasifikasiList as $k)
                    <option value="{{ $k->id }}" @selected((int) old('kode_klasifikasi_id') === $k->id)>{{ $k->kode }} — {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Sifat Surat <span class="text-rose-600">*</span></label>
            <select name="sifat" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
                @foreach($sifat as $s)
                    <option value="{{ $s }}" @selected(old('sifat', 'biasa') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Perihal <span class="text-rose-600">*</span></label>
            <input name="perihal" value="{{ old('perihal') }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ditujukan Kepada</label>
            <input name="tujuan" value="{{ old('tujuan', 'Camat Tamalate') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan</label>
        <textarea name="keterangan" rows="2" placeholder="Keterangan tambahan pada buku register agenda..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">{{ old('keterangan') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Awal Lembar Disposisi (opsional)</label>
        <textarea name="disposisi" rows="2" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">{{ old('disposisi') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">File Scan Surat (PDF/JPG/PNG, maks 5MB)</label>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary-700 hover:file:bg-blue-100">
    </div>

    <div class="pt-2">
        <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-card transition">
            Simpan ke Agenda & Generate Lembar Disposisi
        </button>
    </div>
</form>
@endsection
