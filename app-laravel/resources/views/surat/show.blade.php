@extends('layouts.app')
@section('title', 'Detail Surat Masuk · ' . $surat->nomor_agenda)

@section('content')
@php $u = auth()->user(); @endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('surat.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Buku Agenda</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">Detail Surat Masuk</h1>
        <p class="text-sm text-slate-500">
            <span class="font-mono font-bold text-primary-700">{{ $surat->nomor_agenda }}</span>
            @if($surat->no_agenda) · No. Urut #{{ $surat->no_agenda }} @endif
            · Status: <span class="font-bold text-slate-800">{{ $surat->labelStatus() }}</span>
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('surat.lembar-disposisi', $surat) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-sm transition">
            Cetak Lembar Disposisi
        </a>
        @if($surat->file_path)
            <a href="{{ route('surat.unduh', $surat) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition">
                Unduh Scan PDF
            </a>
        @endif
    </div>
</div>

@if($surat->status === 'ditolak')
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-900">
        <div class="font-bold text-sm mb-1">Surat Masuk Ditolak</div>
        <p class="text-sm"><strong>Alasan Penolakan:</strong> {{ $surat->alasan_penolakan }}</p>
        <p class="text-xs text-rose-700 mt-1">
            Ditolak oleh {{ optional($surat->penolak)->nama ?? '-' }} pada {{ optional($surat->ditolak_waktu)->format('d/m/Y H:i') }}
        </p>
    </div>
@endif

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-5">
        <div>
            <h2 class="font-display font-bold text-base text-primary-950 mb-3 border-b border-slate-100 pb-2">Informasi Buku Agenda</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Nomor Surat:</dt> <dd class="font-mono font-semibold text-slate-900 text-right">{{ $surat->nomor_surat }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Kode Klasifikasi:</dt> <dd class="text-slate-900 text-right">{{ $surat->kodeKlasifikasi ? $surat->kodeKlasifikasi->labelLengkap() : ($surat->klasifikasi ?? '-') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Instansi Pengirim:</dt> <dd class="font-medium text-slate-900 text-right">{{ $surat->asal_surat ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Ditujukan Kepada:</dt> <dd class="text-slate-900 text-right">{{ $surat->tujuan ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Perihal:</dt> <dd class="font-medium text-slate-900 text-right">{{ $surat->perihal }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Tanggal Surat:</dt> <dd class="text-slate-900 text-right">{{ optional($surat->tanggal_surat)->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Tanggal Diterima:</dt> <dd class="text-slate-900 text-right">{{ optional($surat->tanggal_terima)->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Sifat:</dt> <dd class="font-semibold text-slate-900 text-right">{{ ucfirst($surat->sifat) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Keterangan:</dt> <dd class="text-slate-700 text-right">{{ $surat->keterangan ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Dicatat Oleh:</dt> <dd class="text-slate-900 text-right">{{ optional($surat->pencatat)->nama ?? '-' }}</dd></div>
            </dl>
        </div>

        <div>
            <h2 class="font-display font-bold text-base text-primary-950 mb-3 border-b border-slate-100 pb-2">Jejak Disposisi Berjenjang</h2>
            <dl class="text-sm space-y-2">
                <div><dt class="inline text-slate-500">Catatan Awal:</dt> <dd class="inline text-slate-800">{{ $surat->disposisi ?? '-' }}</dd></div>
                <div>
                    <dt class="inline text-slate-500">Paraf Digital Sekcam:</dt>
                    <dd class="inline font-medium text-slate-900">
                        {{ optional($surat->pemaraf)->nama ?? 'Belum diparaf' }}
                        {{ $surat->paraf_waktu ? ' (' . $surat->paraf_waktu->format('d/m/Y H:i') . ')' : '' }}
                    </dd>
                    @if($surat->paraf_token)
                        <div class="text-[11px] font-mono text-emerald-700">Token Paraf: {{ Str::limit($surat->paraf_token, 24) }}</div>
                    @endif
                </div>
                <div>
                    <dt class="inline text-slate-500">Disposisi & TTD Camat:</dt>
                    <dd class="inline font-medium text-slate-900">
                        {{ optional($surat->pendisposisi)->nama ?? 'Belum didisposisi' }}
                        {{ $surat->disposisi_waktu ? ' (' . $surat->disposisi_waktu->format('d/m/Y H:i') . ')' : '' }}
                    </dd>
                    @if($surat->disposisi_token)
                        <div class="text-[11px] font-mono text-blue-700">Token TTD: {{ Str::limit($surat->disposisi_token, 24) }}</div>
                    @endif
                </div>
                <div><dt class="inline text-slate-500">Unit Tujuan Disposisi:</dt> <dd class="inline font-semibold text-primary-700">{{ optional($surat->tujuanUnit)->nama ?? '-' }}</dd></div>
                <div><dt class="inline text-slate-500">Isi Disposisi:</dt> <dd class="inline text-slate-800">{{ $surat->tujuan_disposisi ?? '-' }}</dd></div>
                <div>
                    <dt class="inline text-slate-500">Distribusi ke Unit:</dt>
                    <dd class="inline text-slate-900">
                        {{ optional($surat->pendistribusi)->nama ?? 'Belum didistribusikan' }}
                        {{ $surat->distribusi_waktu ? ' (' . $surat->distribusi_waktu->format('d/m/Y H:i') . ')' : '' }}
                    </dd>
                </div>
                <div><dt class="inline text-slate-500">Status Arsip:</dt> <dd class="inline font-semibold text-slate-900">{{ $surat->arsip ? $surat->arsip->kode_arsip : 'Belum diarsipkan' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Tahap Sekcam: Paraf Digital atau Tolak Surat --}}
        @if($u->hasRole('verifikator_pimpinan', 'pimpinan') && $surat->status === 'dicatat')
            <div class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-4">
                <h2 class="font-display font-bold text-base text-primary-950">Tahap Sekcam: Pemeriksaan & Paraf Digital</h2>
                <form method="POST" action="{{ route('surat.paraf', $surat) }}">
                    @csrf
                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition">
                        ✓ Beri Paraf Digital & Teruskan ke Camat
                    </button>
                </form>

                <hr class="border-slate-100">

                <form method="POST" action="{{ route('surat.tolak', $surat) }}" class="space-y-3">
                    @csrf
                    <label class="block text-xs font-bold uppercase tracking-wider text-rose-700">Opsi Tolak Surat (Alasan Wajib)</label>
                    <textarea name="alasan_penolakan" rows="2" required placeholder="Tuliskan alasan penolakan surat..."
                              class="w-full border border-rose-200 rounded-xl px-3 py-2 text-sm focus:border-rose-500 focus:outline-none"></textarea>
                    <button class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition">
                        ✕ Tolak Surat
                    </button>
                </form>
            </div>
        @endif

        {{-- Tahap Camat: Disposisi & Pilih Unit Tujuan atau Tolak Surat --}}
        @if($u->hasRole('pimpinan') && $surat->status === 'diparaf_sekcam')
            <div class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-4">
                <h2 class="font-display font-bold text-base text-primary-950">Tahap Camat: Disposisi & Tanda Tangan Digital</h2>
                <form method="POST" action="{{ route('surat.disposisi', $surat) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Unit Tujuan (Seksi / Subbag / Kelurahan)</label>
                        <select name="tujuan_unit_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                            <option value="">— Pilih Unit Kerja Tujuan —</option>
                            @foreach($unitsDisposisi as $unit)
                                <option value="{{ $unit->id }}">{{ ucfirst($unit->tipe) }} — {{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Instruksi / Catatan Disposisi <span class="text-rose-600">*</span></label>
                        <textarea name="tujuan_disposisi" rows="3" required placeholder="mis. Segera tindaklanjuti dan laporkan hasilnya"
                                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"></textarea>
                    </div>
                    <button class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition">
                        ✓ Tanda Tangani & Disposisikan
                    </button>
                </form>

                <hr class="border-slate-100">

                <form method="POST" action="{{ route('surat.tolak', $surat) }}" class="space-y-3">
                    @csrf
                    <label class="block text-xs font-bold uppercase tracking-wider text-rose-700">Opsi Tolak Surat (Alasan Wajib)</label>
                    <textarea name="alasan_penolakan" rows="2" required placeholder="Tuliskan alasan penolakan surat..."
                              class="w-full border border-rose-200 rounded-xl px-3 py-2 text-sm focus:border-rose-500 focus:outline-none"></textarea>
                    <button class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition">
                        ✕ Tolak Surat
                    </button>
                </form>
            </div>
        @endif

        {{-- Tahap Operator: Distribusi ke Unit Tujuan --}}
        @if($u->hasRole('operator') && $surat->status === 'didisposisi')
            <form method="POST" action="{{ route('surat.distribusi', $surat) }}" class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card">
                @csrf
                <h2 class="font-display font-bold text-base text-primary-950 mb-2">Tahap Distribusi</h2>
                <p class="text-sm text-slate-600 mb-4">Kirim surat beserta lembar disposisi ke unit tujuan: <strong>{{ optional($surat->tujuanUnit)->nama ?? $surat->tujuan_disposisi }}</strong></p>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition">
                    Tandai Telah Didistribusikan ke Unit Tujuan
                </button>
            </form>
        @endif

        {{-- Tahap Operator: Arsipkan --}}
        @if($surat->status === 'didistribusikan' && ! $surat->arsip)
            <div class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card">
                <h2 class="font-display font-bold text-base text-primary-950 mb-2">Tahap Akhir: Pengarsipan</h2>
                <p class="text-sm text-slate-600 mb-4">Surat telah didistribusikan dan siap dicatat ke Daftar Arsip Dinamis.</p>
                <a href="{{ route('arsip.create') }}" class="inline-block bg-slate-800 hover:bg-slate-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    Arsipkan Surat Sekarang
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
