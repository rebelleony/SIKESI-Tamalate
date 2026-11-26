<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SI Kepegawaian & Presensi Terpadu · Kecamatan Tamalate')</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#1d4ed8',
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        gold: {
                            DEFAULT: '#f59e0b',
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                        },
                        cream: '#fcfbfa',
                        ink: {
                            DEFAULT: '#0f172a',
                            70: '#334155',
                            50: '#64748b',
                            40: '#94a3b8',
                        }
                    },
                    boxShadow: {
                        card: '0 2px 10px -2px rgba(15, 23, 42, 0.06), 0 1px 3px -1px rgba(15, 23, 42, 0.04)',
                        lift: '0 10px 25px -5px rgba(29, 78, 216, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.06)',
                    },
                    borderRadius: {
                        'xl2': '1.25rem',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        .blob {
            position: absolute;
            filter: blur(48px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
            border-radius: 9999px;
        }
        @keyframes pulseSlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.65; transform: scale(1.05); }
        }
        .animate-pulse-slow {
            animation: pulseSlow 7s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

@auth
    <!-- TOP DECORATIVE STRIPE (Style Turikale) -->
    <div class="h-1.5 w-full bg-gradient-to-r from-primary-900 via-blue-600 to-gold shrink-0"></div>

    <!-- STICKY TOP HEADER & TAB BAR -->
    <header class="sticky top-0 z-40 border-b border-blue-100 bg-white/95 backdrop-blur-md shadow-sm transition-all duration-200">
        <!-- Main Top Bar -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Left: Branding with Emblem -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group shrink-0">
                    <!-- Emblem / Logo Kecamatan Tamalate -->
                    <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-blue-700 via-blue-800 to-primary-950 p-2 shadow-sm border border-blue-200/50 flex items-center justify-center text-white shrink-0 group-hover:scale-105 transition-transform duration-200">
                        <svg class="h-7 w-7 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Shield + Pinisi Motif -->
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="rgba(245, 158, 11, 0.15)"/>
                            <path d="M12 7v7"/>
                            <path d="M12 9l5 5H7l5-5z" fill="currentColor"/>
                            <path d="M6 18h12"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <div class="flex items-center gap-2">
                            <span class="font-display text-base sm:text-lg font-extrabold text-primary-900 tracking-tight group-hover:text-primary-700 transition">
                                Kecamatan Tamalate
                            </span>
                            <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                                Kota Makassar
                            </span>
                        </div>
                        <span class="block text-xs font-medium text-slate-500">
                            SI Kepegawaian & Presensi Terpadu
                        </span>
                    </div>
                </a>

                <!-- Center & Right: Navigation Tabs (Desktop) -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-1.5" aria-label="Navigasi Utama">
                    @php
                        $isDashboard = request()->routeIs('dashboard');
                        $isPengajuan = request()->routeIs('pengajuan.*');
                        $isKepegawaian = request()->routeIs('kepegawaian.*');
                        $isPresensi = request()->routeIs('presensi.*');
                        $isSurat = request()->routeIs('surat.*') || request()->routeIs('surat-keluar.*') || request()->routeIs('surat-mandiri.*') || request()->routeIs('template-surat.*');
                        $isArsip = request()->routeIs('arsip.*');
                        $isLaporan = request()->routeIs('laporan.*');
                        $isAdmin = request()->routeIs('admin.master') || (request()->routeIs('admin.*') && !request()->routeIs('admin.distribusi.*'));
                        $isDistribusi = request()->routeIs('admin.distribusi.*');
                        $isProfil = request()->routeIs('profil.*');
                    @endphp

                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isDashboard ? 'bg-primary-50 text-primary-700 font-bold shadow-xs border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Pegawai: Pengajuan Layanan -->
                    @if(auth()->user()->hasRole('pegawai') && auth()->user()->berhakLayanan())
                        <div class="relative group">
                            <a href="{{ route('pengajuan.index') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isPengajuan ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Pengajuan</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="absolute left-0 top-full mt-1 w-48 bg-white border border-blue-100 rounded-xl shadow-lift py-1.5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <a href="{{ route('pengajuan.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-primary-700">Daftar Pengajuan Saya</a>
                                <a href="{{ route('pengajuan.create') }}" class="block px-4 py-2 text-xs font-semibold text-primary-700 bg-blue-50/50 hover:bg-blue-100">
                                    Buat Pengajuan Baru
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Layanan Kepegawaian (Cuti, KGB, Pangkat, Pensiun) -->
                    @if(!auth()->user()->hasRole('pegawai') || auth()->user()->berhakLayanan())
                        <a href="{{ route('kepegawaian.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isKepegawaian ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Kepegawaian</span>
                        </a>
                    @endif

                    <!-- Presensi Apel -->
                    <a href="{{ route('presensi.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isPresensi ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Presensi Apel</span>
                    </a>

                    <!-- Persuratan & Arsip Dinamis -->
                    @if(auth()->user()->hasRole('operator','verifikator_pimpinan','pimpinan','super_admin'))
                        <div class="relative group">
                            <a href="{{ route('surat.index') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isSurat ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Persuratan</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="absolute left-0 top-full mt-1 w-56 bg-white border border-blue-100 rounded-xl shadow-lift py-1.5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <a href="{{ route('surat.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-primary-700">Buku Agenda Surat Masuk</a>
                                <a href="{{ route('surat-keluar.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-primary-700">Buku Agenda Surat Keluar</a>
                                <a href="{{ route('surat-mandiri.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-primary-700">Antrean Surat Mandiri</a>
                                <div class="my-1 border-t border-slate-100"></div>
                                <a href="{{ route('surat-mandiri.portal') }}" target="_blank" class="block px-4 py-2 text-xs font-semibold text-primary-700 hover:bg-blue-50">Portal Layanan Mandiri ↗</a>
                                <a href="{{ route('surat-mandiri.tracking') }}" target="_blank" class="block px-4 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50">Tracking Surat Publik ↗</a>
                                <a href="{{ route('template-surat.index') }}" class="block px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Contoh &amp; Template Surat</a>
                            </div>
                        </div>

                        <a href="{{ route('arsip.index') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isArsip ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>Arsip Dokumen</span>
                        </a>
                    @elseif(auth()->user()->hasRole('pegawai'))
                        <a href="{{ route('surat-mandiri.portal') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-primary-700 hover:bg-slate-100/80 transition-all duration-150">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Surat Mandiri</span>
                        </a>
                        <a href="{{ route('template-surat.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ request()->routeIs('template-surat.*') ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <span>Template Surat</span>
                        </a>
                    @endif

                    <!-- Laporan & Rekap -->
                    @if(auth()->user()->hasRole('operator','super_admin'))
                        <a href="{{ route('laporan.index') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isLaporan ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Laporan</span>
                        </a>
                    @endif

                    <!-- Distribusi Hasil (Super Admin) -->
                    @if(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('admin.distribusi.index') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isDistribusi ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Distribusi Hasil</span>
                        </a>

                        <a href="{{ route('admin.master') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 {{ $isAdmin ? 'bg-primary-50 text-primary-700 font-bold border border-blue-200/80' : 'text-slate-600 hover:text-primary-700 hover:bg-slate-100/80' }}">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Data Master</span>
                        </a>
                    @endif
                </nav>

                <!-- Right Utilities: Role Badge + User Profile & Logout -->
                <div class="hidden sm:flex items-center gap-3">
                    <!-- Role Pill -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200/80 shadow-xs">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ auth()->user()->role->nama_role ?? 'Pengguna' }}
                    </span>

                    <!-- Profile Pill -->
                    <a href="{{ route('profil.show') }}" 
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-primary-300 hover:bg-primary-50/50 transition-all text-xs font-semibold text-slate-700 {{ $isProfil ? 'ring-2 ring-primary-500 bg-primary-50 text-primary-800' : '' }}">
                        <div class="h-7 w-7 rounded-full bg-primary-700 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                            {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                        </div>
                        <div class="text-left hidden md:block">
                            <span class="block truncate max-w-[110px]">{{ auth()->user()->nama }}</span>
                        </div>
                    </a>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" 
                                title="Keluar dari akun"
                                class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 text-xs font-semibold transition">
                            <svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" 
                        onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" 
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-600 hover:text-primary-700 hover:bg-blue-50 focus:outline-none"
                        aria-label="Menu Utama">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-blue-100 bg-white/95 backdrop-blur px-4 pt-3 pb-5 space-y-1">
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-primary-700 text-white flex items-center justify-center font-bold text-xs uppercase">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-800">{{ auth()->user()->nama }}</div>
                        <div class="text-[11px] text-amber-700 font-semibold">{{ auth()->user()->role->nama_role ?? '' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs text-red-600 hover:underline font-semibold">Keluar</button>
                </form>
            </div>

            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isDashboard ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Dashboard</a>
            @if(auth()->user()->hasRole('pegawai') && auth()->user()->berhakLayanan())
                <a href="{{ route('pengajuan.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isPengajuan ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Pengajuan Saya</a>
                <a href="{{ route('pengajuan.create') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-primary-700 hover:bg-blue-50">+ Buat Pengajuan Baru</a>
            @endif
            <a href="{{ route('presensi.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isPresensi ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Presensi Apel</a>
            <a href="{{ route('template-surat.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('template-surat.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Template Surat</a>
            @if(auth()->user()->hasRole('operator','pimpinan','super_admin'))
                <a href="{{ route('surat.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isSurat ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Surat Masuk</a>
                <a href="{{ route('arsip.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isArsip ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Arsip Dokumen</a>
            @endif
            @if(auth()->user()->hasRole('operator','super_admin'))
                <a href="{{ route('laporan.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isLaporan ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Laporan & Rekap</a>
            @endif
            @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('admin.distribusi.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isDistribusi ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Distribusi Hasil</a>
                <a href="{{ route('admin.master') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isAdmin ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Data Master</a>
            @endif
            <a href="{{ route('profil.show') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ $isProfil ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-50' }}">Profil Saya</a>
        </div>
    </header>

    <!-- CIVIC AMBIENT HERO BANNER (Style Turikale) -->
    <div class="relative bg-gradient-to-r from-blue-900 via-primary-800 to-primary-950 text-white overflow-hidden py-6 border-b border-blue-950 shadow-inner">
        <!-- Floating Ambient Blobs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="blob -left-12 -top-12 h-64 w-64 bg-blue-500/25 blur-3xl animate-pulse-slow"></div>
            <div class="blob right-10 top-0 h-56 w-56 bg-amber-400/15 blur-3xl" style="animation-delay: 3s"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-600/60 border border-blue-400/30 px-3 py-0.5 text-xs font-bold uppercase tracking-wider text-amber-300 backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
                        Portal Resmi Kepegawaian & Presensi
                    </div>
                    <h1 class="mt-2 font-display text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                        Kecamatan Tamalate, Kota Makassar
                    </h1>
                    <p class="text-xs sm:text-sm text-blue-100/80 mt-1 max-w-2xl">
                        Pelayanan administrasi kepegawaian mandiri, verifikasi berjenjang SOP, dan presensi apel terintegrasi.
                    </p>
                </div>

                <!-- Quick Badge / Time widget -->
                <div class="hidden sm:flex items-center gap-3">
                    <div class="rounded-xl border border-white/15 bg-white/10 backdrop-blur px-4 py-2.5 text-right">
                        <div class="text-[11px] font-semibold text-amber-300 uppercase tracking-wider">Zona Waktu WITA</div>
                        <div class="text-sm font-bold text-white tracking-wide" id="liveClock">{{ now()->timezone('Asia/Makassar')->format('d M Y · H:i') }} WITA</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN BODY CONTAINER -->
    <main class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Success Notification -->
        @if(session('success'))
            <div class="mb-6 flex items-start gap-3 p-4 rounded-xl2 bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm animate-[fadeIn_0.3s]">
                <div class="h-6 w-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Flash Errors Notification -->
        @if(isset($errors) && $errors->any())
            <div class="mb-6 p-4 rounded-xl2 bg-rose-50 border border-rose-200 text-rose-900 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-bold text-sm text-rose-800">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Terjadi Kesalahan:
                </div>
                <ul class="list-disc pl-7 text-xs sm:text-sm space-y-1 text-rose-700">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Dynamic Content -->
        @yield('content')
    </main>

    <!-- CIVIC FOOTER (Style Kelurahan Turikale) -->
    <footer class="mt-auto bg-slate-900 text-white border-t border-slate-800">
        <!-- Top decorative stripe -->
        <div class="h-1 w-full bg-gradient-to-r from-primary-800 via-blue-500 to-gold"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Col 1: Identity -->
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-700 flex items-center justify-center text-amber-400 font-bold border border-blue-400/40">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-display font-extrabold text-base text-white">Kecamatan Tamalate</div>
                            <div class="text-xs text-blue-300">Pemerintah Kota Makassar · Sulawesi Selatan</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm mt-3">
                        Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu bagi seluruh aparatur sipil dan PPPK di wilayah Kecamatan Tamalate.
                    </p>
                </div>

                <!-- Col 2: Kantor & Pelayanan -->
                <div>
                    <h2 class="mb-3 font-display text-xs font-bold uppercase tracking-wider text-amber-400">Kontak & Pelayanan</h2>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jl. Danau Tanjung Bunga No. 1, Kel. Maccini Sombala, Kec. Tamalate, Kota Makassar, Sulawesi Selatan 90224</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Senin–Kamis 07.30–16.00 WITA · Jumat 07.30–16.30 WITA</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Tautan Penting -->
                <div>
                    <h2 class="mb-3 font-display text-xs font-bold uppercase tracking-wider text-amber-400">Tautan Terkait</h2>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li><a href="https://makassarkota.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300 transition flex items-center gap-1.5">Portal Resmi Pemkot Makassar <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a></li>
                        <li><a href="https://bkpsdmd.makassarkota.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300 transition flex items-center gap-1.5">BKPSDM Kota Makassar <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a></li>
                        <li><a href="https://ppid.makassarkota.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300 transition flex items-center gap-1.5">PPID Kota Makassar <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-800 bg-slate-950/80 py-4 text-center text-xs text-slate-500">
            <div class="mx-auto max-w-7xl px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>© 2026 Pemerintah Kecamatan Tamalate. Semua hak dilindungi.</span>
                <span class="text-slate-400">SI Kepegawaian & Presensi Terpadu · Berdasarkan SOP Layanan Publik</span>
            </div>
        </div>
    </footer>
@else
    @yield('guest')
@endauth

<script>
    // Update live clock
    setInterval(function() {
        var el = document.getElementById('liveClock');
        if (el) {
            var now = new Date();
            var options = { timeZone: 'Asia/Makassar', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            el.innerText = now.toLocaleString('id-ID', options) + ' WITA';
        }
    }, 1000);
</script>

</body>
</html>
