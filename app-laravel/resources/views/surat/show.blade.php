@extends('layouts.app')
@section('content')
@php $u = auth()->user(); @endphp
<a href="{{ route('surat.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
<h1 class="text-2xl font-bold mb-1 mt-2">Detail Surat Masuk</h1>
<p class="text-sm text-gray-500 mb-4">{{ $surat->nomor_agenda }} · <span class="font-medium">{{ $surat->labelStatus() }}</span></p>

<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Informasi Surat</h2>
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Nomor Surat:</dt> <dd class="inline">{{ $surat->nomor_surat }}</dd></div>
            <div><dt class="inline text-gray-500">Klasifikasi:</dt> <dd class="inline">{{ $surat->klasifikasi ?? '-' }}</dd></div>
            <div><dt class="inline text-gray-500">Perihal:</dt> <dd class="inline">{{ $surat->perihal }}</dd></div>
            <div><dt class="inline text-gray-500">Asal:</dt> <dd class="inline">{{ $surat->asal_surat ?? '-' }}</dd></div>
            <div><dt class="inline text-gray-500">Tujuan:</dt> <dd class="inline">{{ $surat->tujuan ?? '-' }}</dd></div>
            <div><dt class="inline text-gray-500">Tanggal Surat:</dt> <dd class="inline">{{ optional($surat->tanggal_surat)->format('d/m/Y') }}</dd></div>
            <div><dt class="inline text-gray-500">Diterima:</dt> <dd class="inline">{{ optional($surat->tanggal_terima)->format('d/m/Y') }}</dd></div>
            <div><dt class="inline text-gray-500">Sifat:</dt> <dd class="inline">{{ ucfirst($surat->sifat) }}</dd></div>
            <div><dt class="inline text-gray-500">Dicatat oleh:</dt> <dd class="inline">{{ optional($surat->pencatat)->nama }}</dd></div>
            @if($surat->file_path)
                <div><dt class="inline text-gray-500">Berkas:</dt> <dd class="inline"><a href="{{ route('surat.unduh', $surat) }}" class="text-blue-600 hover:underline">Unduh</a></dd></div>
            @endif
        </dl>

        <h2 class="font-semibold mt-4 mb-2">Lembar Disposisi</h2>
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Catatan awal:</dt> <dd class="inline">{{ $surat->disposisi ?? '-' }}</dd></div>
            <div><dt class="inline text-gray-500">Paraf Sekcam:</dt> <dd class="inline">{{ optional($surat->pemaraf)->nama ?? 'belum' }}{{ $surat->paraf_waktu ? ' ('.$surat->paraf_waktu->format('d/m/Y H:i').')' : '' }}</dd></div>
            <div><dt class="inline text-gray-500">Disposisi Camat:</dt> <dd class="inline">{{ optional($surat->pendisposisi)->nama ?? 'belum' }}{{ $surat->disposisi_waktu ? ' ('.$surat->disposisi_waktu->format('d/m/Y H:i').')' : '' }}</dd></div>
            <div><dt class="inline text-gray-500">Tujuan Disposisi:</dt> <dd class="inline">{{ $surat->tujuan_disposisi ?? '-' }}</dd></div>
            <div><dt class="inline text-gray-500">Didistribusikan:</dt> <dd class="inline">{{ optional($surat->pendistribusi)->nama ?? 'belum' }}{{ $surat->distribusi_waktu ? ' ('.$surat->distribusi_waktu->format('d/m/Y H:i').')' : '' }}</dd></div>
            <div><dt class="inline text-gray-500">Arsip:</dt> <dd class="inline">{{ $surat->arsip ? $surat->arsip->kode_arsip : 'belum diarsipkan' }}</dd></div>
        </dl>
    </div>

    <div class="space-y-4">
        @if($u->hasRole('seksam','super_admin') && $surat->status === 'dicatat')
            <form method="POST" action="{{ route('surat.paraf', $surat) }}" class="bg-white p-5 rounded shadow">
                @csrf
                <h2 class="font-semibold mb-3">Periksa & Paraf (Sekretaris Camat)</h2>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Paraf Lembar Disposisi</button>
            </form>
        @endif

        @if($u->hasRole('camat_lurah','super_admin') && $surat->status === 'diparaf_sekcam')
            <form method="POST" action="{{ route('surat.disposisi', $surat) }}" class="bg-white p-5 rounded shadow">
                @csrf
                <h2 class="font-semibold mb-3">Disposisi & Tanda Tangan (Camat)</h2>
                <label class="block text-sm font-medium mb-1">Tujuan Disposisi</label>
                <textarea name="tujuan_disposisi" rows="2" required placeholder="mis. Kepada Kepala Seksi Pemerintahan untuk ditindaklanjuti" class="w-full border rounded px-3 py-2 mb-3"></textarea>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Disposisikan</button>
            </form>
        @endif

        @if($u->hasRole('verifikator','super_admin') && $surat->status === 'didisposisi')
            <form method="POST" action="{{ route('surat.distribusi', $surat) }}" class="bg-white p-5 rounded shadow">
                @csrf
                <h2 class="font-semibold mb-3">Distribusi Surat</h2>
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tandai Telah Didistribusikan</button>
            </form>
        @endif

        @if($surat->status === 'didistribusikan' && ! $surat->arsip)
            <div class="bg-white p-5 rounded shadow">
                <h2 class="font-semibold mb-2">Langkah Berikutnya</h2>
                <p class="text-sm text-gray-600 mb-3">Surat siap diarsipkan sesuai nomor surat.</p>
                <a href="{{ route('arsip.create') }}" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">Arsipkan Surat</a>
            </div>
        @endif
    </div>
</div>
@endsection
