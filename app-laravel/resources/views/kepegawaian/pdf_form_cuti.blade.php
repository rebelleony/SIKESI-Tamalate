<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permintaan & Pemberian Cuti · {{ $p->nomor_pengajuan }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111; line-height: 1.4; }
        .header { text-align: right; font-size: 10px; margin-bottom: 12px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        .title { text-align: center; font-weight: bold; font-size: 13px; text-decoration: underline; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
        .sec-title { background: #e5e7eb; font-weight: bold; }
        .ttd-box { width: 100%; border: none; margin-top: 18px; }
        .ttd-box td { border: none; text-align: center; width: 50%; }
    </style>
</head>
<body>
    @php
        $d = $p->data_layanan ?? [];
        $pegawai = $p->pegawai;
    @endphp

    <div class="header">
        Makassar, {{ $p->created_at->format('d F Y') }}<br>
        Kepada Yth. Kepala BKPSDMD Kota Makassar<br>
        Melalui Camat Tamalate<br>
        di - Makassar
    </div>

    <div class="kop">
        <strong>PEMERINTAH KOTA MAKASSAR · KECAMATAN TAMALATE</strong><br>
        <span style="font-size: 9px;">Nomor Registrasi: {{ $p->nomor_pengajuan }} · Klasifikasi: {{ $p->kode_klasifikasi }}</span>
    </div>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

    <table>
        <tr><td colspan="4" class="sec-title">I. DATA PEGAWAI</td></tr>
        <tr>
            <td width="18%">Nama</td><td width="32%">{{ $pegawai->nama ?? '-' }}</td>
            <td width="18%">NIP</td><td width="32%">{{ $pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td><td>{{ $pegawai->jabatan ?? '-' }}</td>
            <td>Pangkat / Gol.</td><td>{{ $pegawai->pangkat_gol ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td><td colspan="3">{{ $p->unitKerja->nama ?? 'Kecamatan Tamalate' }}</td>
        </tr>
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">II. JENIS CUTI YANG DIAMBIL</td></tr>
        @foreach(\App\Models\PengajuanKepegawaian::KODE_CUTI as $kode => $nama)
            <tr>
                <td width="20%">{{ $kode }}</td>
                <td width="65%">{{ $nama }}</td>
                <td width="15%" style="text-align:center; font-weight:bold;">
                    {{ ($d['kode_cuti'] ?? '') === $kode ? '[ ✓ ]' : '[   ]' }}
                </td>
            </tr>
        @endforeach
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">III. ALASAN &amp; LAMANYA CUTI</td></tr>
        <tr>
            <td width="25%">Alasan Cuti</td>
            <td colspan="3">{{ $d['alasan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Selama</td><td>{{ $d['lama_hari'] ?? '-' }} Hari</td>
            <td>Mulai Tanggal</td><td>{{ $d['tanggal_mulai'] ?? '-' }} s/d {{ $d['tanggal_selesai'] ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">IV. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
        <tr>
            <td width="65%">{{ $d['alamat_cuti'] ?? '-' }}</td>
            <td width="12%">Telp/HP</td>
            <td width="23%">{{ $d['no_telp'] ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr><td colspan="2" class="sec-title">V. PERTIMBANGAN &amp; KEPUTUSAN PEJABAT BERWENANG</td></tr>
        <tr>
            <td width="50%">Status Pemrosesan: <strong>{{ $p->labelStatus() }}</strong></td>
            <td width="50%">Posisi Meja: <strong>{{ $p->meja_saat_ini }}</strong></td>
        </tr>
    </table>

    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Camat Tamalate<br><br><br><br>
                <u>( ________________________ )</u>
            </td>
            <td>
                Hormat Saya,<br>
                Pemohon<br><br><br><br>
                <u><strong>{{ $pegawai->nama ?? '-' }}</strong></u><br>
                NIP. {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>
</body>
</html>

