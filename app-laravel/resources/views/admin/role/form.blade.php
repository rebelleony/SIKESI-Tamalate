@extends('layouts.app')
@section('title', ($role->exists ? 'Edit' : 'Tambah') . ' Peran · Data Master')
@section('content')
@php
    $isEdit = $role->exists;
    $permText = old('permissions_text', $isEdit ? implode("\n", $role->permissions ?? []) : '');
@endphp
<a href="{{ route('admin.role.index') }}" class="text-sm text-blue-600 hover:underline">← Daftar Peran</a>
<h1 class="text-2xl font-bold mt-1 mb-4">{{ $isEdit ? 'Edit Peran' : 'Tambah Peran' }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.role.update', $role) : route('admin.role.store') }}" class="bg-white p-5 rounded shadow max-w-lg space-y-4 text-sm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-gray-600 mb-1">Nama Peran <span class="text-red-500">*</span></label>
        <input name="nama_role" value="{{ old('nama_role', $role->nama_role) }}" required maxlength="50"
               placeholder="mis. verifikator" class="w-full border rounded px-3 py-2">
        <p class="text-xs text-gray-400 mt-1">Gunakan nama unik; nama ini yang dipakai middleware <code>role:</code>.</p>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Deskripsi</label>
        <input name="deskripsi" value="{{ old('deskripsi', $role->deskripsi) }}" maxlength="255" class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Permissions <span class="text-gray-400">(satu per baris)</span></label>
        <textarea name="permissions_text" rows="5" class="w-full border rounded px-3 py-2 font-mono text-xs"
                  placeholder="pengajuan.verifikasi&#10;approval.tier1&#10;laporan.lihat">{{ $permText }}</textarea>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-amber-600 text-white px-5 py-2 rounded hover:bg-amber-700">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Peran' }}</button>
        <a href="{{ route('admin.role.index') }}" class="px-5 py-2 rounded border hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
