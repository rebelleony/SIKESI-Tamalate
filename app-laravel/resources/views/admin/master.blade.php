@extends('layouts.app')
@section('title', 'Data Master & Akun (UC-03) · Kecamatan Tamalate')

@section('content')
<div class="mb-8">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 mb-2">
        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
        Hak Akses Khusus Super Admin
    </div>
    <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
        Data Master & Manajemen Akun
    </h1>
    <p class="text-sm text-slate-500 mt-1">
        Kelola basis data pegawai, hierarki unit kerja (Kecamatan, 11 Kelurahan, Seksi), dan peran RBAC.
    </p>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Pegawai / Akun -->
    <a href="{{ route('admin.pegawai.index') }}" 
       class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all block group border-l-4 border-l-blue-600 hover:-translate-y-0.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Master Akun</span>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>
        <div class="text-3xl font-display font-black text-primary-950 mt-3">{{ $totalPegawai }}</div>
        <div class="font-display font-extrabold text-base text-slate-800 mt-1">Pegawai & PPPK</div>
        <div class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            <span class="font-bold text-emerald-600">{{ $pegawaiAktif }} akun aktif</span> · Tambah, edit, nonaktifkan akun, set peran & hierarki atasan.
        </div>
    </a>

    <!-- Unit Kerja -->
    <a href="{{ route('admin.unit.index') }}" 
       class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all block group border-l-4 border-l-emerald-600 hover:-translate-y-0.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Hierarki Organisasi</span>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div class="text-3xl font-display font-black text-emerald-950 mt-3">{{ $totalUnit }}</div>
        <div class="font-display font-extrabold text-base text-slate-800 mt-1">Unit Kerja</div>
        <div class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Kecamatan Tamalate, 11 Kelurahan definitif, Seksi, dan Sub-Bagian beserta induk hierarkinya.
        </div>
    </a>

    <!-- Peran RBAC -->
    <a href="{{ route('admin.role.index') }}" 
       class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all block group border-l-4 border-l-amber-500 hover:-translate-y-0.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Otorisasi & RBAC</span>
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>
        <div class="text-3xl font-display font-black text-amber-950 mt-3">{{ $totalRole }}</div>
        <div class="font-display font-extrabold text-base text-slate-800 mt-1">5 Role Utama</div>
        <div class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Struktur RBAC berjenjang: Super Admin, Operator, Verifikator Pimpinan, Pimpinan, dan Pegawai / Staf.
        </div>
    </a>

    <!-- Kode Klasifikasi Arsip -->
    <a href="{{ route('admin.klasifikasi.index') }}"
       class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card hover:shadow-lift transition-all block group border-l-4 border-l-indigo-600 hover:-translate-y-0.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Persuratan Dinamis</span>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
        <div class="text-3xl font-display font-black text-indigo-950 mt-3">{{ \App\Models\KodeKlasifikasi::count() }}</div>
        <div class="font-display font-extrabold text-base text-slate-800 mt-1">Kode Klasifikasi</div>
        <div class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Standar Permendagri No. 83 Tahun 2022 (000, 400, 600, 800, 900).
        </div>
    </a>
</div>

<!-- Impor Excel Card -->
<div class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50/50 p-6 shadow-sm">
    <div class="flex items-start gap-4">
        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
        </div>
        <div>
            <h3 class="font-display font-bold text-base text-primary-950">Impor Massal Data Pegawai dari Excel (BKPSDMD)</h3>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                Tersedia command CLI Artisan untuk mengimpor berkas Excel data PPPK Paruh Waktu / ASN secara massal tanpa menulis ulang:
            </p>
            <div class="mt-3 p-3 rounded-xl bg-slate-900 text-slate-100 font-mono text-xs overflow-x-auto shadow-inner">
                php artisan pegawai:import "storage/app/imports/DATA PEGAWAI PPPK PW.xlsx" --jenis=PPPK_PARUH_WAKTU
            </div>
        </div>
    </div>
</div>
@endsection
