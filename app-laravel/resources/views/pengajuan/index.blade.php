@extends('layouts.app')
@section('title', 'Daftar Pengajuan Saya · Kecamatan Tamalate')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Pelayanan Kepegawaian Mandiri
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Daftar Pengajuan Layanan
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pantau status verifikasi, persetujuan berjenjang, dan penerbitan SK/Surat Izin resmi Anda.
        </p>
    </div>

    @if(auth()->user()->hasRole('pegawai') && auth()->user()->berhakLayanan())
        <a href="{{ route('kepegawaian.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-sm shadow-card transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengajuan Baru
        </a>
    @endif
</div>

<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">Nomor Berkas</th>
                    <th class="py-3.5 px-4">Pemohon / Unit</th>
                    <th class="py-3.5 px-4">Jenis Layanan</th>
                    <th class="py-3.5 px-4">Status Alur SOP</th>
                    <th class="py-3.5 px-4">Tanggal Diajukan</th>
                    <th class="py-3.5 px-6 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengajuan as $item)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-4 px-6 font-mono font-semibold text-xs text-slate-800">
                        {{ $item['nomor'] }}
                        <span class="ml-1 inline-flex px-2 py-0.5 rounded text-[10px] font-sans font-bold bg-slate-100 text-slate-600">{{ $item['sumber'] }}</span>
                    </td>
                    <td class="py-4 px-4">
                        <div class="font-semibold text-slate-900 text-xs">{{ $item['pemohon'] }}</div>
                        <div class="text-[11px] text-slate-500">{{ $item['unit'] }}</div>
                    </td>
                    <td class="py-4 px-4 font-bold text-slate-800">
                        {{ $item['layanan'] }}
                    </td>
                    <td class="py-4 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $item['status_label'] }}
                        </span>
                    </td>
                    <td class="py-4 px-4 text-xs text-slate-500">
                        {{ $item['tanggal'] ?? '-' }}
                    </td>
                    <td class="py-4 px-6 text-right">
                        <a href="{{ $item['url'] }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 font-bold text-xs border border-primary-200 transition">
                            Lihat Detail →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Belum ada berkas pengajuan.</p>
                        <p class="text-xs text-slate-400 mt-1">Gunakan tombol "+ Buat Pengajuan Baru" di atas untuk mengajukan layanan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $pengajuan->links() }}
    </div>
</div>
@endsection
