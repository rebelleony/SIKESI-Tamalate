@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-4">Tambah Arsip Dokumen</h1>

<form method="POST" action="{{ route('arsip.store') }}" class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf

    <div>
        <label class="block text-sm font-medium mb-1">Jenis Dokumen</label>
        <select name="jenis_dokumen" id="jenis_dokumen" required class="w-full border rounded px-3 py-2">
            <option value="surat_masuk" @selected(old('jenis_dokumen', 'surat_masuk') === 'surat_masuk')>Surat Masuk</option>
            <option value="surat_keluar" @selected(old('jenis_dokumen') === 'surat_keluar')>Surat Keluar / Output Pengajuan</option>
            <option value="dokumen_lain" @selected(old('jenis_dokumen') === 'dokumen_lain')>Dokumen Lain</option>
        </select>
    </div>

    <div data-sumber="surat_masuk">
        <label class="block text-sm font-medium mb-1">Pilih Surat Masuk (sudah didistribusikan)</label>
        <select name="surat_masuk_id" class="w-full border rounded px-3 py-2">
            <option value="">— pilih —</option>
            @foreach($suratMasuk as $s)
                <option value="{{ $s->id }}" @selected((int) old('surat_masuk_id') === $s->id)>{{ $s->nomor_agenda }} — {{ Str::limit($s->perihal, 40) }}</option>
            @endforeach
        </select>
    </div>

    <div data-sumber="surat_keluar" class="hidden">
        <label class="block text-sm font-medium mb-1">Pilih Pengajuan Selesai</label>
        <select name="pengajuan_id" class="w-full border rounded px-3 py-2">
            <option value="">— pilih —</option>
            @foreach($pengajuan as $p)
                <option value="{{ $p->id }}" @selected((int) old('pengajuan_id') === $p->id)>{{ $p->nomor_ajuan }} — {{ ucfirst(str_replace('_', ' ', $p->jenis_layanan)) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Judul Dokumen</label>
        <input name="judul" value="{{ old('judul') }}" required class="w-full border rounded px-3 py-2">
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kode Arsip (tata naskah)</label>
            <input name="kode_arsip" value="{{ old('kode_arsip') }}" required placeholder="mis. 800/ASM/X/2026" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Kategori</label>
            <input name="kategori" value="{{ old('kategori') }}" placeholder="mis. Kepegawaian" class="w-full border rounded px-3 py-2">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Status Dokumen</label>
            <select name="status_dokumen" id="status_dokumen" required class="w-full border rounded px-3 py-2">
                @foreach($statusDokumen as $key => $label)
                    <option value="{{ $key }}" @selected(old('status_dokumen', 'aktif') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Lokasi Simpan</label>
            <select name="lokasi" id="lokasi" required class="w-full border rounded px-3 py-2">
                @foreach($lokasi as $key => $label)
                    <option value="{{ $key }}" @selected(old('lokasi', 'lemari_arsip') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nomor Daftar Arsip</label>
            <input name="nomor_daftar_arsip" value="{{ old('nomor_daftar_arsip') }}" placeholder="mis. DA/2026/001" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Daftar Arsip Ditandatangani Oleh</label>
            <select name="ditandatangai_oleh" class="w-full border rounded px-3 py-2">
                <option value="">— pilih —</option>
                @foreach($penandatangan as $p)
                    <option value="{{ $p->id }}" @selected((int) old('ditandatangai_oleh') === $p->id)>{{ $p->nama }} ({{ $p->role->nama_role }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <button class="bg-slate-800 text-white px-5 py-2 rounded hover:bg-slate-700">Simpan Arsip</button>
</form>

<script>
(function () {
    var sel = document.getElementById('jenis_dokumen');
    function toggle() {
        document.querySelectorAll('[data-sumber]').forEach(function (el) {
            el.classList.toggle('hidden', el.dataset.sumber !== sel.value);
        });
    }
    // sinkronkan lokasi bawaan mengikuti status dokumen (aktif->lemari, tidak aktif->gudang, penting->lurah)
    var statusSel = document.getElementById('status_dokumen');
    var lokasiSel = document.getElementById('lokasi');
    var map = { aktif: 'lemari_arsip', tidak_aktif: 'gudang_arsip', penting: 'lurah' };
    statusSel.addEventListener('change', function () { lokasiSel.value = map[statusSel.value]; });
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
@endsection
