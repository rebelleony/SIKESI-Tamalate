@extends('layouts.app')
@section('title', 'Catat Surat Keluar · Kecamatan Tamalate')

@section('content')
<div class="mb-6">
    <a href="{{ route('surat-keluar.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Buku Agenda Surat Keluar</a>
    <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-2">Catat Surat Keluar & Generator Nomor Otomatis</h1>
    <p class="text-sm text-slate-500">Nomor surat akan digenerate otomatis mengikuti standar Permendagri No. 83 Tahun 2022.</p>
</div>

<form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data" class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-4 max-w-3xl">
    @csrf

    <!-- Live Preview Nomor Otomatis -->
    <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-blue-700">Pratinjau Nomor Surat Otomatis</div>
            <div id="previewNomor" class="font-mono text-lg font-extrabold text-primary-900 mt-0.5">
                —
            </div>
        </div>
        <div class="text-xs text-slate-600">
            No. Urut Tahun Ini: <span id="previewUrut" class="font-mono font-bold text-primary-700">#{{ $nextNoUrut }}</span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Klasifikasi Arsip <span class="text-rose-600">*</span></label>
            <select name="kode_klasifikasi_id" id="kode_klasifikasi_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
                @foreach($klasifikasiList as $k)
                    <option value="{{ $k->id }}" data-kode="{{ $k->kode }}" @selected((int) old('kode_klasifikasi_id') === $k->id)>{{ $k->kode }} — {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-600">*</span></label>
            <input type="date" name="tgl_surat" id="tgl_surat" value="{{ old('tgl_surat', now()->toDateString()) }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Instansi Tujuan <span class="text-rose-600">*</span></label>
            <input name="instansi_tujuan" value="{{ old('instansi_tujuan') }}" required placeholder="mis. BKPSDM Kota Makassar" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Penanggung Jawab <span class="text-rose-600">*</span></label>
            <input name="penanggung_jawab" value="{{ old('penanggung_jawab', 'Camat Tamalate') }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Perihal <span class="text-rose-600">*</span></label>
        <input name="perihal" value="{{ old('perihal') }}" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan</label>
        <textarea name="keterangan" rows="2" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-primary-500 focus:outline-none">{{ old('keterangan') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">File Naskah Dinas (PDF/JPG/PNG, opsional)</label>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary-700 hover:file:bg-blue-100">
    </div>

    <div class="pt-2">
        <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-card transition">
            Generate Nomor & Simpan Surat Keluar
        </button>
    </div>
</form>

<script>
(function () {
    var klasEl = document.getElementById('kode_klasifikasi_id');
    var tglEl = document.getElementById('tgl_surat');
    var prevEl = document.getElementById('previewNomor');
    var urutEl = document.getElementById('previewUrut');

    function updatePreview() {
        var params = new URLSearchParams({
            kode_klasifikasi_id: klasEl.value,
            tgl_surat: tglEl.value
        });
        fetch("{{ route('surat-keluar.preview') }}?" + params.toString(), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            prevEl.textContent = d.nomor_surat;
            urutEl.textContent = '#' + d.no_urut;
        })
        .catch(function () {});
    }

    klasEl.addEventListener('change', updatePreview);
    tglEl.addEventListener('change', updatePreview);
    updatePreview();
})();
</script>
@endsection

