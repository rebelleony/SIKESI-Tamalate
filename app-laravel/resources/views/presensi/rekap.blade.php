@extends('layouts.app')
@section('title', 'Rekap Bulanan & Peringatan Pembinaan · Kecamatan Tamalate')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('presensi.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Presensi Apel</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">Rekapitulasi Bulanan &amp; Pembinaan Pegawai</h1>
        <p class="text-sm text-slate-500">Ambang batas peringatan pembinaan: <strong>≥ {{ $ambangTk }} kali Tanpa Keterangan (TK)</strong> dalam sebulan.</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('presensi.rekap', ['bulan' => $bulan, 'unit_kerja_id' => $unitId, 'export' => 'xlsx']) }}"
           class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-card transition">
            Ekspor Excel (.xlsx)
        </a>
        <a href="{{ route('presensi.rekap', ['bulan' => $bulan, 'unit_kerja_id' => $unitId, 'export' => 'pdf']) }}"
           class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-card transition">
            Ekspor PDF
        </a>
    </div>
</div>

@if($peringatanCount > 0)
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-900 flex items-center justify-between gap-4">
        <div>
            <div class="font-extrabold text-sm">⚠ Peringatan Pembinaan Disiplin Pegawai</div>
            <p class="text-xs text-rose-800 mt-0.5">
                Terdapat <strong>{{ $peringatanCount }}</strong> pegawai dengan akumulasi Tanpa Keterangan (TK) ≥ {{ $ambangTk }} kali pada bulan {{ $bulan }}. Gunakan tombol <strong>Buat Draf Surat Panggilan Pembinaan</strong> pada baris pegawai terkait.
            </p>
        </div>
    </div>
@endif

<form method="GET" action="{{ route('presensi.rekap') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <input type="month" name="bulan" value="{{ $bulan }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
    <select name="unit_kerja_id" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
        <option value="">Semua Unit Kerja / Kelurahan</option>
        @foreach($units as $u)
            <option value="{{ $u->id }}" @selected((string) $unitId === (string) $u->id)>{{ ucfirst($u->tipe) }} — {{ $u->nama }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold">Tampilkan</button>
</form>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">Pegawai</th>
                    <th class="py-3.5 px-4">Unit Kerja</th>
                    <th class="py-3.5 px-4 text-center">Hadir</th>
                    <th class="py-3.5 px-4 text-center">Izin</th>
                    <th class="py-3.5 px-4 text-center">Dinas Luar</th>
                    <th class="py-3.5 px-4 text-center">TK</th>
                    <th class="py-3.5 px-6 text-right">Pembinaan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($rekap as $row)
                    @php $p = $row['pegawai']; @endphp
                    <tr class="{{ $row['pembinaan'] ? 'bg-rose-50/50' : 'hover:bg-blue-50/20' }}">
                        <td class="py-3.5 px-6">
                            <div class="font-bold text-slate-900">{{ $p->nama }}</div>
                            <div class="font-mono text-xs text-slate-500">{{ $p->nip }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-slate-700">{{ $p->unitKerja->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-700">{{ $row['hadir'] }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-amber-700">{{ $row['izin'] }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-blue-700">{{ $row['dinas_luar'] }}</td>
                        <td class="py-3.5 px-4 text-center font-extrabold {{ $row['pembinaan'] ? 'text-rose-700' : 'text-slate-700' }}">{{ $row['tk'] }}</td>
                        <td class="py-3.5 px-6 text-right">
                            @if($row['pembinaan'])
                                <a href="{{ route('presensi.pembinaan', ['pegawai' => $p->id, 'bulan' => $bulan]) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">
                                    Buat Draf Surat Panggilan Pembinaan
                                </a>
                            @else
                                <span class="text-xs text-emerald-700 font-semibold">Aman</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

