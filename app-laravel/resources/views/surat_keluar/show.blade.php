@extends('layouts.app')
@section('title', 'Detail Surat Keluar · ' . $surat->nomor_surat)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('surat-keluar.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Buku Agenda Surat Keluar</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">Detail Surat Keluar</h1>
        <p class="font-mono text-sm font-bold text-primary-700 mt-0.5">{{ $surat->nomor_surat }}</p>
    </div>

    @if($surat->file_path)
        <a href="{{ route('surat-keluar.unduh', $surat) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition">
            Unduh Berkas Naskah
        </a>
    @endif
</div>

<div class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card max-w-2xl">
    <dl class="text-sm space-y-3">
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Nomor Urut:</dt> <dd class="font-mono font-bold text-slate-900">#{{ $surat->no_urut }} (Tahun {{ $surat->tahun }})</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Nomor Surat Resmi:</dt> <dd class="font-mono font-bold text-primary-700">{{ $surat->nomor_surat }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Kode Klasifikasi:</dt> <dd class="text-slate-900">{{ optional($surat->kodeKlasifikasi)->labelLengkap() }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Instansi Tujuan:</dt> <dd class="font-semibold text-slate-900">{{ $surat->instansi_tujuan }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Tanggal Surat:</dt> <dd class="text-slate-900">{{ optional($surat->tgl_surat)->format('d/m/Y') }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Perihal:</dt> <dd class="font-medium text-slate-900 text-right">{{ $surat->perihal }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Penanggung Jawab:</dt> <dd class="text-slate-900">{{ $surat->penanggung_jawab }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2"><dt class="text-slate-500">Keterangan:</dt> <dd class="text-slate-700 text-right">{{ $surat->keterangan ?? '-' }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-500">Dicatat Oleh:</dt> <dd class="text-slate-900">{{ optional($surat->pencatat)->nama ?? '-' }}</dd></div>
    </dl>
</div>
@endsection

