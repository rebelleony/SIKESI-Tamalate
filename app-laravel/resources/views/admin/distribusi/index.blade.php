@extends('layouts.app')
@section('title', 'Distribusi Hasil Layanan · Super Admin')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
            Kewenangan Super Admin: Penyaluran & Distribusi Hasil
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-primary-950">
            Distribusi Hasil Pengajuan Layanan
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Pemeriksaan administrasi akhir, validasi tanda tangan digital Camat/Lurah, dan penerusan dokumen resmi kepada Pegawai/Staf.
        </p>
    </div>
</div>

<!-- 3 Stat Cards Ringkasan -->
<div class="grid sm:grid-cols-3 gap-5 mb-8">
    <a href="{{ route('admin.distribusi.index', ['tab' => 'menunggu_distribusi']) }}" 
       class="rounded-2xl border bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 {{ $tab === 'menunggu_distribusi' ? 'border-l-emerald-600 ring-2 ring-emerald-500/20' : 'border-l-emerald-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Siap Didistribusikan</p>
                <div class="mt-2 text-3xl font-display font-black text-emerald-700">{{ $counts['menunggu_distribusi'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Ditandatangani Camat/Lurah</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.distribusi.index', ['tab' => 'penolakan']) }}" 
       class="rounded-2xl border bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 {{ $tab === 'penolakan' ? 'border-l-rose-600 ring-2 ring-rose-500/20' : 'border-l-rose-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Penyampaian Penolakan</p>
                <div class="mt-2 text-3xl font-display font-black text-rose-700">{{ $counts['penolakan'] }}</div>
                <p class="text-xs text-slate-500 mt-1">Ditolak pejabat struktural</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.distribusi.index', ['tab' => 'selesai']) }}" 
       class="rounded-2xl border bg-white p-5 shadow-card hover:shadow-lift transition-all border-l-4 {{ $tab === 'selesai' ? 'border-l-blue-600 ring-2 ring-blue-500/20' : 'border-l-blue-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Selesai / Terdistribusi</p>
                <div class="mt-2 text-3xl font-display font-black text-blue-700">{{ $counts['selesai'] }}</div>
                <p class="text-xs text-slate-500 mt-1">SK/Hasil diterima pegawai</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </a>
</div>

<!-- Navigasi Tabs -->
<div class="flex border-b border-slate-200 mb-6 gap-2">
    <a href="{{ route('admin.distribusi.index', ['tab' => 'menunggu_distribusi']) }}" 
       class="px-4 py-2.5 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'menunggu_distribusi' ? 'border-primary-700 text-primary-900 bg-blue-50/50 rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
        <span>Siap Didistribusikan</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-extrabold">{{ $counts['menunggu_distribusi'] }}</span>
    </a>
    <a href="{{ route('admin.distribusi.index', ['tab' => 'penolakan']) }}" 
       class="px-4 py-2.5 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'penolakan' ? 'border-rose-600 text-rose-900 bg-rose-50/50 rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
        <span>Penyampaian Penolakan</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-800 font-extrabold">{{ $counts['penolakan'] }}</span>
    </a>
    <a href="{{ route('admin.distribusi.index', ['tab' => 'selesai']) }}" 
       class="px-4 py-2.5 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'selesai' ? 'border-blue-600 text-blue-900 bg-blue-50/50 rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
        <span>Riwayat Selesai</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-700 font-extrabold">{{ $counts['selesai'] }}</span>
    </a>
</div>

<!-- Filter & Search Bar -->
<form method="GET" class="bg-white p-4 rounded-2xl border border-blue-100 shadow-xs mb-6 flex flex-wrap gap-3 items-center">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama pemohon, NIP, nomor berkas..." 
               class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
    </div>
    <div>
        <select name="layanan" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
            <option value="">Semua Jenis Layanan</option>
            <option value="cuti" {{ $filter['layanan'] === 'cuti' ? 'selected' : '' }}>Cuti Pegawai</option>
            <option value="kgb" {{ $filter['layanan'] === 'kgb' ? 'selected' : '' }}>Kenaikan Gaji Berkala (KGB)</option>
            <option value="kenaikan_pangkat" {{ $filter['layanan'] === 'kenaikan_pangkat' ? 'selected' : '' }}>Kenaikan Pangkat</option>
            <option value="pensiun" {{ $filter['layanan'] === 'pensiun' ? 'selected' : '' }}>Pensiun</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition">
        Saring
    </button>
</form>

<!-- Tabel Pengajuan -->
<div class="rounded-2xl border border-blue-100 bg-white shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-blue-50/60 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-blue-100">
                <tr>
                    <th class="py-3.5 px-6">Nomor Berkas</th>
                    <th class="py-3.5 px-4">Nama Pemohon</th>
                    <th class="py-3.5 px-4">Jenis Layanan</th>
                    <th class="py-3.5 px-4">Tanda Tangan Pejabat</th>
                    <th class="py-3.5 px-4">Status Terkini</th>
                    <th class="py-3.5 px-6 text-right">Aksi Super Admin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengajuan as $p)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="py-3.5 px-6 font-mono font-semibold text-xs text-slate-800">
                        {{ $p->nomor_ajuan }}
                        <span class="block text-[10px] text-slate-400 font-sans mt-0.5">{{ optional($p->tanggal_ajuan)->format('d/m/Y') }}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="font-bold text-slate-900 block text-xs">{{ $p->pegawai->nama ?? '-' }}</span>
                        <span class="text-[11px] font-mono text-slate-500 block">NIP. {{ $p->pegawai->nip ?? '-' }}</span>
                        <span class="text-[10px] text-slate-400">{{ $p->pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                        {{ ucfirst(str_replace('_', ' ', $p->jenis_layanan)) }}
                    </td>
                    <td class="py-3.5 px-4">
                        @if($p->isDitandatangani())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ $p->tandaTanganPejabat->nama ?? 'Camat/Lurah' }}
                            </span>
                            <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ optional($p->tanda_tangan_waktu)->format('d/m/Y H:i') }} WITA</span>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ditandatangani</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold 
                            {{ $p->status === 'disetujui_dan_ditandatangani' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                            {{ in_array($p->status, ['ditolak_tier1', 'ditolak_final']) ? 'bg-rose-50 text-rose-800 border border-rose-200' : '' }}
                            {{ $p->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                            {{ $p->status === 'selesai_ditolak' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                            {{ $p->labelStatus() }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <a href="{{ route('admin.distribusi.show', $p) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition">
                            Periksa & Proses →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="font-semibold text-sm text-slate-600">Tidak ada pengajuan pada tab ini.</p>
                        <p class="text-xs text-slate-400 mt-1">Semua berkas telah diproses sesuai alur distribusi.</p>
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
