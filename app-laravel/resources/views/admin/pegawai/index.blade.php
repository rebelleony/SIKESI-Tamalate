@extends('layouts.app')
@section('title', 'Data Pegawai · Data Master')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('admin.master') }}" class="text-sm text-blue-600 hover:underline">← Data Master</a>
        <h1 class="text-2xl font-bold mt-1">Pegawai / Akun</h1>
    </div>
    <a href="{{ route('admin.pegawai.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">+ Tambah Pegawai</a>
</div>

<form method="GET" class="bg-white p-4 rounded shadow mb-4 grid md:grid-cols-5 gap-3 text-sm">
    <input name="q" value="{{ $filter['q'] }}" placeholder="Cari nama / NIP / username / jabatan" class="border rounded px-3 py-2 md:col-span-2">
    <select name="role" class="border rounded px-3 py-2">
        <option value="">Semua peran</option>
        @foreach($roles as $r)
            <option value="{{ $r->nama_role }}" @selected($filter['role'] === $r->nama_role)>{{ $r->nama_role }}</option>
        @endforeach
    </select>
    <select name="unit" class="border rounded px-3 py-2">
        <option value="">Semua unit</option>
        @foreach($units as $u)
            <option value="{{ $u->id }}" @selected((string) $filter['unit'] === (string) $u->id)>{{ $u->nama }}</option>
        @endforeach
    </select>
    <select name="status" class="border rounded px-3 py-2">
        <option value="">Semua status</option>
        <option value="aktif" @selected($filter['status'] === 'aktif')>Aktif</option>
        <option value="nonaktif" @selected($filter['status'] === 'nonaktif')>Nonaktif</option>
    </select>
    <div class="md:col-span-5 flex gap-2">
        <button class="bg-slate-700 text-white px-4 py-2 rounded hover:bg-slate-800">Terapkan Filter</button>
        <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2 rounded border hover:bg-gray-50">Reset</a>
    </div>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-2">Nama</th>
                <th class="px-4 py-2">NIP</th>
                <th class="px-4 py-2">Username</th>
                <th class="px-4 py-2">Peran</th>
                <th class="px-4 py-2">Unit Kerja</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($pegawai as $p)
                <tr class="{{ $p->status === 'nonaktif' ? 'bg-gray-100 text-gray-500' : '' }}">
                    <td class="px-4 py-2">
                        <div class="font-medium">{{ $p->nama }}</div>
                        <div class="text-xs text-gray-500">{{ $p->jabatan }}</div>
                    </td>
                    <td class="px-4 py-2">{{ $p->nip }}</td>
                    <td class="px-4 py-2">{{ $p->username }}</td>
                    <td class="px-4 py-2">{{ $p->role->nama_role ?? '-' }}</td>
                    <td class="px-4 py-2">{{ optional($p->unitKerja)->nama ?? '-' }}</td>
                    <td class="px-4 py-2">
                        @if($p->status === 'aktif')
                            <span class="text-green-700 bg-green-100 px-2 py-0.5 rounded text-xs">Aktif</span>
                        @else
                            <span class="text-gray-600 bg-gray-200 px-2 py-0.5 rounded text-xs">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.pegawai.edit', $p) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.pegawai.status', $p) }}" class="inline">
                            @csrf @method('PATCH')
                            <button class="ml-2 {{ $p->status === 'aktif' ? 'text-red-600' : 'text-green-600' }} hover:underline"
                                    onclick="return confirm('{{ $p->status === 'aktif' ? 'Nonaktifkan akun ini?' : 'Aktifkan kembali akun ini?' }}')">
                                {{ $p->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.pegawai.password', $p) }}" class="inline"
                              onsubmit="var p=prompt('Kata sandi baru untuk {{ $p->nama }} (min. 8 karakter):'); if(!p){return false;} this.password_baru.value=p; return p.length>=8;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="password_baru">
                            <button class="ml-2 text-gray-600 hover:underline">Reset Sandi</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Tidak ada pegawai yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $pegawai->links() }}</div>
@endsection
