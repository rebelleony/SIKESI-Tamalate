@extends('layouts.app')
@section('title', 'Masuk · SI Kepegawaian & Presensi Terpadu Kecamatan Tamalate')

@section('guest')
<div class="min-h-screen flex flex-col justify-between bg-gradient-to-br from-blue-100/70 via-blue-50/50 to-cream relative overflow-hidden">
    <!-- Top decorative stripe (Turikale style) -->
    <div class="h-1.5 w-full bg-gradient-to-r from-primary-900 via-blue-600 to-gold shrink-0"></div>

    <!-- Floating Ambient Blobs -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="blob -left-16 -top-10 h-80 w-80 bg-blue-300/30 blur-3xl animate-pulse-slow"></div>
        <div class="blob right-0 top-16 h-80 w-80 bg-primary-300/25 blur-3xl" style="animation-delay: 3s"></div>
        <div class="blob bottom-10 left-1/3 h-64 w-64 bg-gold-100/40 blur-3xl" style="animation-delay: 6s"></div>
    </div>

    <!-- Top Mini Brand Header -->
    <header class="relative z-10 border-b border-blue-100/80 bg-white/70 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-700 to-primary-950 p-2 shadow-xs border border-blue-200 flex items-center justify-center text-white shrink-0">
                    <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="rgba(245, 158, 11, 0.15)"/>
                        <path d="M12 7v7"/>
                        <path d="M12 9l5 5H7l5-5z" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <span class="block font-display text-base font-extrabold text-primary-900 tracking-tight leading-tight">
                        Kecamatan Tamalate
                    </span>
                    <span class="block text-[11px] font-semibold text-primary-600">
                        Pemerintah Kota Makassar · Sulawesi Selatan
                    </span>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Aktif & Terintegrasi
                </span>
            </div>
        </div>
    </header>

    <!-- Main Hero & Login Content -->
    <main class="relative z-10 mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 flex-1 flex items-center">
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full my-auto">
            
            <!-- Left Column: Hero Civic Information (Style Turikale) -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="inline-block rounded-full bg-blue-600 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-xs">
                        Portal Resmi Kepegawaian & Presensi
                    </span>
                    <h1 class="mt-4 font-display text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-primary-950">
                        Kecamatan Tamalate, <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-primary-800 to-amber-600">
                            Kota Makassar
                        </span>
                    </h1>
                    <p class="mt-4 text-base text-slate-600 leading-relaxed max-w-2xl">
                        Sistem informasi resmi untuk pelayanan administrasi kepegawaian (Cuti, KGB, Kenaikan Pangkat, Pensiun), pengelolaan agenda surat masuk & disposisi, serta presensi apel terpadu bagi seluruh ASN dan PPPK.
                    </p>
                </div>

                <!-- 4 Highlight Stat Cards (Turikale Style) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="rounded-2xl border border-blue-100 bg-white/90 p-3.5 text-center shadow-card backdrop-blur">
                        <p class="font-display text-2xl font-extrabold text-primary-700">11</p>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Kelurahan</p>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-white/90 p-3.5 text-center shadow-card backdrop-blur">
                        <p class="font-display text-2xl font-extrabold text-amber-600">4+</p>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Layanan SOP</p>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-white/90 p-3.5 text-center shadow-card backdrop-blur">
                        <p class="font-display text-2xl font-extrabold text-blue-600">2 Sesi</p>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Apel Pagi & Sore</p>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-white/90 p-3.5 text-center shadow-card backdrop-blur">
                        <p class="font-display text-2xl font-extrabold text-primary-800">SOP</p>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Verifikasi Berjenjang</p>
                    </div>
                </div>

                <!-- Info Banner -->
                <div class="rounded-2xl border-l-4 border-gold bg-white/95 p-4 shadow-card text-xs text-slate-600 space-y-1">
                    <p class="font-bold text-primary-900 text-sm">Alur Layanan 4 Tahap Berjenjang</p>
                    <p>Pengajuan Mandiri (Pegawai) → Verifikasi Dokumen (Verifikator) → Persetujuan Tingkat Pertama (Kasi/Kasubag) → Persetujuan Akhir & Pengesahan (Camat/Lurah) → Surat / Dokumen Selesai.</p>
                </div>
            </div>

            <!-- Right Column: Login Card -->
            <div class="lg:col-span-5">
                <div class="rounded-2xl border border-blue-100 bg-white/95 p-6 sm:p-8 shadow-lift backdrop-blur">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-primary-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        </div>
                        <div>
                            <h2 class="font-display text-lg font-extrabold text-primary-950">Masuk ke Portal</h2>
                            <p class="text-xs text-slate-500">Gunakan NIP / Username akun Anda</p>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                NIP / Username
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input id="loginUsername" name="login" value="{{ old('login') }}" required autofocus
                                       placeholder="Contoh: admin atau NIP"
                                       class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Kata Sandi
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input id="loginPassword" type="password" name="password" required
                                       placeholder="••••••••"
                                       class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <span>Ingat saya di perangkat ini</span>
                            </label>
                        </div>

                        <button type="submit" 
                                class="w-full rounded-xl bg-gradient-to-r from-blue-700 to-primary-800 text-white font-bold py-3 text-sm shadow-card hover:from-blue-800 hover:to-primary-900 transition-all duration-150 flex items-center justify-center gap-2 group">
                            <span>Masuk ke Akun</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <!-- Quick Demo Role Fillers -->
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14H8a4 4 0 01-.82-7.916 4 4 0 017.64 0A4 4 0 0112 14z"/></svg>
                            Pilih Akun Uji Coba (5 Role Utama):
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-xs">
                            <button type="button" onclick="fillLogin('pegawai')" class="px-2 py-1.5 rounded-lg border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-700 font-semibold transition text-center truncate">
                                1. Pegawai / Staf
                            </button>
                            <button type="button" onclick="fillLogin('operator')" class="px-2 py-1.5 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50 text-slate-700 font-semibold transition text-center truncate">
                                2. Operator
                            </button>
                            <button type="button" onclick="fillLogin('kasi')" class="px-2 py-1.5 rounded-lg border border-slate-200 hover:border-purple-300 hover:bg-purple-50 text-slate-700 font-semibold transition text-center truncate">
                                3. Kasi / Kasubag
                            </button>
                            <button type="button" onclick="fillLogin('camat')" class="px-2 py-1.5 rounded-lg border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-700 font-semibold transition text-center truncate">
                                4. Camat / Lurah
                            </button>
                            <button type="button" onclick="fillLogin('admin')" class="px-2 py-1.5 rounded-lg border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-700 font-semibold transition text-center truncate col-span-2 sm:col-span-1">
                                5. Super Admin
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Bottom Wave SVG Decoration (Turikale Style) -->
    <div class="relative w-full overflow-hidden leading-none shrink-0 pointer-events-none">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-12 text-slate-900 fill-current opacity-90">
            <path d="M0,0 C150,90 350,-40 500,45 C650,130 900,10 1200,30 L1200,120 L0,120 Z"></path>
        </svg>
    </div>

    <!-- Mini Footer -->
    <footer class="bg-slate-900 text-white text-xs py-5 px-4 text-center shrink-0 border-t border-slate-800">
        <div class="mx-auto max-w-7xl flex flex-col sm:flex-row items-center justify-between gap-2 text-slate-400">
            <span>© 2026 Pemerintah Kecamatan Tamalate · Kota Makassar. Semua hak dilindungi.</span>
            <span>Gunakan peramban modern untuk pengalaman optimal.</span>
        </div>
    </footer>
</div>

<script>
    function fillLogin(role) {
        document.getElementById('loginUsername').value = role;
        document.getElementById('loginPassword').value = 'password';
    }
</script>
@endsection
