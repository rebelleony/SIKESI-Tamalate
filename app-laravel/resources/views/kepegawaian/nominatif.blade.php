@extends('layouts.app')
@section('title', 'Daftar Nominatif Kolektif & Surat Pengantar BKPSDMD')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('kepegawaian.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Layanan Kepegawaian</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">
            Daftar Nominatif Kolektif &amp; Pengantar BKPSDMD
        </h1>
        <p class="text-sm text-slate-500">Pembuatan daftar nominatif kolektif usulan Kenaikan Pangkat dan Pensiun PNS Kecamatan Tamalate.</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('kepegawaian.nominatif', ['jenis' => 'kenaikan_pangkat']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold border {{ $jenis === 'kenaikan_pangkat' ? 'bg-primary-700 text-white border-primary-700' : 'bg-white text-slate-700 border-slate-200' }}">
            Kenaikan Pangkat
        </a>
        <a href="{{ route('kepegawaian.nominatif', ['jenis' => 'pensiun']) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold border {{ $jenis === 'pensiun' ? 'bg-primary-700 text-white border-primary-700' : 'bg-white text-slate-700 border-slate-200' }}">
            Pensiun PNS
        </a>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold">
            Cetak Daftar &amp; Pengantar
        </button>
    </div>
</div>

<!-- Surat Pengantar Ringkas ke BKPSDMD -->
<div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card mb-6">
    <div class="text-center border-b-2 border-slate-800 pb-3 mb-4">
        <div class="text-xs font-bold uppercase tracking-wider">Pemerintah Kota Makassar</div>
        <div class="text-lg font-extrabold uppercase">Kecamatan Tamalate</div>
        <div class="text-xs text-slate-500">SURAT PENGANTAR KOLEKTIF KE BKPSDMD KOTA MAKASSAR</div>
    </div>
    <p class="text-sm text-slate-700 leading-relaxed">
        Kepada Yth. <strong>Kepala BKPSDMD Kota Makassar</strong> di Makassar.<br>
        Bersama ini kami sampaikan Daftar Nominatif Kolektif Usulan <strong>{{ $jenis === 'kenaikan_pangkat' ? 'Kenaikan Pangkat' : 'Pensiun PNS' }}</strong>
        lingkup Kecamatan Tamalate &amp; 11 Kelurahan sebanyak <strong>{{ $daftar->count() }}</strong> orang pegawai sebagaimana terlampir di bawah ini.
    </p>
</div>

<form method="POST" action="{{ route('kepegawaian.nominatif.simpan') }}" class="bg-white border border-blue-100 rounded-2xl shadow-card overflow-hidden">
    @csrf
    <div class="p-4 bg-blue-50/60 border-b border-blue-100 flex flex-wrap items-center gap-3">
        <input type="text" name="nomor_nominatif" required placeholder="Nomor Surat Pengantar Nominatif (mis. 800.1.3.2/012/KT/XI/2026)"
               class="flex-1 min-w-[260px] rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-mono">
        <input type="date" name="tanggal_nominatif" value="{{ now()->toDateString() }}" required
               class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs">
        <button type="submit" class="px-4 py-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs">
            Tetapkan ke Daftar Terpilih
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-700 text-xs font-bold uppercase border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">Pilih</th>
                    <th class="py-3 px-4">NIP / Nama Pegawai</th>
                    <th class="py-3 px-4">Unit Kerja</th>
                    <th class="py-3 px-4">Detail Usulan</th>
                    <th class="py-3 px-4">No. Nominatif</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($daftar as $item)
                    @php $d = $item->data_layanan ?? []; @endphp
                    <tr>
                        <td class="py-3 px-4">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="rounded text-primary-600">
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $item->pegawai->nama ?? '-' }}</div>
                            <div class="font-mono text-xs text-slate-500">{{ $item->pegawai->nip ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-700">{{ $item->unitKerja->nama ?? '-' }}</td>
                        <td class="py-3 px-4 text-xs">
                            @if($jenis === 'kenaikan_pangkat')
                                {{ $d['pangkat_lama'] ?? '-' }} → <strong>{{ $d['pangkat_diusulkan'] ?? '-' }}</strong>
                                (TMT {{ $d['tmt_pangkat_diusulkan'] ?? '-' }})
                            @else
                                Jenis: <strong>{{ $d['jenis_pensiun'] ?? '-' }}</strong> · TMT {{ $d['tmt_pensiun'] ?? '-' }}
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-primary-700">
                            {{ $item->nomor_nominatif ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold">{{ $item->labelStatus() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada usulan {{ str_replace('_', ' ', $jenis) }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
@endsection

