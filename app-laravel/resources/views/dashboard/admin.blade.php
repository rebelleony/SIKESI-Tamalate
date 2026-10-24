@extends('layouts.app')
@section('title', 'Dashboard Super Admin · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-rose-600"></span>
            Super Administrator
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Pusat Kendali & Data Master
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pantau ringkasan kepegawaian, verifikasi pengajuan, dan struktur akun Kecamatan Tamalate.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.master') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Kelola Data Master (UC-03)
        </a>
    </div>
</div>

<!-- 4 Key Stat Cards (Turikale Style) -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Pegawai Aktif -->
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 border-l-blue-600">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pegawai Terdaftar</p>
                <div class="mt-2 text-3xl font-display font-black text-primary-950">{{ $totalPegawai }}</div>
                <p class="text-xs text-slate-500 mt-1">ASN & PPPK</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Total Pengajuan -->
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 border-l-indigo-600">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengajuan</p>
                <div class="mt-2 text-3xl font-display font-black text-indigo-950">{{ $totalPengajuan }}</div>
                <p class="text-xs text-slate-500 mt-1">Seluruh jenis layanan</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Menunggu Verifikasi -->
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 border-l-amber-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Menunggu Verifikasi</p>
                <div class="mt-2 text-3xl font-display font-black text-amber-700">{{ $menungguVerifikasi }}</div>
                <p class="text-xs text-slate-500 mt-1">Antrean staf arsip</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Disetujui Selesai -->
    <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 border-l-emerald-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Disetujui / Selesai</p>
                <div class="mt-2 text-3xl font-display font-black text-emerald-700">{{ $disetujui }}</div>
                <p class="text-xs text-slate-500 mt-1">SK / Izin diterbitkan</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Link Banner for UC-03 Master -->
<div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
    <h3 class="font-display font-bold text-base text-primary-950 mb-2">Pintasan Manajemen Data Master (UC-03)</h3>
    <p class="text-xs text-slate-500 mb-4">Akses cepat modul kepegawaian dan organisasi:</p>
    <div class="grid sm:grid-cols-3 gap-3">
        <a href="{{ route('admin.pegawai.index') }}" class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-blue-50/60 hover:border-blue-200 transition block">
            <span class="block font-bold text-sm text-slate-800">Kelola Akun Pegawai</span>
            <span class="block text-xs text-slate-500 mt-0.5">Tambah, ubah data, atur atasan & reset sandi</span>
        </a>
        <a href="{{ route('admin.unit.index') }}" class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-blue-50/60 hover:border-blue-200 transition block">
            <span class="block font-bold text-sm text-slate-800">Kelola Unit Kerja</span>
            <span class="block text-xs text-slate-500 mt-0.5">Kecamatan, 11 Kelurahan & Seksi</span>
        </a>
        <a href="{{ route('admin.role.index') }}" class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-blue-50/60 hover:border-blue-200 transition block">
            <span class="block font-bold text-sm text-slate-800">Kelola Hak Akses / Peran</span>
            <span class="block text-xs text-slate-500 mt-0.5">Struktur RBAC alur verifikasi SOP</span>
        </a>
    </div>
</div>
@endsection
