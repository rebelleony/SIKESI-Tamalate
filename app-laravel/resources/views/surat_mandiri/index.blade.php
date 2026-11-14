@extends('layouts.app')
@section('title', 'Kelola Layanan Surat Mandiri · Kecamatan Tamalate')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200 mb-2">
            Portal Layanan Kelurahan & Unit Internal
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Antrean Pengajuan Surat Mandiri
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Kelola permohonan Surat Pengantar, Undangan, dan Permohonan Fasilitasi dari kelurahan/unit kerja.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('surat-mandiri.portal') }}" target="_blank"
           class="px-4 py-2.5 rounded-xl border border-blue-200 bg-white hover:bg-blue-50 text-primary-700 font-semibold text-sm transition">
            Buka Portal Publik
        </a>
        <a href="{{ route('surat-mandiri.tracking') }}" target="_blank"
           class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm transition">
            Halaman Tracking
        </a>
    </div>
</div>

<!-- Filter -->
<form method="GET" action="{{ route('surat-mandiri.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari kode tracking, nama pemohon, unit asal, atau perihal..."
           class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    <select name="status" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        <option value="">Semua Status</option>
        @foreach(\App\Models\PengajuanSuratMandiri::STATUS as $k => $lbl)
            <option value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $lbl }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold transition">Filter</button>
</form>

<div class="space-y-4">
    @forelse($daftar as $item)
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-card">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono font-extrabold text-sm text-primary-700">{{ $item->kode_tracking }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">{{ $item->jenis_surat }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">{{ $item->labelStatus() }}</span>
                    </div>
                    <div class="font-bold text-slate-900">{{ $item->perihal }}</div>
                    <div class="text-xs text-slate-600">
                        Pemohon: <strong>{{ $item->nama_pemohon }}</strong> ({{ $item->unit_asal }}) · {{ $item->created_at->format('d/m/Y H:i') }}
                        @if($item->file_pendukung)
                            · <a href="{{ route('surat-mandiri.unduh', $item) }}" class="text-primary-700 font-semibold hover:underline">Unduh Lampiran</a>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('surat-mandiri.status', $item) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                        @foreach(\App\Models\PengajuanSuratMandiri::STATUS as $k => $lbl)
                            <option value="{{ $k }}" @selected($item->status === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <select name="surat_keluar_id" class="border border-slate-200 rounded-xl px-3 py-2 text-xs max-w-[200px]">
                        <option value="">— Tautkan Surat Keluar —</option>
                        @foreach($suratKeluarList as $sk)
                            <option value="{{ $sk->id }}" @selected($item->surat_keluar_id === $sk->id)>{{ $sk->nomor_surat }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="catatan_admin" value="{{ $item->catatan_admin }}" placeholder="Catatan / alasan tolak"
                           class="border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    <button type="submit" class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white border border-blue-100 rounded-2xl p-10 text-center text-slate-400">
            Belum ada pengajuan surat mandiri.
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $daftar->links() }}
</div>
@endsection

