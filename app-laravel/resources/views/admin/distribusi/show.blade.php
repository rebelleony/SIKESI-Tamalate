@extends('layouts.app')
@section('title', 'Detail Distribusi · ' . $pengajuan->nomor_ajuan)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('admin.distribusi.index') }}" class="text-xs font-semibold text-primary-700 hover:text-primary-800 flex items-center gap-1 mb-2">
            ← Kembali ke Antrean Distribusi
        </a>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-display font-extrabold text-primary-950">
                Pemeriksaan & Distribusi: {{ $pengajuan->nomor_ajuan }}
            </h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                {{ $pengajuan->status === 'disetujui_dan_ditandatangani' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                {{ in_array($pengajuan->status, ['ditolak_tier1', 'ditolak_final']) ? 'bg-rose-50 text-rose-800 border border-rose-200' : '' }}
                {{ $pengajuan->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                {{ $pengajuan->status === 'selesai_ditolak' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                {{ $pengajuan->labelStatus() }}
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            Layanan: <strong>{{ ucfirst(str_replace('_', ' ', $pengajuan->jenis_layanan)) }}</strong> · Diajukan pada {{ optional($pengajuan->tanggal_ajuan)->format('d F Y') }}
        </p>
    </div>

    @if($pengajuan->isDitandatangani())
        <div class="flex items-center gap-2">
            <a href="{{ route('pengajuan.dokumen', $pengajuan) }}" target="_blank"
               class="px-4 py-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Pratinjau Dokumen SK / Izin PDF
            </a>
        </div>
    @endif
</div>

<div class="grid lg:grid-cols-12 gap-6">
    <!-- Kolom Kiri: Ringkasan Pengajuan, Berkas & Jejak Riwayat -->
    <div class="lg:col-span-7 space-y-6">
        <!-- Identitas Pemohon -->
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-3">Data Pemohon</h2>
            <dl class="grid sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <dt class="text-slate-400">Nama Pegawai</dt>
                    <dd class="font-bold text-slate-800 mt-0.5 text-sm">{{ $pengajuan->pegawai->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">NIP</dt>
                    <dd class="font-mono font-semibold text-slate-800 mt-0.5">{{ $pengajuan->pegawai->nip ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Jabatan</dt>
                    <dd class="font-medium text-slate-800 mt-0.5">{{ $pengajuan->pegawai->jabatan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Unit Kerja</dt>
                    <dd class="font-medium text-slate-800 mt-0.5">{{ $pengajuan->pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Berkas Persyaratan Terlampir -->
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-3">Dokumen Persyaratan Pemohon</h2>
            <ul class="divide-y divide-slate-100 text-xs">
                @forelse($pengajuan->berkas as $b)
                <li class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $b->nama_file }}</p>
                        <p class="text-[11px] text-slate-400">Kategori: {{ $b->kategori }} · Versi {{ $b->versi }}</p>
                    </div>
                    <a href="{{ route('berkas.download', $b) }}" class="text-primary-600 hover:text-primary-800 font-bold">
                        Unduh
                    </a>
                </li>
                @empty
                <li class="py-4 text-center text-slate-400">Tidak ada berkas terlampir.</li>
                @endforelse
            </ul>
        </div>

        <!-- Jejak Riwayat Alur Verifikasi -->
        <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-4">Jejak Alur Proses Pejabat</h2>
            <div class="space-y-4">
                @forelse($pengajuan->riwayat as $r)
                <div class="flex items-start gap-3 text-xs border-l-2 border-slate-200 pl-4 relative">
                    <span class="w-2.5 h-2.5 rounded-full {{ $r->keputusan === 'setuju' || $r->keputusan === 'teruskan' ? 'bg-emerald-500' : 'bg-rose-500' }} absolute -left-[5px] top-1"></span>
                    <div>
                        <span class="font-bold text-slate-800 block">
                            {{ ucfirst(str_replace('_', ' ', $r->tahap)) }}: 
                            <span class="{{ $r->keputusan === 'setuju' || $r->keputusan === 'teruskan' ? 'text-emerald-700' : 'text-rose-700' }} uppercase">
                                {{ $r->keputusan }}
                            </span>
                        </span>
                        <p class="text-slate-500 text-[11px] mt-0.5">
                            Oleh: <strong>{{ $r->pejabat->nama ?? 'Sistem' }}</strong> ({{ $r->pejabat->jabatan ?? '-' }}) · 
                            {{ optional($r->waktu)->format('d/m/Y H:i') }} WITA
                        </p>
                        @if($r->catatan)
                            <p class="p-2.5 bg-slate-50 rounded-lg text-slate-700 mt-1 italic border border-slate-100">
                                "{{ $r->catatan }}"
                            </p>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400">Belum ada riwayat tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Sertifikat Digital & Tindakan Distribusi Super Admin -->
    <div class="lg:col-span-5 space-y-6">
        <!-- Card Sertifikat Tanda Tangan Digital (Jika Disetujui Camat/Lurah) -->
        @if($pengajuan->isDitandatangani())
        <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-card border-t-4 border-t-emerald-600">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <h3 class="font-display font-bold text-base text-primary-950">Sertifikat Tanda Tangan Digital</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Hasil pengesahan resmi oleh Camat/Lurah menggunakan autentikasi kriptografis akun pejabat.</p>

            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2.5 text-xs mb-4">
                <div>
                    <span class="text-[11px] text-emerald-700 block">Pejabat Penandatangan:</span>
                    <span class="font-bold text-emerald-950 text-sm">{{ $pengajuan->tandaTanganPejabat->nama ?? ($pengajuan->tanda_tangan_payload['penandatangan']['nama'] ?? '-') }}</span>
                    <span class="text-[11px] font-mono text-emerald-800 block">NIP. {{ $pengajuan->tandaTanganPejabat->nip ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-emerald-700 block">Jabatan / Satuan Kerja:</span>
                    <span class="font-semibold text-emerald-900">{{ $pengajuan->tandaTanganPejabat->jabatan ?? 'Camat / Lurah' }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-emerald-700 block">Waktu Penandatanganan:</span>
                    <span class="font-bold text-emerald-950">{{ optional($pengajuan->tanda_tangan_waktu)->timezone('Asia/Makassar')->format('d/m/Y H:i:s') }} WITA</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-900 text-slate-300 text-xs space-y-1 mb-4 shadow-inner">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Token Hash Autentikasi:</span>
                <span class="font-mono text-[10px] break-all text-emerald-400 block">{{ $pengajuan->tanda_tangan_token }}</span>
            </div>

            <a href="{{ route('dokumen.verifikasi', $pengajuan->tanda_tangan_token) }}" target="_blank"
               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 underline">
                Buka Halaman Verifikasi Publik Tanda Tangan →
            </a>
        </div>
        @endif

        <!-- Panel Tindakan Super Admin -->
        <div class="rounded-2xl border border-blue-200 bg-white p-6 shadow-card border-t-4 border-t-blue-600">
            <h3 class="font-display font-bold text-base text-primary-950 mb-2">Tindakan Super Admin</h3>

            @if($pengajuan->status === 'disetujui_dan_ditandatangani')
                <p class="text-xs text-slate-600 mb-4">
                    Pengajuan telah sah ditandatangani oleh Camat/Lurah. Lakukan pemeriksaan administratif dan distribusikan dokumen resmi ke Pegawai/Staf agar pemohon dapat melihat dan mengunduhnya.
                </p>

                <!-- Form Distribusikan Hasil -->
                <form method="POST" action="{{ route('admin.distribusi.kirim', $pengajuan) }}" class="space-y-4 mb-6 pb-6 border-b border-slate-100">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Catatan Distribusi (Opsional)
                        </label>
                        <textarea name="catatan" rows="2" placeholder="Contoh: SK telah diverifikasi dan siap diunduh oleh pegawai yang bersangkutan..." 
                                  class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Distribusikan Dokumen Resmi ke Pegawai (Selesai)
                    </button>
                </form>

                <!-- Form Prosedur Koreksi Administratif -->
                <div class="pt-2">
                    <p class="text-xs text-slate-500 mb-2">
                        <strong>Koreksi Administratif:</strong> Jika terdapat kekeliruan administrasi pada hasil yang telah ditandatangani, kembalikan melalui prosedur resmi (tidak boleh mengubah dokumen final secara langsung).
                    </p>
                    <form method="POST" action="{{ route('admin.distribusi.koreksi', $pengajuan) }}" 
                          onsubmit="return confirm('Kembalikan pengajuan ini untuk koreksi administratif? Tanda tangan digital saat ini akan dicabut hingga berkas diperbaiki.')"
                          class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-rose-700 mb-1">
                                Alasan Koreksi Administratif (Wajib)
                            </label>
                            <textarea name="catatan_koreksi" rows="2" required placeholder="Tuliskan kekeliruan administratif yang perlu diperbaiki..." 
                                      class="w-full text-xs rounded-xl border border-rose-200 px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full py-2 px-3 rounded-xl border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs transition">
                            Kembalikan untuk Koreksi Administratif
                        </button>
                    </form>
                </div>

            @elseif(in_array($pengajuan->status, ['ditolak_tier1', 'ditolak_final']))
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 mb-4">
                    <span class="font-bold block mb-1">Pengajuan Ditolak oleh Pejabat</span>
                    <p class="text-rose-800">Alasan: "{{ $pengajuan->catatan }}"</p>
                </div>
                <p class="text-xs text-slate-600 mb-4">
                    Sampaikan hasil keputusan penolakan pejabat ini secara resmi kepada Pegawai/Staf tanpa mengubah substansi keputusan.
                </p>
                <form method="POST" action="{{ route('admin.distribusi.sampaikan_penolakan', $pengajuan) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Catatan Penyampaian Tambahan (Opsional)
                        </label>
                        <textarea name="catatan" rows="2" placeholder="Tuliskan catatan penyampaian hasil penolakan ke pegawai..." 
                                  class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                        Sampaikan Hasil Penolakan ke Pegawai
                    </button>
                </form>

            @elseif($pengajuan->isSelesai())
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1.5">
                    <div class="flex items-center gap-1.5 font-bold text-emerald-800">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Pengajuan Selesai & Telah Didistribusikan
                    </div>
                    <p>Hasil didistribusikan oleh <strong>{{ $pengajuan->distribusiAdmin->nama ?? 'Super Admin' }}</strong> pada {{ optional($pengajuan->distribusi_waktu)->format('d/m/Y H:i') }} WITA.</p>
                    @if($pengajuan->distribusi_catatan)
                        <p class="italic text-emerald-800">"{{ $pengajuan->distribusi_catatan }}"</p>
                    @endif
                </div>

            @elseif($pengajuan->status === 'selesai_ditolak')
                <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-700 space-y-1">
                    <span class="font-bold block text-slate-800">Hasil Penolakan Telah Disampaikan</span>
                    <p>Keputusan penolakan telah diteruskan ke pegawai pada {{ optional($pengajuan->distribusi_waktu)->format('d/m/Y H:i') }} WITA.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
