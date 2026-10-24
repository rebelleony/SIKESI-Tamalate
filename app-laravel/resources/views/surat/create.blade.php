@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-4">Catat Surat Masuk</h1>

<form method="POST" action="{{ route('surat.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nomor Surat</label>
            <input name="nomor_surat" value="{{ old('nomor_surat') }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Klasifikasi</label>
            <input name="klasifikasi" value="{{ old('klasifikasi') }}" placeholder="mis. Kepegawaian" class="w-full border rounded px-3 py-2">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Surat</label>
            <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Diterima</label>
            <input type="date" name="tanggal_terima" value="{{ old('tanggal_terima', now()->toDateString()) }}" required class="w-full border rounded px-3 py-2">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Asal Surat</label>
            <input name="asal_surat" value="{{ old('asal_surat') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tujuan</label>
            <input name="tujuan" value="{{ old('tujuan', 'Camat Tamalate') }}" class="w-full border rounded px-3 py-2">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Perihal</label>
        <input name="perihal" value="{{ old('perihal') }}" required class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Sifat</label>
        <select name="sifat" required class="w-full border rounded px-3 py-2">
            @foreach($sifat as $s)
                <option value="{{ $s }}" @selected(old('sifat', 'biasa') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Catatan Lembar Disposisi (opsional)</label>
        <textarea name="disposisi" rows="2" class="w-full border rounded px-3 py-2">{{ old('disposisi') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Berkas Surat (PDF/JPG/PNG, opsional)</label>
        <input type="file" name="file" class="w-full text-sm">
    </div>

    <button class="bg-slate-800 text-white px-5 py-2 rounded hover:bg-slate-700">Catat ke Agenda</button>
</form>
@endsection
