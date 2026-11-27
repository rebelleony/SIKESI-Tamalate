<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Layanan Surat Mandiri · Kecamatan Tamalate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="h-1.5 w-full bg-gradient-to-r from-blue-900 via-blue-600 to-amber-500"></div>

    <header class="bg-white border-b border-blue-100 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-800 text-amber-400 flex items-center justify-center font-extrabold text-sm">KT</div>
                <div>
                    <div class="font-extrabold text-blue-950 text-sm sm:text-base leading-tight">SIP-TAMALATE · Portal Surat Mandiri</div>
                    <div class="text-xs text-slate-500">Kecamatan Tamalate & 11 Kelurahan · Kota Makassar</div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <a href="{{ route('template-surat.index') }}" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 hover:bg-blue-100 transition">
                    Template Surat
                </a>
                <a href="{{ route('surat-mandiri.tracking') }}" class="px-3 py-2 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100 transition">
                    Lacak Kode Tracking
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg bg-blue-700 text-white hover:bg-blue-800 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">Login Pegawai</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form Pengajuan -->
            <div class="lg:col-span-2 bg-white border border-blue-100 rounded-2xl p-6 shadow-sm">
                @auth
                    @if(auth()->user()->hasRole('pimpinan', 'verifikator_pimpinan'))
                        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="font-bold">Mode Pimpinan ({{ auth()->user()->jabatan ?? auth()->user()->nama }})</div>
                                <p class="mt-0.5 text-amber-800">Anda dapat mengajukan surat resmi (Undangan, Surat Pengantar, atau Permohonan Fasilitasi) melalui portal ini untuk diproses penomoran &amp; pengarsipannya oleh Operator, atau langsung membuat draf dokumen siap cetak (.DOCX/.PDF).</p>
                            </div>
                            <a href="{{ route('template-surat.index', ['jenis' => 'undangan']) }}" class="px-3 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold shrink-0 text-center">
                                Buat Draf Surat ↗
                            </a>
                        </div>
                    @endif
                @endauth

                <div class="mb-5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                        Layanan Kelurahan & Unit Internal
                    </span>
                    <h1 class="text-2xl font-extrabold text-blue-950 mt-2">Ajukan Surat Internal / Pengantar</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Ajukan Surat Pengantar, Undangan, atau Permohonan Fasilitasi. Anda akan memperoleh <strong>Kode Tracking Unik</strong> untuk memantau status surat secara real-time tanpa perlu login berulang kali.
                    </p>
                </div>

                <form method="POST" action="{{ route('surat-mandiri.submit') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Surat <span class="text-rose-600">*</span></label>
                            <select name="jenis_surat" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                                @foreach($jenisSurat as $j)
                                    <option value="{{ $j }}" @selected(old('jenis_surat') === $j)>{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Unit / Kelurahan Asal <span class="text-rose-600">*</span></label>
                            <select name="unit_asal" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                                <option value="">— Pilih Kelurahan / Seksi / Subbag —</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->nama }}" @selected(old('unit_asal') === $u->nama)>{{ ucfirst($u->tipe) }} — {{ $u->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Pemohon <span class="text-rose-600">*</span></label>
                            <input name="nama_pemohon" value="{{ old('nama_pemohon', auth()->user()?->nama) }}" required placeholder="Nama lengkap pegawai/staf" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">NIP / NIK (opsional)</label>
                            <input name="nip_nik" value="{{ old('nip_nik', auth()->user()?->nip) }}" placeholder="18 digit NIP atau NIK" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input name="no_hp" value="{{ old('no_hp', auth()->user()?->no_hp) }}" placeholder="08xxxxxxxxxx" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Perihal Permohonan <span class="text-rose-600">*</span></label>
                            <input name="perihal" value="{{ old('perihal') }}" required placeholder="Ringkasan perihal surat" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan / Detail Kebutuhan</label>
                        <textarea name="keterangan" rows="3" placeholder="Jelaskan detail tujuan, waktu pelaksanaan, atau keterangan lain..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">{{ old('keterangan') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Lampiran Pendukung (PDF/JPG/PNG, maks 3MB)</label>
                        <input type="file" name="file_pendukung" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-3 rounded-xl text-sm shadow transition">
                        Kirim Permohonan & Dapatkan Kode Tracking
                    </button>
                </form>
            </div>

            <!-- Sidebar Quick Tracking -->
            <div class="space-y-4">
                <div class="bg-white border border-amber-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="font-extrabold text-base text-slate-900 mb-2">Sudah Punya Kode Tracking?</h2>
                    <p class="text-xs text-slate-500 mb-4">Masukkan kode tracking (contoh: <span class="font-mono font-bold">TRK-2026-XXXXXX</span>) untuk mengecek posisi surat secara real-time.</p>
                    <form method="GET" action="{{ route('surat-mandiri.tracking') }}" class="space-y-3">
                        <input name="kode" required placeholder="TRK-2026-XXXXXX" class="w-full font-mono uppercase border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none">
                        <button class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl text-sm transition">
                            Lacak Posisi Surat
                        </button>
                    </form>
                </div>

                <div class="bg-blue-950 text-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-sm mb-2">Alur Layanan Mandiri</h3>
                    <ol class="text-xs text-blue-100 space-y-2 list-decimal list-inside">
                        <li>Isi formulir permohonan surat di portal ini.</li>
                        <li>Catat/simpan <strong>Kode Tracking</strong> yang muncul.</li>
                        <li>Operator Kecamatan memverifikasi & memproses draf surat.</li>
                        <li>Pimpinan (Sekcam/Camat) menandatangani naskah dinas.</li>
                        <li>Pantau status real-time hingga <strong>Selesai / Siap Diambil</strong>.</li>
                    </ol>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

