@extends('layouts.app')
@section('title', 'Detail Pengajuan · ' . $p->nomor_pengajuan)

@section('content')
@php
    $u = auth()->user();
    $tahapan = $p->tahapanAlur();
    $keys = array_keys($tahapan);
    $currentStatusKey = in_array($p->status, ['selesai', 'diteruskan_bkpsdm'], true) ? 'final' : $p->status;
    $currentIndex = array_search($currentStatusKey, $keys, true);
    $canProcess = match($p->status) {
        'verifikasi_operator_kelurahan', 'antrean_operator_kecamatan' => $u->hasRole('operator'),
        'approval_lurah', 'approval_camat' => $u->hasRole('pimpinan'),
        'approval_kasubag' => $u->hasRole('verifikator_pimpinan'),
        'approval_sekcam' => $u->hasRole('verifikator_pimpinan', 'pimpinan'),
        default => false,
    };
@endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('kepegawaian.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Layanan Kepegawaian</a>
        <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-1">{{ $p->labelJenis() }}</h1>
        <p class="text-sm text-slate-500">
            <span class="font-mono font-bold text-primary-700">{{ $p->nomor_pengajuan }}</span> · Kode Klasifikasi: <span class="font-mono">{{ $p->kode_klasifikasi }}</span>
        </p>
    </div>

    <div class="flex items-center gap-2">
        @if($p->jenis_layanan === 'cuti')
            <a href="{{ route('kepegawaian.form-cuti', $p) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-card transition">
                Generate PDF Form Cuti BKPSDMD
            </a>
        @endif
    </div>
</div>

<!-- TIMELINE VISUAL: Posisi Berkas di Meja Siapa -->
<div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-5">
        <div>
            <h2 class="font-display font-bold text-base text-primary-950">Timeline Visual Posisi Berkas</h2>
            <p class="text-xs text-slate-500">Jalur SOP: {{ $p->asal_wilayah === 'kelurahan' ? 'Kelurahan → Kecamatan → BKPSDMD' : 'Kecamatan → BKPSDMD' }}</p>
        </div>
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-xs font-bold text-primary-900">
            <span class="h-2.5 w-2.5 rounded-full bg-blue-600 animate-pulse"></span>
            Meja Saat Ini: {{ $p->meja_saat_ini }}
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-{{ count($tahapan) }} gap-3">
        @foreach($tahapan as $k => $label)
            @php
                $idx = array_search($k, $keys, true);
                $isDone = $currentIndex !== false && $idx < $currentIndex;
                $isActive = $currentIndex !== false && $idx === $currentIndex;
                if ($k === 'final' && in_array($p->status, ['selesai', 'diteruskan_bkpsdm'], true)) {
                    $isDone = true;
                    $isActive = false;
                }
            @endphp
            <div class="rounded-xl border p-3 transition {{ $isDone ? 'border-emerald-300 bg-emerald-50' : ($isActive ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 bg-slate-50/70') }}">
                <div class="text-[10px] font-bold uppercase tracking-wider {{ $isDone ? 'text-emerald-700' : ($isActive ? 'text-blue-700' : 'text-slate-400') }}">
                    {{ $isDone ? '✓ Selesai' : ($isActive ? '● Sedang Diproses' : 'Menunggu') }}
                </div>
                <div class="text-xs font-bold mt-1 {{ $isDone ? 'text-emerald-950' : ($isActive ? 'text-blue-950' : 'text-slate-500') }}">
                    {{ $label }}
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Data Pengajuan & Lampiran -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-4 border-b border-slate-100 pb-2">Informasi Pemohon & Layanan</h2>
            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Nama Pegawai</dt> <dd class="font-semibold text-slate-900">{{ $p->pegawai->nama ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">NIP</dt> <dd class="font-mono font-semibold text-slate-900">{{ $p->pegawai->nip ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Unit Kerja</dt> <dd class="font-semibold text-slate-900">{{ $p->unitKerja->nama ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Status Saat Ini</dt> <dd class="font-bold text-primary-700">{{ $p->labelStatus() }}</dd></div>
                @foreach($p->data_layanan ?? [] as $key => $val)
                    <div>
                        <dt class="text-xs text-slate-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="font-medium text-slate-800">{{ is_array($val) ? json_encode($val) : $val }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <!-- Lampiran Berkas Digital -->
        <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-4 border-b border-slate-100 pb-2">Lampiran Berkas Digital (PDF)</h2>
            <div class="space-y-2.5">
                @forelse($p->lampiran as $lamp)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50/70">
                        <div>
                            <div class="font-semibold text-sm text-slate-900">{{ $lamp->nama_dokumen }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $lamp->nama_file_asli }} ({{ $lamp->ukuran_kb }} KB)
                                · Verifikasi: <span class="font-bold">{{ ucfirst(str_replace('_', ' ', $lamp->status_verifikasi)) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('kepegawaian.lampiran.unduh', $lamp) }}"
                           class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-blue-50 text-primary-700 font-semibold text-xs">
                            Unduh PDF
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada lampiran.</p>
                @endforelse
            </div>
        </div>

        <!-- Checklist Kelengkapan Dokumen (Kenaikan Pangkat & Pensiun) -->
        @if(in_array($p->jenis_layanan, ['kenaikan_pangkat', 'pensiun'], true) && !empty($p->checklist_kelengkapan))
            <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card">
                <h2 class="font-display font-bold text-base text-primary-950 mb-4 border-b border-slate-100 pb-2">Checklist Kelengkapan Dokumen Persyaratan</h2>
                @if($u->hasRole('operator'))
                    <form method="POST" action="{{ route('kepegawaian.checklist', $p) }}" class="space-y-3">
                        @csrf
                        @foreach($p->checklist_kelengkapan as $idx => $chk)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl border border-slate-100">
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                    <input type="checkbox" name="checklist[{{ $idx }}][lengkap]" value="1" @checked(!empty($chk['lengkap'])) class="rounded text-primary-600">
                                    <span>{{ $chk['item'] }}</span>
                                </label>
                                <input type="text" name="checklist[{{ $idx }}][catatan]" value="{{ $chk['catatan'] ?? '' }}" placeholder="Catatan operator..."
                                       class="border border-slate-200 rounded-lg px-2.5 py-1 text-xs">
                            </div>
                        @endforeach
                        <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                            Simpan Checklist Kelengkapan
                        </button>
                    </form>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach($p->checklist_kelengkapan as $chk)
                            <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                                <span>{{ !empty($chk['lengkap']) ? '✓' : '○' }} {{ $chk['item'] }}</span>
                                <span class="text-xs font-bold {{ !empty($chk['lengkap']) ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ !empty($chk['lengkap']) ? 'Lengkap' : 'Belum Dicek' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>

    <!-- Kolom Kanan: Panel Aksi & Log Status -->
    <div class="space-y-6">
        @if($canProcess)
            <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card space-y-4">
                <h2 class="font-display font-bold text-base text-primary-950">Tindakan ({{ $p->meja_saat_ini }})</h2>
                <form method="POST" action="{{ route('kepegawaian.proses', $p) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Catatan / Alasan (wajib jika revisi/tolak)</label>
                        <textarea name="catatan" rows="3" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        @php $isSignStage = in_array($p->status, ['approval_lurah', 'approval_camat'], true); @endphp
                        <button type="submit" name="aksi" value="setuju"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-sm transition">
                            {{ $isSignStage ? '✓ Setujui & Terbitkan Tanda Tangan Elektronik' : '✓ Setujui & Teruskan ke Meja Berikutnya' }}
                        </button>
                        <button type="submit" name="aksi" value="revisi"
                                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-sm transition">
                            ↺ Kembalikan untuk Revisi
                        </button>
                        <button type="submit" name="aksi" value="tolak"
                                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 rounded-xl text-sm transition">
                            ✕ Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if($p->pegawai_id === $u->id && $p->status === 'perlu_revisi')
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 space-y-3">
                <h2 class="font-bold text-sm text-amber-900">Perlu Revisi Berkas</h2>
                <p class="text-xs text-amber-800">{{ $p->catatan_terakhir }}</p>
                <form method="POST" action="{{ route('kepegawaian.revisi', $p) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="lampiran[]" accept=".pdf" multiple class="w-full text-xs">
                    <textarea name="catatan" rows="2" placeholder="Keterangan perbaikan..." class="w-full border border-amber-200 rounded-xl px-3 py-2 text-xs"></textarea>
                    <button class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 rounded-xl text-xs">Kirim Ulang Revisi</button>
                </form>
            </div>
        @endif

        @if(!empty($signatures['camat']) || !empty($signatures['lurah']))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-card space-y-2.5">
                <h2 class="font-display font-bold text-sm text-emerald-950">✓ Tanda Tangan Elektronik Terbit</h2>
                @foreach(['lurah' => 'Lurah', 'camat' => 'Camat Tamalate'] as $slot => $labelPejabat)
                    @if(!empty($signatures[$slot]))
                        <div class="bg-white/90 rounded-xl p-3 border border-emerald-100 text-xs space-y-0.5">
                            <div class="font-bold text-emerald-900">{{ $labelPejabat }}: {{ $signatures[$slot]['nama'] }}</div>
                            <div class="text-slate-600">NIP. {{ $signatures[$slot]['nip'] }} · {{ $signatures[$slot]['waktu_wita'] }}</div>
                            <div class="font-mono text-[10px] text-emerald-800 break-all">Token: {{ $signatures[$slot]['token'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Riwayat Log Status -->
        <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-card">
            <h2 class="font-display font-bold text-base text-primary-950 mb-4 border-b border-slate-100 pb-2">Log Perjalanan Berkas</h2>
            <ol class="relative border-l border-blue-200 ml-2 space-y-4">
                @foreach($p->logs as $log)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-primary-600 border-2 border-white"></div>
                        <div class="text-xs font-bold text-primary-900">{{ $log->meja }}</div>
                        <div class="text-[11px] text-slate-500">
                            {{ $log->aktor->nama ?? '-' }} · {{ optional($log->waktu)->format('d/m/Y H:i') }}
                        </div>
                        @if($log->catatan)
                            <div class="text-xs text-slate-700 mt-1 bg-slate-50 p-2 rounded-lg">{{ $log->catatan }}</div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection

