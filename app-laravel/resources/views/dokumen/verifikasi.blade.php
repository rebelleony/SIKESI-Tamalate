@extends('layouts.app')
@section('title', 'Verifikasi Dokumen & Tanda Tangan Digital · Kecamatan Tamalate')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <div class="mb-6 text-center">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Layanan Verifikasi Publik Dokumen Resmi
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Verifikasi Tanda Tangan Digital
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pemerintah Kecamatan Tamalate · Kota Makassar · Sulawesi Selatan
        </p>
    </div>

    <!-- Card Sertifikat Verifikasi -->
    <div class="rounded-2xl border border-emerald-200 bg-white p-6 sm:p-8 shadow-card overflow-hidden relative">
        <div class="h-2 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600 absolute top-0 left-0 right-0"></div>

        <div class="flex items-center gap-4 mb-6 pt-2 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 uppercase tracking-wider mb-1">
                    ✓ Dokumen Asli & Sah
                </span>
                <h2 class="text-lg font-display font-bold text-slate-900 leading-snug">
                    Tersertifikasi Elektronik Kecamatan Tamalate
                </h2>
                <p class="text-xs text-slate-500">Tanda tangan digital pejabat tercatat secara kriptografis dan tidak dapat dipalsukan.</p>
            </div>
        </div>

        <!-- Detail Grid -->
        <div class="grid sm:grid-cols-2 gap-6 text-sm mb-6">
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Pengajuan</h3>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                    <div>
                        <span class="text-[11px] text-slate-500 block">Nomor Berkas:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $pengajuan->nomor_ajuan }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Jenis Layanan:</span>
                        <span class="font-bold text-primary-700">{{ ucfirst(str_replace('_', ' ', $pengajuan->jenis_layanan)) }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Nama Pemohon:</span>
                        <span class="font-bold text-slate-800">{{ $pengajuan->pegawai->nama ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block font-mono">NIP: {{ $pengajuan->pegawai->nip ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 block">Unit Kerja:</span>
                        <span class="text-xs text-slate-700 font-medium">{{ $pengajuan->pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tanda Tangan Pejabat</h3>
                <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100 space-y-2">
                    <div>
                        <span class="text-[11px] text-emerald-700 block">Pejabat Penandatangan:</span>
                        <span class="font-bold text-emerald-950">{{ $pengajuan->tandaTanganPejabat->nama ?? ($pengajuan->tanda_tangan_payload['penandatangan']['nama'] ?? '-') }}</span>
                        <span class="text-xs text-emerald-800 block font-mono">NIP: {{ $pengajuan->tandaTanganPejabat->nip ?? ($pengajuan->tanda_tangan_payload['penandatangan']['nip'] ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-emerald-700 block">Jabatan:</span>
                        <span class="text-xs font-semibold text-emerald-900">{{ $pengajuan->tandaTanganPejabat->jabatan ?? ($pengajuan->tanda_tangan_payload['penandatangan']['jabatan'] ?? 'Camat / Lurah') }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-emerald-700 block">Waktu Penandatanganan:</span>
                        <span class="font-bold text-emerald-950 text-xs">
                            {{ $pengajuan->tanda_tangan_waktu ? $pengajuan->tanda_tangan_waktu->timezone('Asia/Makassar')->format('d F Y, H:i:s') . ' WITA' : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Token Kriptografis -->
        <div class="mb-6 p-4 rounded-xl bg-slate-900 text-slate-200 text-xs space-y-1.5 shadow-inner">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Token Verifikasi Elektronik (SHA-256 HMAC):</span>
            <div class="font-mono text-[11px] break-all text-emerald-400 bg-slate-950/70 p-2 rounded-lg border border-slate-800">
                {{ $pengajuan->tanda_tangan_token }}
            </div>
            <p class="text-[10px] text-slate-400 pt-1">
                Metode: Sertifikasi Digital SIKESI Tamalate · Integritas dokumen terjamin dari perubahan tanpa hak.
            </p>
        </div>

        <!-- Status Distribusi Super Admin -->
        <div class="p-4 rounded-xl border {{ $pengajuan->isSelesai() ? 'bg-blue-50/80 border-blue-200 text-blue-950' : 'bg-amber-50/80 border-amber-200 text-amber-950' }} text-xs flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $pengajuan->isSelesai() ? 'text-blue-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <span class="font-bold block text-sm">
                    Status Distribusi: {{ $pengajuan->isSelesai() ? 'Telah Didistribusikan oleh Super Admin' : 'Menunggu Distribusi Administrasi oleh Super Admin' }}
                </span>
                @if($pengajuan->isSelesai())
                    <p class="mt-0.5 text-blue-800">
                        Didistribusikan kepada pemohon pada {{ optional($pengajuan->distribusi_waktu)->format('d/m/Y H:i') }} WITA.
                        @if($pengajuan->distribusi_catatan)
                            <span class="block mt-1 italic font-sans text-blue-700">"{{ $pengajuan->distribusi_catatan }}"</span>
                        @endif
                    </p>
                @else
                    <p class="mt-0.5 text-amber-800">
                        Dokumen telah disetujui & ditandatangani pejabat, saat ini sedang dalam proses penyiapan administrasi akhir oleh Super Admin sebelum diserahkan kepada pemohon.
                    </p>
                @endif
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-primary-700 hover:text-primary-800 flex items-center gap-1">
                ← Kembali ke Dashboard
            </a>
            @if($pengajuan->isSelesai() && auth()->check())
                <a href="{{ route('pengajuan.dokumen', ['pengajuan' => $pengajuan, 'unduh' => 1]) }}" class="px-4 py-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Salinan Dokumen PDF Resmi
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
