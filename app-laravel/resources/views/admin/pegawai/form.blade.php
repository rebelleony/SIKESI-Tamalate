@extends('layouts.app')
@section('title', ($user->exists ? 'Edit' : 'Tambah') . ' Pegawai · Data Master')
@section('content')
@php
    $isEdit = $user->exists;
    $v = fn ($k, $d = null) => old($k, $user->$k ?? $d);
@endphp
<a href="{{ route('admin.pegawai.index') }}" class="text-sm text-blue-600 hover:underline">← Daftar Pegawai</a>
<h1 class="text-2xl font-bold mt-1 mb-4">{{ $isEdit ? 'Edit Pegawai' : 'Tambah Pegawai' }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.pegawai.update', $user) : route('admin.pegawai.store') }}" class="space-y-4 max-w-4xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Akun & akses --}}
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Akun & Akses</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">NIP <span class="text-red-500">*</span></label>
                <input name="nip" value="{{ $v('nip') }}" required maxlength="30" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Username <span class="text-gray-400">(kosongkan = pakai NIP)</span></label>
                <input name="username" value="{{ $v('username') }}" maxlength="60" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input name="nama" value="{{ $v('nama') }}" required maxlength="120" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">
                    Kata Sandi {!! $isEdit ? '<span class="text-gray-400">(kosongkan bila tidak diubah)</span>' : '<span class="text-red-500">*</span>' !!}
                </label>
                <input type="password" name="password" minlength="8" {{ $isEdit ? '' : 'required' }} class="w-full border rounded px-3 py-2" autocomplete="new-password">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Peran <span class="text-red-500">*</span></label>
                <select name="role_id" required class="w-full border rounded px-3 py-2">
                    <option value="">— pilih peran —</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" @selected((string) $v('role_id') === (string) $r->id)>{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="aktif" @selected($v('status', 'aktif') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($v('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Kepegawaian --}}
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Data Kepegawaian</h2>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Unit Kerja</label>
                <select name="unit_kerja_id" class="w-full border rounded px-3 py-2">
                    <option value="">— pilih unit —</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected((string) $v('unit_kerja_id') === (string) $u->id)>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Atasan Langsung</label>
                <select name="atasan_id" class="w-full border rounded px-3 py-2">
                    <option value="">— tidak ada —</option>
                    @foreach($atasan as $a)
                        <option value="{{ $a->id }}" @selected((string) $v('atasan_id') === (string) $a->id)>{{ $a->nama }} ({{ $a->nip }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Jabatan</label>
                <input name="jabatan" value="{{ $v('jabatan') }}" maxlength="120" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Jenis Pegawai <span class="text-red-500">*</span></label>
                <select name="jenis_pegawai" required class="w-full border rounded px-3 py-2">
                    @foreach(['PNS' => 'PNS', 'PPPK_PENUH' => 'PPPK Penuh Waktu', 'PPPK_PARUH_WAKTU' => 'PPPK Paruh Waktu'] as $k => $lbl)
                        <option value="{{ $k }}" @selected($v('jenis_pegawai', 'PNS') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Pangkat / Golongan</label>
                <input name="pangkat_gol" value="{{ $v('pangkat_gol') }}" maxlength="30" placeholder="mis. Penata Muda, III/a" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">TMT Golongan</label>
                <input type="date" name="tmt_golongan" value="{{ $v('tmt_golongan') ? \Illuminate\Support\Carbon::parse($v('tmt_golongan'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Eselon</label>
                <input name="eselon" value="{{ $v('eselon') }}" maxlength="30" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">TMT Jabatan</label>
                <input type="date" name="tmt_jabatan" value="{{ $v('tmt_jabatan') ? \Illuminate\Support\Carbon::parse($v('tmt_jabatan'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Status Pegawai</label>
                <input name="status_pegawai" value="{{ $v('status_pegawai') }}" maxlength="30" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">TMT Pegawai</label>
                <input type="date" name="tmt_pegawai" value="{{ $v('tmt_pegawai') ? \Illuminate\Support\Carbon::parse($v('tmt_pegawai'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">TMT CPNS</label>
                <input type="date" name="tmt_cpns" value="{{ $v('tmt_cpns') ? \Illuminate\Support\Carbon::parse($v('tmt_cpns'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">TMT PNS</label>
                <input type="date" name="tmt_pns" value="{{ $v('tmt_pns') ? \Illuminate\Support\Carbon::parse($v('tmt_pns'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Masa Kerja (tahun)</label>
                <input type="number" min="0" name="masa_kerja_tahun" value="{{ $v('masa_kerja_tahun', 0) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Masa Kerja (bulan)</label>
                <input type="number" min="0" name="masa_kerja_bulan" value="{{ $v('masa_kerja_bulan', 0) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Gaji Pokok</label>
                <input type="number" step="0.01" min="0" name="gaji_pokok" value="{{ $v('gaji_pokok') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>
    </div>

    {{-- Identitas & kontak --}}
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Identitas & Kontak</h2>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="block text-gray-600 mb-1">Tempat Lahir</label>
                <input name="tempat_lahir" value="{{ $v('tempat_lahir') }}" maxlength="80" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ $v('tanggal_lahir') ? \Illuminate\Support\Carbon::parse($v('tanggal_lahir'))->toDateString() : '' }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    <option value="L" @selected($v('jenis_kelamin') === 'L')>Laki-laki</option>
                    <option value="P" @selected($v('jenis_kelamin') === 'P')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Agama</label>
                <input name="agama" value="{{ $v('agama') }}" maxlength="30" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Status Perkawinan</label>
                <input name="status_perkawinan" value="{{ $v('status_perkawinan') }}" maxlength="20" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Pendidikan Awal</label>
                <input name="pendidikan_awal" value="{{ $v('pendidikan_awal') }}" maxlength="60" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Pendidikan Akhir</label>
                <input name="pendidikan_akhir" value="{{ $v('pendidikan_akhir') }}" maxlength="60" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">No. Askes</label>
                <input name="no_askes" value="{{ $v('no_askes') }}" maxlength="40" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">NPWP</label>
                <input name="npwp" value="{{ $v('npwp') }}" maxlength="30" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">No. KTP</label>
                <input name="no_ktp" value="{{ $v('no_ktp') }}" maxlength="20" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">No. Telepon</label>
                <input name="no_telp" value="{{ $v('no_telp') }}" maxlength="25" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">No. HP</label>
                <input name="no_hp" value="{{ $v('no_hp') }}" maxlength="20" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Email</label>
                <input type="email" name="email" value="{{ $v('email') }}" maxlength="120" class="w-full border rounded px-3 py-2">
            </div>
            <div class="md:col-span-3">
                <label class="block text-gray-600 mb-1">Alamat</label>
                <input name="alamat" value="{{ $v('alamat') }}" maxlength="255" class="w-full border rounded px-3 py-2">
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Pegawai' }}</button>
        <a href="{{ route('admin.pegawai.index') }}" class="px-5 py-2 rounded border hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
