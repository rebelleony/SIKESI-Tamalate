@extends('layouts.app')
@section('title', 'Contoh Form & Template Surat · Kecamatan Tamalate')

@section('content')
    @include('template_surat._content')
@endsection

@section('guest')
    <div class="h-1.5 w-full bg-gradient-to-r from-blue-900 via-blue-600 to-amber-500"></div>
    <header class="bg-white border-b border-blue-100 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-800 text-amber-400 flex items-center justify-center font-extrabold text-sm">KT</div>
                <div>
                    <div class="font-extrabold text-blue-950 text-sm sm:text-base leading-tight">SIP-TAMALATE · Contoh &amp; Template Surat</div>
                    <div class="text-xs text-slate-500">Kecamatan Tamalate · Kota Makassar</div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <a href="{{ route('surat-mandiri.portal') }}" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 hover:bg-blue-100 transition">
                    Portal Surat Mandiri
                </a>
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg bg-blue-700 text-white hover:bg-blue-800 transition">
                    Login Pegawai
                </a>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 flex-1">
        @include('template_surat._content')
    </main>
@endsection
