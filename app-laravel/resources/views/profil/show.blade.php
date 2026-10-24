@extends('layouts.app')
@section('title', 'Profil Saya · SI Kepegawaian Tamalate')

@section('content')
<div class="mb-8">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
        Identitas & Keamanan Akun (UC-02)
    </div>
    <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
        Profil Saya
    </h1>
    <p class="text-sm text-slate-500 mt-1">
        Kelola informasi kontak, foto profil, dan kata sandi akun Anda secara mandiri.
    </p>
</div>

<div class="grid lg:grid-cols-12 gap-6">
    <!-- Identitas (Baca-Saja) Card -->
    <div class="lg:col-span-4">
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card border-l-4 border-l-gold">
            <div class="flex flex-col items-center mb-6">
                @if($user->foto_profil)
                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="Foto profil" 
                         class="w-28 h-28 rounded-full object-cover border-2 border-primary-300 shadow-md">
                @else
                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-primary-700 to-blue-900 text-white flex items-center justify-center text-4xl font-black shadow-md border-2 border-primary-200">
                        {{ strtoupper(substr($user->nama, 0, 1)) }}
                    </div>
                @endif
                <h2 class="font-display font-extrabold text-base text-primary-950 mt-3 text-center">{{ $user->nama }}</h2>
                <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $user->role->nama_role ?? '-' }}
                </span>
            </div>

            <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-3 border-b border-slate-100 pb-2">
                Data Master (Terkunci)
            </h3>
            <dl class="text-xs space-y-2.5">
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">NIP:</dt> <dd class="font-mono font-bold text-slate-800">{{ $user->nip ?: '-' }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Username:</dt> <dd class="font-mono font-semibold text-slate-800">{{ $user->username }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Jabatan:</dt> <dd class="font-semibold text-slate-800 text-right">{{ $user->jabatan ?: '-' }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Unit Kerja:</dt> <dd class="font-semibold text-slate-800 text-right">{{ optional($user->unitKerja)->nama ?? '-' }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Atasan Langsung:</dt> <dd class="font-semibold text-slate-800 text-right">{{ optional($user->atasan)->nama ?? '-' }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Jenis Pegawai:</dt> <dd class="font-semibold text-slate-800 uppercase">{{ str_replace('_', ' ', $user->jenis_pegawai) }}</dd></div>
                <div class="flex justify-between py-1 border-b border-slate-50"><dt class="text-slate-500">Pangkat / Golongan:</dt> <dd class="font-semibold text-slate-800">{{ $user->pangkat_gol ?: '-' }}</dd></div>
                <div class="flex justify-between py-1"><dt class="text-slate-500">Masa Kerja:</dt> <dd class="font-semibold text-slate-800">{{ $user->masaKerjaTeks() }}</dd></div>
            </dl>
            <p class="text-[11px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                * Perubahan data master hanya dapat diproses oleh Super Admin via modul Data Master.
            </p>
        </div>
    </div>

    <!-- Kolom Kanan: Form Kontak & Form Sandi -->
    <div class="lg:col-span-8 space-y-6">
        <!-- Form Kontak & Foto -->
        <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" 
              class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            @csrf @method('PUT')
            <h3 class="font-display font-bold text-base text-primary-950 mb-1">Perbarui Kontak & Foto Profil</h3>
            <p class="text-xs text-slate-500 mb-5">Pastikan nomor handphone dan email aktif untuk notifikasi verifikasi berkas.</p>

            <div class="grid sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="120" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">No. Telepon Rumah</label>
                    <input name="no_telp" value="{{ old('no_telp', $user->no_telp) }}" maxlength="25" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">No. Handphone / WhatsApp</label>
                    <input name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" maxlength="20" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Unggah Foto Profil Baru</label>
                    <input type="file" name="foto_profil" accept="image/png,image/jpeg" 
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold">
                    <p class="text-[10px] text-slate-400 mt-1">Format JPG/PNG, ukuran berkas maksimal 2 MB.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Domisili</label>
                    <input name="alamat" value="{{ old('alamat', $user->alamat) }}" maxlength="255" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-card transition">
                    Simpan Perubahan Kontak
                </button>
            </div>
        </form>

        <!-- Form Ubah Kata Sandi Mandiri -->
        <form method="POST" action="{{ route('profil.password') }}" 
              class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            @csrf @method('PUT')
            <h3 class="font-display font-bold text-base text-primary-950 mb-1">Ganti Kata Sandi Mandiri</h3>
            <p class="text-xs text-slate-500 mb-5">Wajib memasukkan kata sandi lama untuk konfirmasi keamanan akun.</p>

            <div class="grid sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kata Sandi Lama</label>
                    <input type="password" name="password_lama" required 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" name="password" required minlength="8" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-600 mb-1.5">Ulangi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required minlength="8" 
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-card transition">
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
