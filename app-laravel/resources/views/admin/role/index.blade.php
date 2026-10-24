@extends('layouts.app')
@section('title', 'Peran · Data Master')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('admin.master') }}" class="text-sm text-blue-600 hover:underline">← Data Master</a>
        <h1 class="text-2xl font-bold mt-1">Peran (Role)</h1>
    </div>
    <a href="{{ route('admin.role.create') }}" class="bg-amber-600 text-white text-sm px-4 py-2 rounded hover:bg-amber-700">+ Tambah Peran</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-2">Nama Peran</th>
                <th class="px-4 py-2">Deskripsi</th>
                <th class="px-4 py-2">Permissions</th>
                <th class="px-4 py-2 text-center">Pegawai</th>
                <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($roles as $r)
                <tr>
                    <td class="px-4 py-2 font-medium">{{ $r->nama_role }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $r->deskripsi ?? '-' }}</td>
                    <td class="px-4 py-2">
                        @forelse($r->permissions ?? [] as $perm)
                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded mr-1 mb-1">{{ $perm }}</span>
                        @empty
                            <span class="text-gray-400 text-xs">-</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-2 text-center">{{ $r->users_count }}</td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.role.edit', $r) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.role.destroy', $r) }}" class="inline"
                              onsubmit="return confirm('Hapus peran {{ $r->nama_role }}?')">
                            @csrf @method('DELETE')
                            <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada peran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="text-xs text-gray-500 mt-2">Peran yang masih dipakai pegawai tidak dapat dihapus.</p>
@endsection
