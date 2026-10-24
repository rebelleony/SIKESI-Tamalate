@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-4">Buat Pengajuan Layanan</h1>

@if($errors->any())
<div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded p-3 mb-4 max-w-2xl">
    <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('pengajuan.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4 max-w-2xl">
    @csrf

    <div>
        <label class="block text-sm font-medium mb-1">Jenis Layanan</label>
        <select name="jenis_layanan" id="jenis_layanan" required class="w-full border rounded px-3 py-2">
            @foreach($labelJenis as $key => $label)
                <option value="{{ $key }}" @selected(old('jenis_layanan', 'cuti') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- ===== CUTI ===== --}}
    <fieldset data-jenis="cuti" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Jenis Cuti</label>
            <select name="jenis_cuti_id" class="w-full border rounded px-3 py-2">
                <option value="">— pilih —</option>
                @foreach($jenisCuti as $jc)
                    <option value="{{ $jc->id }}" @selected((int) old('jenis_cuti_id') === $jc->id)>{{ $jc->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Alasan</label>
            <input name="alasan" value="{{ old('alasan') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Lama (hari)</label>
                <input type="number" name="lama_hari" min="1" value="{{ old('lama_hari') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Alamat Selama Cuti</label>
            <input name="alamat_cuti" value="{{ old('alamat_cuti') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. Telepon</label>
            <input name="no_telp" value="{{ old('no_telp', $pegawai->no_telp) }}" class="w-full border rounded px-3 py-2">
        </div>
    </fieldset>

    {{-- ===== KGB ===== --}}
    <fieldset data-jenis="kgb" class="space-y-4 hidden">
        <div class="bg-slate-50 border rounded p-4 text-sm space-y-1">
            <p><span class="text-gray-500">Golongan/Ruang:</span> {{ $pegawai->pangkat_gol ?? '-' }}</p>
            <p><span class="text-gray-500">TMT Golongan Terakhir:</span> {{ optional($pegawai->tmt_golongan)->format('d/m/Y') ?? '-' }}</p>
            <p><span class="text-gray-500">Masa Kerja:</span> {{ $pegawai->masaKerjaTeks() }}</p>
            <p class="font-medium text-green-700">TMT KGB Berikutnya (otomatis, +24 bulan): {{ optional($nextKgb)->format('d/m/Y') ?? 'data TMT belum lengkap' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full border rounded px-3 py-2">{{ old('keterangan') }}</textarea>
        </div>
    </fieldset>

    {{-- ===== KENAIKAN PANGKAT ===== --}}
    <fieldset data-jenis="kenaikan_pangkat" class="space-y-4 hidden">
        <div class="bg-slate-50 border rounded p-4 text-sm space-y-1">
            <p><span class="text-gray-500">Pangkat/Gol Sekarang:</span> {{ $pegawai->pangkat_gol ?? '-' }}</p>
            <p><span class="text-gray-500">TMT Pangkat Sekarang:</span> {{ optional($pegawai->tmt_golongan)->format('d/m/Y') ?? '-' }}</p>
            <p><span class="text-gray-500">Masa Kerja:</span> {{ $pegawai->masaKerjaTeks() }}</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Pangkat/Gol Diusulkan</label>
                <select name="pangkat_gol_diusulkan" class="w-full border rounded px-3 py-2">
                    <option value="">— pilih —</option>
                    @foreach($golongan as $g)
                        <option value="{{ $g }}" @selected(old('pangkat_gol_diusulkan', $nextGol) === $g)>{{ $g }}</option>
                    @endforeach
                </select>
                @if($nextGol)<p class="text-xs text-gray-500 mt-1">Saran sistem (satu tingkat di atas): {{ $nextGol }}</p>@endif
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">TMT Pangkat Diusulkan</label>
                <input type="date" name="tmt_pangkat_diusulkan" value="{{ old('tmt_pangkat_diusulkan') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full border rounded px-3 py-2">{{ old('keterangan') }}</textarea>
        </div>
    </fieldset>

    {{-- ===== PENSIUN ===== --}}
    <fieldset data-jenis="pensiun" class="space-y-4 hidden">
        <div>
            <label class="block text-sm font-medium mb-1">Jenis Pensiun</label>
            <select name="jenis_pensiun" class="w-full border rounded px-3 py-2">
                @foreach($jenisPensiun as $key => $label)
                    <option value="{{ $key }}" @selected(old('jenis_pensiun', 'BUP') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="bg-slate-50 border rounded p-4 text-sm space-y-1">
            <p><span class="text-gray-500">Tanggal Lahir:</span> {{ optional($pegawai->tanggal_lahir)->format('d/m/Y') ?? '-' }}</p>
            <p><span class="text-gray-500">Batas Usia Pensiun:</span> {{ $bup }} tahun</p>
            <p class="font-medium text-green-700">Perkiraan TMT Pensiun (otomatis): {{ optional($tmtPensiun)->format('d/m/Y') ?? 'data tanggal lahir belum lengkap' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full border rounded px-3 py-2">{{ old('keterangan') }}</textarea>
        </div>
    </fieldset>

    <div>
        <label class="block text-sm font-medium mb-1">Berkas Persyaratan (PDF/JPG/PNG, maks 2MB tiap file)</label>
        <input type="file" name="berkas[]" multiple required class="w-full text-sm">
    </div>

    <button class="bg-slate-800 text-white px-5 py-2 rounded hover:bg-slate-700">Kirim Pengajuan</button>
</form>

<script>
(function () {
    var sel = document.getElementById('jenis_layanan');
    function toggle() {
        document.querySelectorAll('fieldset[data-jenis]').forEach(function (fs) {
            fs.classList.toggle('hidden', fs.dataset.jenis !== sel.value);
        });
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
@endsection
