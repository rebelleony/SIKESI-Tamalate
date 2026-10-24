@extends('layouts.app')
@section('title', ($unit->exists ? 'Edit' : 'Tambah') . ' Unit Kerja · Data Master')
@section('content')
@php
    $isEdit = $unit->exists;
    $v = fn ($k, $d = null) => old($k, $unit->$k ?? $d);
@endphp
<a href="{{ route('admin.unit.index') }}" class="text-sm text-blue-600 hover:underline">← Daftar Unit Kerja</a>
<h1 class="text-2xl font-bold mt-1 mb-4">{{ $isEdit ? 'Edit Unit Kerja' : 'Tambah Unit Kerja' }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.unit.update', $unit) : route('admin.unit.store') }}" class="bg-white p-5 rounded shadow max-w-lg space-y-4 text-sm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-gray-600 mb-1">Nama Unit <span class="text-red-500">*</span></label>
        <input name="nama" value="{{ $v('nama') }}" required maxlength="120" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Tipe <span class="text-red-500">*</span></label>
        <select name="tipe" required class="w-full border rounded px-3 py-2">
            @foreach($tipe as $k => $lbl)
                <option value="{{ $k }}" @selected($v('tipe') === $k)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Unit Induk</label>
        <select name="induk_id" class="w-full border rounded px-3 py-2">
            <option value="">— tidak ada (unit puncak) —</option>
            @foreach($induk as $u)
                <option value="{{ $u->id }}" @selected((string) $v('induk_id') === (string) $u->id)>{{ $u->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-emerald-600 text-white px-5 py-2 rounded hover:bg-emerald-700">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Unit' }}</button>
        <a href="{{ route('admin.unit.index') }}" class="px-5 py-2 rounded border hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
