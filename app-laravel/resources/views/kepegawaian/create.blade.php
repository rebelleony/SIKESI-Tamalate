@extends('layouts.app')
@section('title', 'Ajukan Layanan Kepegawaian · Kecamatan Tamalate')

@section('content')
<div class="mb-6">
    <a href="{{ route('kepegawaian.index') }}" class="text-sm font-semibold text-primary-700 hover:underline">← Kembali ke Daftar Layanan Kepegawaian</a>
    <h1 class="text-2xl font-display font-extrabold text-primary-950 mt-2">Pengajuan Layanan Kepegawaian</h1>
    <p class="text-sm text-slate-500">
        Unit Anda: <strong>{{ $pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}</strong>
        ({{ $pegawai->unitKerja?->tipe === 'kelurahan' ? 'Alur Kelurahan → Kecamatan → BKPSDMD' : 'Alur Kecamatan → BKPSDMD' }})
    </p>
</div>

<form method="POST" action="{{ route('kepegawaian.store') }}" enctype="multipart/form-data" class="bg-white border border-blue-100 p-6 rounded-2xl shadow-card space-y-5 max-w-3xl">
    @csrf

    <div>
        @if($pegawai->isPppkParuhWaktu())
            <input type="hidden" name="jenis_layanan" id="jenis_layanan" value="cuti">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Layanan</label>
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-3.5 py-2.5 text-sm font-semibold text-blue-900">Pengajuan Cuti PPPK Paruh Waktu</div>
        @else
            <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Jenis Layanan <span class="text-rose-600">*</span></label>
            <select name="jenis_layanan" id="jenis_layanan" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:border-primary-500 focus:outline-none">
                @foreach($jenisList as $k => $lbl)
                    <option value="{{ $k }}" @selected(old('jenis_layanan', 'cuti') === $k)>{{ $lbl }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <!-- Section: CUTI -->
    <div data-section="cuti" class="space-y-4 border-t border-slate-100 pt-4">
        <h2 class="font-bold text-sm text-primary-900 uppercase tracking-wider">{{ $pegawai->isPppk() ? 'Jenis Cuti PPPK' : 'Detail Pengajuan Cuti (800.1.11.2 s/d .7)' }}</h2>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Cuti & Kode Klasifikasi</label>
            <select name="kode_cuti" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
                @foreach($kodeCuti as $kode => $nama)
                    <option value="{{ $kode }}" @selected(old('kode_cuti') === $kode)>{{ $kode === 'cuti_bersama' ? '800' : $kode }} — {{ $nama }}</option>
                @endforeach
            </select>
            @if($pegawai->isPppk())
                <p class="mt-1 text-xs text-slate-500">Jenis cuti mengikuti Peraturan BKN Nomor 7 Tahun 2022.</p>
            @endif
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Alasan Cuti</label>
            <textarea name="alasan" rows="2" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('alasan') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">No. Telp / HP</label>
            <input name="no_telp" value="{{ old('no_telp', $pegawai->no_hp) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Selama Menjalankan Cuti</label>
            <textarea name="alamat_cuti" rows="2" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">{{ old('alamat_cuti', $pegawai->alamat) }}</textarea>
        </div>
    </div>

    <!-- Section: KGB -->
    <div data-section="kgb" class="hidden space-y-4 border-t border-slate-100 pt-4">
        <h2 class="font-bold text-sm text-primary-900 uppercase tracking-wider">Detail Kenaikan Gaji Berkala (800.1.11.13)</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">TMT KGB Terakhir</label>
                <input type="date" name="tmt_kgb_terakhir" value="{{ old('tmt_kgb_terakhir', optional($pegawai->tmt_golongan)->toDateString()) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Gaji Pokok Lama (Rp)</label>
                <input type="number" name="gaji_pokok_lama" value="{{ old('gaji_pokok_lama', $pegawai->gaji_pokok ?? 3000000) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Gaji Pokok Baru (opsional)</label>
                <input type="number" name="gaji_pokok_baru" value="{{ old('gaji_pokok_baru') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Masa Kerja (Tahun)</label>
                <input type="number" name="masa_kerja_tahun" value="{{ old('masa_kerja_tahun', $pegawai->masa_kerja_tahun) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Masa Kerja (Bulan)</label>
                <input type="number" name="masa_kerja_bulan" value="{{ old('masa_kerja_bulan', $pegawai->masa_kerja_bulan) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan</label>
            <input name="keterangan" value="{{ old('keterangan') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
        </div>
    </div>

    <!-- Section: KENAIKAN PANGKAT -->
    <div data-section="kenaikan_pangkat" class="hidden space-y-4 border-t border-slate-100 pt-4">
        <h2 class="font-bold text-sm text-primary-900 uppercase tracking-wider">Detail Usulan Kenaikan Pangkat (800.1.3.2)</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Pangkat/Golongan Saat Ini</label>
                <input name="pangkat_lama" value="{{ old('pangkat_lama', $pegawai->pangkat_gol ?? 'III/a') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Pangkat/Golongan Diusulkan</label>
                <select name="pangkat_diusulkan" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
                    @foreach($golongan as $g)
                        <option value="{{ $g }}" @selected(old('pangkat_diusulkan', $nextGol) === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">TMT Pangkat Terakhir</label>
                <input type="date" name="tmt_pangkat_terakhir" value="{{ old('tmt_pangkat_terakhir', optional($pegawai->tmt_golongan)->toDateString()) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">TMT Diusulkan</label>
                <input type="date" name="tmt_pangkat_diusulkan" value="{{ old('tmt_pangkat_diusulkan') }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Periode Usulan</label>
                <input name="periode_usulan" value="{{ old('periode_usulan', 'Oktober ' . now()->year) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
        </div>
    </div>

    <!-- Section: PENSIUN -->
    <div data-section="pensiun" class="hidden space-y-4 border-t border-slate-100 pt-4">
        <h2 class="font-bold text-sm text-primary-900 uppercase tracking-wider">Detail Usulan Pensiun PNS (800.1.6.6)</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Pensiun</label>
                <select name="jenis_pensiun" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
                    @foreach($jenisPensiun as $k => $lbl)
                        <option value="{{ $k }}" @selected(old('jenis_pensiun') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">TMT Pensiun</label>
                <input type="date" name="tmt_pensiun" value="{{ old('tmt_pensiun', optional($tmtPensiun)->toDateString()) }}" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm">
            </div>
        </div>
    </div>

    <!-- Upload Lampiran Digital (PDF max 5MB) -->
    <div class="border-t border-slate-100 pt-4 space-y-3">
        <div class="rounded-xl bg-blue-50/80 border border-blue-200 px-3.5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <span class="text-slate-700">Belum punya format surat? Lihat contoh pengisian &amp; unduh template resmi (.DOCX / .PDF).</span>
            <a href="{{ route('template-surat.index') }}" target="_blank" class="font-bold text-primary-700 hover:underline shrink-0">
                Buka Template Surat ↗
            </a>
        </div>
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-slate-700">Unggah Persyaratan Digital (PDF, maks. 5MB per file) <span class="text-rose-600">*</span></label>
            <button type="button" onclick="tambahLampiran()" class="text-xs font-bold text-primary-700 hover:underline">+ Tambah File</button>
        </div>
        <div id="lampiranContainer" class="space-y-2">
            <div class="grid sm:grid-cols-2 gap-2">
                <input type="text" name="nama_dokumen[]" placeholder="Nama dokumen (mis. SK Pangkat / SK KGB Terakhir)" class="border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <input type="file" name="lampiran[]" accept=".pdf" required class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-primary-700 file:font-semibold">
            </div>
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="bg-primary-700 hover:bg-primary-800 text-white font-bold px-6 py-3 rounded-xl text-sm shadow-card transition">
            Kirim Pengajuan Kepegawaian
        </button>
    </div>
</form>

<script>
(function () {
    var sel = document.getElementById('jenis_layanan');
    function toggleSections() {
        document.querySelectorAll('[data-section]').forEach(function (el) {
            var active = el.dataset.section === sel.value;
            el.classList.toggle('hidden', !active);
            el.querySelectorAll('input, select, textarea').forEach(function (inp) {
                inp.disabled = !active;
            });
        });
    }
    sel.addEventListener('change', toggleSections);
    toggleSections();
})();

function tambahLampiran() {
    var div = document.createElement('div');
    div.className = 'grid sm:grid-cols-2 gap-2';
    div.innerHTML = '<input type="text" name="nama_dokumen[]" placeholder="Nama dokumen persyaratan" class="border border-slate-200 rounded-xl px-3 py-2 text-xs">' +
                    '<input type="file" name="lampiran[]" accept=".pdf" class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-primary-700 file:font-semibold">';
    document.getElementById('lampiranContainer').appendChild(div);
}
</script>
@endsection

