@extends('layouts.app')
@section('title', 'Unit Kerja · Data Master')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('admin.master') }}" class="text-sm text-blue-600 hover:underline">← Data Master</a>
        <h1 class="text-2xl font-bold mt-1">Unit Kerja</h1>
    </div>
    <a href="{{ route('admin.unit.create') }}" class="bg-emerald-600 text-white text-sm px-4 py-2 rounded hover:bg-emerald-700">+ Tambah Unit</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-2">Nama</th>
                <th class="px-4 py-2">Tipe</th>
                <th class="px-4 py-2">Induk</th>
                <th class="px-4 py-2 text-center">Pegawai</th>
                <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($units as $u)
                <tr>
                    <td class="px-4 py-2 font-medium">{{ $u->nama }}</td>
                    <td class="px-4 py-2">{{ $tipe[$u->tipe] ?? $u->tipe }}</td>
                    <td class="px-4 py-2">{{ optional($u->induk)->nama ?? '-' }}</td>
                    <td class="px-4 py-2 text-center">{{ $u->users_count }}</td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.unit.edit', $u) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.unit.destroy', $u) }}" class="inline"
                              onsubmit="return confirm('Hapus unit {{ $u->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada unit kerja.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="text-xs text-gray-500 mt-2">Unit yang masih memiliki pegawai atau unit anak tidak dapat dihapus.</p>
@endsection
