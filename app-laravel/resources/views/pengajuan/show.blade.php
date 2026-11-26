@extends('layouts.app')
@section('content')
@php $u = auth()->user(); $d = $pengajuan->data_form ?? []; @endphp
<a href="{{ back()->getTargetUrl() }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
<h1 class="text-2xl font-bold mb-1 mt-2">Detail Pengajuan</h1>
<p class="text-sm text-gray-500 mb-4">{{ $pengajuan->nomor_ajuan }} · <span class="font-medium">{{ $pengajuan->labelStatus() }}</span></p>

{{-- Banner Status Khusus & Tombol Dokumen --}}
@if($pengajuan->status === 'selesai')
    <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-emerald-950">Pengajuan Selesai & Dokumen Resmi Telah Didistribusikan</h3>
                <p class="text-xs text-emerald-800 mt-0.5">
                    Hasil pengajuan telah diverifikasi dan diserahkan oleh Super Admin ({{ optional($pengajuan->distribusiAdmin)->nama ?? 'Super Admin' }}) pada {{ optional($pengajuan->distribusi_waktu)->format('d/m/Y H:i') ?? '-' }}.
                </p>
                @if($pengajuan->distribusi_catatan)
                    <p class="text-xs text-emerald-900 mt-1 italic bg-white/70 px-2 py-1 rounded">"{{ $pengajuan->distribusi_catatan }}"</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pengajuan.dokumen', $pengajuan) }}" target="_blank"
               class="inline-flex items-center gap-1.5 bg-emerald-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-emerald-800 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Dokumen
            </a>
            <a href="{{ route('pengajuan.dokumen', ['pengajuan' => $pengajuan, 'unduh' => 1]) }}"
               class="inline-flex items-center gap-1.5 bg-slate-800 text-white text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-slate-900 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </a>
        </div>
    </div>
@elseif($pengajuan->status === 'disetujui_dan_ditandatangani')
    <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-blue-950">Disetujui dan Ditandatangani Digital (Menunggu Distribusi oleh Super Admin)</h3>
                <p class="text-xs text-blue-800 mt-0.5">
                    Pengajuan telah disetujui secara resmi dengan tanda tangan digital Pimpinan. Saat ini berkas berada di Super Admin untuk pemeriksaan akhir kelengkapan administrasi dan penyerahan/distribusi hasil kepada Anda.
                </p>
            </div>
        </div>
        @if($u->hasRole('super_admin'))
            <a href="{{ route('admin.distribusi.show', $pengajuan) }}" 
               class="inline-flex items-center gap-1.5 bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-blue-800 shadow-xs transition shrink-0">
                Periksa & Distribusikan Hasil →
            </a>
        @endif
    </div>
@elseif($pengajuan->status === 'selesai_ditolak')
    <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-rose-950">Pengajuan Ditolak oleh Pejabat & Hasil Telah Disampaikan oleh Super Admin</h3>
                <p class="text-xs text-rose-800 mt-0.5">
                    Keputusan penolakan pejabat telah resmi disampaikan melalui sistem oleh Super Admin.
                </p>
                @if($pengajuan->catatan)
                    <p class="text-xs text-rose-900 mt-1 italic bg-white/70 px-2 py-1 rounded"><strong>Alasan Penolakan:</strong> "{{ $pengajuan->catatan }}"</p>
                @endif
                @if($pengajuan->distribusi_catatan)
                    <p class="text-xs text-rose-900 mt-1 italic bg-white/70 px-2 py-1 rounded"><strong>Catatan Super Admin:</strong> "{{ $pengajuan->distribusi_catatan }}"</p>
                @endif
            </div>
        </div>
    </div>
@elseif(in_array($pengajuan->status, ['ditolak_tier1', 'ditolak_final'], true))
    <div class="mb-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-amber-950">Pengajuan Ditolak Pejabat (Menunggu Penyampaian oleh Super Admin)</h3>
                <p class="text-xs text-amber-800 mt-0.5">
                    Pengajuan ditolak oleh {{ $pengajuan->status === 'ditolak_final' ? 'Pimpinan' : 'Verifikator Pimpinan' }} dengan catatan: "{{ $pengajuan->catatan ?? '-' }}". Berkas diteruskan ke Super Admin untuk ditindaklanjuti dan disampaikan kepada pemohon.
                </p>
            </div>
        </div>
        @if($u->hasRole('super_admin'))
            <a href="{{ route('admin.distribusi.show', $pengajuan) }}" 
               class="inline-flex items-center gap-1.5 bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-amber-800 shadow-xs transition shrink-0">
                Tindak Lanjuti Penolakan →
            </a>
        @endif
    </div>
