@extends('layouts.app')
@section('content')
@php $u = auth()->user(); $d = $pengajuan->data_form ?? []; @endphp
<a href="{{ back()->getTargetUrl() }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
<h1 class="text-2xl font-bold mb-1 mt-2">Detail Pengajuan</h1>
<p class="text-sm text-gray-500 mb-4">{{ $pengajuan->nomor_ajuan }} · <span class="font-medium">{{ $pengajuan->labelStatus() }}</span></p>

@if(in_array($pengajuan->status, \App\Models\Pengajuan::STATUS_SELESAI, true))
    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('pengajuan.dokumen', $pengajuan) }}" target="_blank"
           class="inline-block bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">
            Lihat Dokumen (PDF)
        </a>
        <a href="{{ route('pengajuan.dokumen', ['pengajuan' => $pengajuan, 'unduh' => 1]) }}"
           class="inline-block bg-slate-700 text-white text-sm px-4 py-2 rounded hover:bg-slate-800">
            Unduh PDF
        </a>
        <span class="text-xs text-gray-500">SK / surat izin resmi hasil pengajuan ini.</span>
    </div>
@endif

<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded shadow">
        <h2 class="font-semibold mb-3">Data Pemohon</h2>
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Nama:</dt> <dd class="inline">{{ $pengajuan->pegawai->nama }}</dd></div>
            <div><dt class="inline text-gray-500">NIP:</dt> <dd class="inline">{{ $pengajuan->pegawai->nip }}</dd></div>
            <div><dt class="inline text-gray-500">Jabatan:</dt> <dd class="inline">{{ $pengajuan->pegawai->jabatan }}</dd></div>
            <div><dt class="inline text-gray-500">Unit Kerja:</dt> <dd class="inline">{{ optional($pengajuan->pegawai->unitKerja)->nama }}</dd></div>
            <div><dt class="inline text-gray-500">Masa Kerja:</dt> <dd class="inline">{{ $pengajuan->pegawai->masaKerjaTeks() }}</dd></div>
        </dl>

        <h2 class="font-semibold mt-4 mb-2">Rincian Pengajuan</h2>
        @php
            $rincian = match ($pengajuan->jenis_layanan) {
                'cuti' => [
                    'Jenis Cuti' => \App\Models\JenisCuti::find($d['jenis_cuti_id'] ?? null)?->nama ?? '-',
                    'Alasan' => $d['alasan'] ?? '-',
                    'Lama' => ($d['lama_hari'] ?? '-') . ' hari',
                    'Periode' => ($d['tanggal_mulai'] ?? '-') . ' s/d ' . ($d['tanggal_selesai'] ?? '-'),
                    'Alamat Selama Cuti' => $d['alamat_cuti'] ?? '-',
                    'No. Telepon' => $d['no_telp'] ?? '-',
                ],
                'kgb' => [
                    'Golongan/Ruang' => $d['golongan_ruang'] ?? '-',
                    'TMT Golongan Terakhir' => $d['tmt_golongan_lama'] ?? '-',
                    'TMT KGB Baru' => $d['tanggal_kgb_baru'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                'kenaikan_pangkat' => [
                    'Pangkat/Gol Sekarang' => $d['pangkat_gol_sekarang'] ?? '-',
                    'TMT Pangkat Sekarang' => $d['tmt_pangkat_sekarang'] ?? '-',
                    'Pangkat/Gol Diusulkan' => $d['pangkat_gol_diusulkan'] ?? '-',
                    'TMT Pangkat Diusulkan' => $d['tmt_pangkat_diusulkan'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                'pensiun' => [
                    'Jenis Pensiun' => \App\Support\LayananKepegawaian::JENIS_PENSIUN[$d['jenis_pensiun'] ?? ''] ?? ($d['jenis_pensiun'] ?? '-'),
                    'Tanggal Lahir' => $d['tanggal_lahir'] ?? '-',
                    'Batas Usia Pensiun' => ($d['bup_tahun'] ?? '-') . ' tahun',
                    'TMT Pensiun' => $d['tmt_pensiun'] ?? '-',
                    'Masa Kerja' => $d['masa_kerja'] ?? '-',
                    'Keterangan' => $d['keterangan'] ?? '-',
                ],
                default => [],
            };
        @endphp
        <dl class="text-sm space-y-1">
            <div><dt class="inline text-gray-500">Jenis Layanan:</dt> <dd class="inline font-medium">{{ \App\Support\LayananKepegawaian::label($pengajuan->jenis_layanan) }}</dd></div>
            @foreach($rincian as $label => $nilai)
                <div><dt class="inline text-gray-500">{{ $label }}:</dt> <dd class="inline">{{ $nilai ?: '-' }}</dd></div>
            @endforeach
        </dl>
    </div>

    <div class="space-y-4">
        <div class="bg-white p-5 rounded shadow">
            <h2 class="font-semibold mb-3">Berkas Lampiran</h2>
            <ul class="text-sm space-y-1">
                @forelse($pengajuan->berkas as $b)
                    <li>📎 <a href="{{ route('berkas.download', $b) }}" class="text-blue-600 hover:underline">{{ $b->nama_file }}</a></li>
                @empty
                    <li class="text-gray-400">Tidak ada berkas.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <h2 class="font-semibold mb-3">Riwayat Proses</h2>
            <ol class="text-sm space-y-2 border-l-2 border-gray-200 pl-4">
                @forelse($pengajuan->riwayat as $r)
                    <li>
                        <div class="font-medium">{{ ucfirst($r->tahap) }} — {{ strtoupper($r->keputusan) }}</div>
                        <div class="text-gray-500">{{ optional($r->pejabat)->nama }} · {{ $r->waktu->format('d/m/Y H:i') }}</div>
                        @if($r->catatan)<div class="text-gray-600 italic">"{{ $r->catatan }}"</div>@endif
                    </li>
                @empty
                    <li class="text-gray-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</div>

{{-- Aksi sesuai peran & status --}}
@if($u->hasRole('verifikator','super_admin') && $pengajuan->status === 'menunggu_verifikasi')
<form method="POST" action="{{ route('approval.verifikasi', $pengajuan) }}" class="bg-white p-5 rounded shadow mt-4 max-w-2xl">
    @csrf
    <h2 class="font-semibold mb-3">Verifikasi Berkas</h2>
    <textarea name="catatan" placeholder="Catatan (wajib diisi bila mengembalikan untuk revisi)" class="w-full border rounded px-3 py-2 mb-3"></textarea>
    <div class="space-x-2">
        <button name="keputusan" value="teruskan" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Validasi & Teruskan</button>
        <button name="keputusan" value="revisi" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Kembalikan untuk Revisi</button>
    </div>
</form>
@endif

@if($u->hasRole('kasi_kasubag','super_admin') && $pengajuan->status === 'menunggu_approval_tier1')
<form method="POST" action="{{ route('approval.tier1', $pengajuan) }}" class="bg-white p-5 rounded shadow mt-4 max-w-2xl">
    @csrf
    <h2 class="font-semibold mb-3">Persetujuan Tingkat Pertama (Kasi/Kasubag)</h2>
    <textarea name="catatan" placeholder="Catatan / alasan penolakan" class="w-full border rounded px-3 py-2 mb-3"></textarea>
    <div class="space-x-2">
        <button name="keputusan" value="setuju" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Setujui</button>
        <button name="keputusan" value="tolak" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Tolak</button>
    </div>
</form>
@endif

@if($u->hasRole('seksam','super_admin') && $pengajuan->status === 'menunggu_approval_tier2')
<form method="POST" action="{{ route('approval.tier2', $pengajuan) }}" class="bg-white p-5 rounded shadow mt-4 max-w-2xl">
    @csrf
    <h2 class="font-semibold mb-3">Persetujuan Tingkat Kedua (Sekretaris Camat)</h2>
    <textarea name="catatan" placeholder="Catatan / alasan penolakan" class="w-full border rounded px-3 py-2 mb-3"></textarea>
    <div class="space-x-2">
        <button name="keputusan" value="setuju" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Verifikasi & Paraf</button>
        <button name="keputusan" value="tolak" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Tolak</button>
    </div>
</form>
@endif

@if($u->hasRole('camat_lurah','super_admin') && $pengajuan->status === 'menunggu_approval_final')
<form method="POST" action="{{ route('approval.final', $pengajuan) }}" class="bg-white p-5 rounded shadow mt-4 max-w-2xl">
    @csrf
    <h2 class="font-semibold mb-3">Persetujuan Akhir (Camat/Lurah)</h2>
    <textarea name="catatan" placeholder="Instruksi / alasan penolakan" class="w-full border rounded px-3 py-2 mb-3"></textarea>
    <div class="space-x-2">
        <button name="keputusan" value="setuju" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Setujui Akhir</button>
        <button name="keputusan" value="tolak" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Tolak</button>
    </div>
</form>
@endif

@if($u->hasRole('verifikator','super_admin') && $pengajuan->status === 'menunggu_pengiriman')
<form method="POST" action="{{ route('approval.kirim', $pengajuan) }}" class="bg-white p-5 rounded shadow mt-4 max-w-2xl">
    @csrf
    <h2 class="font-semibold mb-3">Registrasi & Kirim ke BKPSDMD</h2>
    <input name="nomor_agenda" placeholder="Nomor agenda surat keluar" class="w-full border rounded px-3 py-2 mb-3">
    <textarea name="catatan" placeholder="Catatan pengiriman (opsional)" class="w-full border rounded px-3 py-2 mb-3"></textarea>
    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tandai Telah Dikirim</button>
</form>
@endif
@endsection
