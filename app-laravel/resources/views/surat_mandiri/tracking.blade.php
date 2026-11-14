<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Surat Mandiri · Kecamatan Tamalate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="h-1.5 w-full bg-gradient-to-r from-blue-900 via-blue-600 to-amber-500"></div>

    <header class="bg-white border-b border-blue-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-800 text-amber-400 flex items-center justify-center font-extrabold text-sm">KT</div>
                <div>
                    <div class="font-extrabold text-blue-950 text-sm sm:text-base">Tracking Surat Mandiri · SIP-TAMALATE</div>
                    <div class="text-xs text-slate-500">Pelacakan Real-Time Tanpa Login Berulang</div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <a href="{{ route('surat-mandiri.portal') }}" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 hover:bg-blue-100 transition">
                    + Ajukan Surat Baru
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg bg-blue-700 text-white hover:bg-blue-800 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900 font-semibold text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search Box -->
        <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-sm">
            <h1 class="text-xl font-extrabold text-blue-950 mb-2">Lacak Status Pengajuan Surat</h1>
            <form method="GET" action="{{ route('surat-mandiri.tracking') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="kode" value="{{ $kode }}" required placeholder="Masukkan Kode Tracking (contoh: TRK-2026-XXXXXX)"
                       class="flex-1 font-mono uppercase rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-3 rounded-xl text-sm transition">
                    Cek Status
                </button>
            </form>
        </div>

        @if($kode !== '' && ! $hasil)
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-center text-rose-800">
                <div class="font-bold text-base">Kode Tracking Tidak Ditemukan</div>
                <p class="text-sm mt-1">Pastikan kode tracking <span class="font-mono font-bold">{{ $kode }}</span> sudah benar.</p>
            </div>
        @endif

        @if($hasil)
            <div class="bg-white border border-blue-100 rounded-2xl p-6 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Kode Tracking</div>
                        <div class="font-mono text-2xl font-extrabold text-blue-800">{{ $hasil->kode_tracking }}</div>
                    </div>
                    <div>
                        @php
                            $badge = match($hasil->status) {
                                'selesai' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'ditolak' => 'bg-rose-50 text-rose-800 border-rose-200',
                                default => 'bg-amber-50 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $badge }}">
                            {{ $hasil->labelStatus() }}
                        </span>
                    </div>
                </div>

                <!-- Timeline Status -->
                @php
                    $steps = [
                        'diajukan' => 'Permohonan Diajukan',
                        'diproses_operator' => 'Diproses Operator',
                        'menunggu_pimpinan' => 'Menunggu TTD Pimpinan',
                        'selesai' => 'Selesai / Siap Diambil',
                    ];
                    $order = ['diajukan' => 1, 'diproses_operator' => 2, 'menunggu_pimpinan' => 3, 'selesai' => 4, 'ditolak' => 0];
                    $cur = $order[$hasil->status] ?? 1;
                @endphp

                @if($hasil->status !== 'ditolak')
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($steps as $k => $label)
                            @php $stepNum = $order[$k]; $done = $cur >= $stepNum; @endphp
                            <div class="rounded-xl border p-3 {{ $done ? 'border-emerald-300 bg-emerald-50/70' : 'border-slate-200 bg-slate-50' }}">
                                <div class="text-[11px] font-bold {{ $done ? 'text-emerald-700' : 'text-slate-400' }}">Tahap {{ $stepNum }}</div>
                                <div class="text-xs font-bold mt-0.5 {{ $done ? 'text-emerald-950' : 'text-slate-500' }}">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                        <strong>Permohonan Ditolak:</strong> {{ $hasil->catatan_admin ?? 'Tidak ada catatan.' }}
                    </div>
                @endif

                <dl class="grid sm:grid-cols-2 gap-4 text-sm border-t border-slate-100 pt-4">
                    <div><dt class="text-xs text-slate-400">Jenis Surat</dt> <dd class="font-semibold">{{ $hasil->jenis_surat }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Unit / Kelurahan Asal</dt> <dd class="font-semibold">{{ $hasil->unit_asal }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Nama Pemohon</dt> <dd class="font-semibold">{{ $hasil->nama_pemohon }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Tanggal Pengajuan</dt> <dd class="font-semibold">{{ $hasil->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-400">Perihal</dt> <dd class="font-semibold">{{ $hasil->perihal }}</dd></div>
                    @if($hasil->suratKeluar)
                        <div class="sm:col-span-2 rounded-xl bg-blue-50 border border-blue-200 p-3">
                            <dt class="text-xs font-bold text-blue-700">Nomor Surat Keluar Resmi Diterbitkan</dt>
                            <dd class="font-mono text-base font-extrabold text-blue-950 mt-0.5">{{ $hasil->suratKeluar->nomor_surat }}</dd>
                        </div>
                    @endif
                    @if($hasil->catatan_admin && $hasil->status !== 'ditolak')
                        <div class="sm:col-span-2"><dt class="text-xs text-slate-400">Catatan Petugas</dt> <dd class="text-slate-700">{{ $hasil->catatan_admin }}</dd></div>
                    @endif
                </dl>
            </div>
        @endif
    </main>
</body>
</html>