@endif

<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Data Pemohon</h2>
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Nama:</dt> <dd class="inline">{{ $pengajuan->pegawai->nama }}</dd></div>
            <div><dt class="inline text-gray-500">NIP:</dt> <dd class="inline">{{ $pengajuan->pegawai->nip }}</dd></div>
            <div><dt class="inline text-gray-500">Jabatan:</dt> <dd class="inline">{{ $pengajuan->pegawai->jabatan }}</dd></div>
            <div><dt class="inline text-gray-500">Unit Kerja:</dt> <dd class="inline">{{ optional($pengajuan->pegawai->unitKerja)->nama }}</dd></div>
            <div><dt class="inline text-gray-500">Masa Kerja:</dt> <dd class="inline">{{ $pengajuan->pegawai->masaKerjaTeks() }}</dd></div>
        </dl>

        <h2 class="font-semibold mt-4 mb-2">Rincian Pengajuan</h2>
        @php
            $rincian = match ($pengajuan->jenis_layanan) {
                'cuti' => [
                    'Jenis Cuti' => \App\Models\JenisCuti::find($d['jenis_cuti_id'] ?? null)?->nama ?? '-',
                    'Alasan' => $d['alasan'] ?? '-',
                    'Lama' => ($d['lama_hari'] ?? '-') . ' hari',
                    'Periode' => ($d['tanggal_mulai'] ?? '-') . ' s/d ' . ($d['tanggal_selesai'] ?? '-'),
                    'No. Telepon' => $d['no_telp'] ?? '-',
                ],
                'kgb' => [
                    'Golongan/Ruang' => $d['golongan_ruang'] ?? '-',
                    'TMT Golongan Terakhir' => $d['tmt_golongan_lama'] ?? '-',
                    'TMT KGB Baru' => $d['tanggal_kgb_baru'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                'kenaikan_pangkat' => [
                    'Pangkat/Gol Sekarang' => $d['pangkat_gol_sekarang'] ?? '-',
                    'TMT Pangkat Sekarang' => $d['tmt_pangkat_sekarang'] ?? '-',
                    'Pangkat/Gol Diusulkan' => $d['pangkat_gol_diusulkan'] ?? '-',
                    'TMT Pangkat Diusulkan' => $d['tmt_pangkat_diusulkan'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                'pensiun' => [
                    'Jenis Pensiun' => \App\Support\LayananKepegawaian::JENIS_PENSIUN[$d['jenis_pensiun'] ?? ''] ?? ($d['jenis_pensiun'] ?? '-'),
                    'Tanggal Lahir' => $d['tanggal_lahir'] ?? '-',
                    'Batas Usia Pensiun' => ($d['bup_tahun'] ?? '-') . ' tahun',
                    'TMT Pensiun' => $d['tmt_pensiun'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                default => [],
            };
        @endphp
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Jenis Layanan:</dt> <dd class="inline font-medium">{{ \App\Support\LayananKepegawaian::label($pengajuan->jenis_layanan) }}</dd></div>
            @foreach($rincian as $label => $nilai)
                <div><dt class="inline text-gray-500">{{ $label }}:</dt> <dd class="inline">{{ $nilai ?: '-' }}</dd></div>
            @endforeach
        </dl>
    </div>

    <div class="space-y-4">
        <div class="bg-white p-5 rounded shadow">
            <h2 class="font-semibold mb-3">Berkas Lampiran</h2>
            <ul class="text-sm space-y-1">
                @forelse($pengajuan->berkas as $b)
                    <li>📎 <a href="{{ route('berkas.download', $b) }}" class="text-blue-600 hover:underline">{{ $b->nama_file }}</a></li>
                @empty
                    <li class="text-gray-400">Tidak ada berkas.</li>
                @endforelse
            </ul>
        </div>

        @if($pengajuan->isDitandatangani())
            <div class="bg-gradient-to-br from-emerald-50 via-teal-50/50 to-white p-5 rounded-2xl border border-emerald-200 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white text-xs">✓</span>
                    <h3 class="font-bold text-sm text-emerald-950">Sertifikat Tanda Tangan Digital Pejabat</h3>
                </div>
                <p class="text-xs text-emerald-900 mb-3">
                    Dokumen ini telah disetujui dan ditandatangani secara elektronik oleh pejabat berwenang menggunakan kredensial autentikasi resmi.
                </p>
                <dl class="text-xs space-y-1.5 bg-white/80 p-3 rounded-xl border border-emerald-100">
                    <div>
                        <dt class="text-slate-500 font-medium">Penandatangan:</dt>
                        <dd class="font-bold text-slate-800">{{ optional($pengajuan->tandaTanganPejabat)->nama ?? 'Pejabat Berwenang' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">NIP / Jabatan:</dt>
                        <dd class="text-slate-700">{{ optional($pengajuan->tandaTanganPejabat)->nip ?? '-' }} · {{ optional($pengajuan->tandaTanganPejabat)->jabatan ?? 'Camat / Lurah' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Waktu Penandatanganan:</dt>
                        <dd class="font-mono text-slate-700">{{ optional($pengajuan->tanda_tangan_waktu)->format('d/m/Y H:i:s T') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Metode & Keamanan:</dt>
                        <dd class="text-slate-700 font-mono text-[11px]">HMAC-SHA256 Auth Fingerprint</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Kode Integritas / Token:</dt>
                        <dd class="font-mono text-[11px] text-emerald-900 break-all select-all bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                            {{ $pengajuan->tanda_tangan_token }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-3 text-right">
                    <a href="{{ route('dokumen.verifikasi', $pengajuan->tanda_tangan_token) }}" target="_blank"
                       class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900 hover:underline">
                        Verifikasi Keaslian Kriptografis →
                    </a>
                </div>
            </div>
        @endif

        <div class="bg-white p-5 rounded shadow">
            <h2 class="font-semibold mb-3">Riwayat Proses</h2>
            <ol class="text-sm space-y-2 border-l-2 border-gray-200 pl-4">
                @forelse($pengajuan->riwayat as $r)
                    <li>
                        <div class="font-medium">{{ ucfirst($r->tahap) }} — {{ strtoupper($r->keputusan) }}</div>
                        <div class="text-gray-500">{{ optional($r->pejabat)->nama }} · {{ $r->waktu->format('d/m/Y H:i') }}</div>
                        @if($r->catatan)<div class="text-gray-600 italic">"{{ $r->catatan }}"</div>@endif
                    </li>
                @empty
                    <li class="text-gray-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</div>

{{-- Aksi sesuai peran & alur persetujuan 5 role --}}

{{-- 1. Verifikator: Verifikasi Berkas (Teruskan ke Verifikator Pimpinan atau Kembalikan untuk Revisi) --}}
@if($u->hasRole('operator') && $pengajuan->status === 'menunggu_verifikasi')
<form method="POST" action="{{ route('approval.verifikasi', $pengajuan) }}" class="bg-white p-6 rounded-2xl border border-blue-100 shadow-card mt-5 max-w-2xl border-l-4 border-l-blue-600">
    @csrf
    <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
        <h2 class="font-display font-bold text-base text-primary-950">Tahap 1: Verifikasi Kelengkapan Berkas (Verifikator)</h2>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Periksa seluruh dokumen persyaratan pemohon. Teruskan berkas yang valid ke Verifikator Pimpinan atau kembalikan kepada pegawai dengan catatan revisi.
    </p>
    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Catatan / Rincian Revisi (Wajib jika dikembalikan)</label>
    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan verifikasi atau berkas yang belum lengkap..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
    <div class="flex gap-2 flex-wrap">
        <button name="keputusan" value="teruskan" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            ✓ Valid & Teruskan ke Verifikator Pimpinan
        </button>
        <button name="keputusan" value="revisi" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            ↩ Kembalikan untuk Revisi
        </button>
    </div>
</form>
@endif

{{-- 2. Pegawai: Unggah Ulang Berkas Revisi --}}
@if($u->hasRole('pegawai') && $pengajuan->pegawai_id === $u->id && $pengajuan->status === 'perlu_revisi')
<form method="POST" action="{{ route('pengajuan.revisi', $pengajuan) }}" enctype="multipart/form-data" class="bg-amber-50/80 border border-amber-200 p-6 rounded-2xl shadow-card mt-5 max-w-2xl border-l-4 border-l-amber-500">
    @csrf
    <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
        <h2 class="font-display font-bold text-base text-amber-950">Perbaiki & Unggah Ulang Berkas Revisi</h2>
    </div>
    <div class="p-3 rounded-xl bg-white/80 border border-amber-200 text-xs text-amber-900 mb-3">
        <span class="font-bold block mb-0.5">Catatan Verifikator:</span>
        <p class="italic">"{{ $pengajuan->catatan ?? 'Harap lengkapi berkas persyaratan sesuai ketentuan.' }}"</p>
    </div>
    <label class="block text-xs font-bold uppercase tracking-wider text-amber-900 mb-1">Pilih Berkas Pengganti / Tambahan (PDF/JPG/PNG, maks 3MB)</label>
    <input type="file" name="berkas[]" multiple required class="w-full border border-amber-300 rounded-xl px-3 py-2 text-xs mb-3 bg-white focus:outline-none">
    <label class="block text-xs font-bold uppercase tracking-wider text-amber-900 mb-1">Catatan Pemohon (Opsional)</label>
    <textarea name="catatan" rows="2" placeholder="Jelaskan perbaikan berkas yang telah Anda perbarui..." class="w-full border border-amber-300 rounded-xl px-3 py-2 text-sm mb-3 bg-white focus:outline-none"></textarea>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Kirim Ulang Berkas Revisi ke Verifikator
    </button>
</form>
@endif

{{-- 3. Kasi / Kasubag: Persetujuan Tingkat Pertama --}}
@if($u->hasRole('verifikator_pimpinan') && $pengajuan->status === 'menunggu_approval_tier1')
<form method="POST" action="{{ route('approval.tier1', $pengajuan) }}" class="bg-white p-6 rounded-2xl border border-blue-100 shadow-card mt-5 max-w-2xl border-l-4 border-l-indigo-600">
    @csrf
    <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
        <h2 class="font-display font-bold text-base text-primary-950">Tahap 2: Persetujuan Tingkat Pertama (Kasi / Kasubag)</h2>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Tinjau kelengkapan berkas yang telah diverifikasi. Setujui untuk meneruskan ke Pimpinan, atau tolak pengajuan (alasan penolakan dicatat dan diteruskan ke Super Admin).
    </p>
    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Catatan / Alasan Penolakan (Wajib jika menolak)</label>
    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan apabila menolak pengajuan..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
    <div class="flex gap-2 flex-wrap">
        <button name="keputusan" value="setuju" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            ✓ Setujui & Teruskan ke Pimpinan
        </button>
        <button name="keputusan" value="tolak" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            ✕ Tolak Pengajuan (Teruskan ke Super Admin)
        </button>
    </div>
</form>
@endif

{{-- 4. Camat / Lurah: Persetujuan Akhir & Tanda Tangan Digital --}}
@if($u->hasRole('pimpinan') && $pengajuan->status === 'menunggu_approval_final')
<form method="POST" action="{{ route('approval.final', $pengajuan) }}" class="bg-white p-6 rounded-2xl border border-blue-100 shadow-card mt-5 max-w-2xl border-l-4 border-l-emerald-600">
    @csrf
    <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
        <h2 class="font-display font-bold text-base text-primary-950">Tahap 3: Persetujuan Akhir & Tanda Tangan Digital (Camat / Lurah)</h2>
    </div>
    <div class="text-xs text-slate-600 mb-3 space-y-1">
        <p>• Berkas ini telah melewati verifikasi dokumen dan persetujuan Verifikator Pimpinan.</p>
        <p>• <strong>Setujui:</strong> Menerbitkan Tanda Tangan Digital resmi atas nama <strong>{{ $u->nama }} ({{ $u->nip }})</strong>. Status akan menjadi <em>Disetujui dan Ditandatangani</em> dan diteruskan ke Super Admin untuk proses administrasi & distribusi hasil.</p>
        <p>• <strong>Tolak:</strong> Mencatat alasan penolakan dan meneruskannya ke Super Admin untuk ditindaklanjuti. Anda tidak mengembalikan pengajuan secara langsung kepada pemohon.</p>
    </div>
    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Catatan / Alasan Penolakan (Wajib jika menolak)</label>
    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan penolakan jika ditolak..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
    <div class="flex gap-2 flex-wrap">
        <button name="keputusan" value="setuju" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            ✓ Setujui & Terbitkan Tanda Tangan Digital
        </button>
        <button name="keputusan" value="tolak" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
            ✕ Tolak Pengajuan (Teruskan ke Super Admin)
        </button>
    </div>
</form>
@endif
@endsection
