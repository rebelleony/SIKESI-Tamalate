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

    <div class="kop">
        <strong style="font-size:13px;">PEMERINTAH KOTA MAKASSAR · KECAMATAN TAMALATE</strong><br>
        <span style="font-size: 9px;">Jalan Danau Tanjung Bunga Utara No. 181 Makassar 90224 · Telp. +62411 - 879 249</span><br>
        <span style="font-size: 8.5px;">Email : tamalate.bekerja22@gmail.com · Nomor Registrasi: {{ $p->nomor_pengajuan }} · Klasifikasi: {{ $p->kode_klasifikasi }}</span>
    </div>

    <div class="header">
        Makassar, {{ $p->created_at->format('d F Y') }}<br>
        Kepada Yth. Wali Kota Makassar<br>
        Cq. Kepala Badan Kepegawaian dan Pengembangan SDM Daerah Kota Makassar<br>
        di - Makassar
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
            <td>Masa Kerja</td><td>{{ $pegawai->masaKerjaTeks() ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td><td>{{ $p->unitKerja->nama ?? 'Kecamatan Tamalate' }}</td>
            <td>Pangkat / Gol.</td><td>{{ $pegawai->pangkat_gol ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">II. JENIS CUTI YANG DIAMBIL</td></tr>
        @foreach($kodeCuti as $kode => $nama)
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
        <tr><td colspan="2" class="sec-title">III. ALASAN CUTI</td></tr>
        <tr><td>{{ $d['alasan'] ?? '-' }}</td></tr>
    </table>

    <table>
        <tr><td colspan="6" class="sec-title">IV. LAMA CUTI</td></tr>
        <tr>
            <td width="12%">Selama</td><td width="18%">{{ $d['lama_hari'] ?? '-' }} Hari</td>
            <td width="18%">Mulai Tanggal</td><td width="22%">{{ $d['tanggal_mulai'] ?? '-' }}</td>
            <td width="8%" style="text-align:center;">s/d</td><td width="22%">{{ $d['tanggal_selesai'] ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr><td colspan="5" class="sec-title">V. CATATAN CUTI</td></tr>
        <tr><td colspan="3"><strong>1. CUTI TAHUNAN</strong></td><td colspan="2">2. CUTI BESAR</td></tr>
        <tr><td width="15%">Tahun</td><td width="15%">Sisa</td><td width="34%">Keterangan</td><td colspan="2">3. CUTI SAKIT</td></tr>
        <tr><td>{{ $d['tahun_cuti'] ?? $p->created_at?->format('Y') }}</td><td>{{ $d['sisa_cuti'] ?? '........' }}</td><td>{{ $d['keterangan_cuti'] ?? '' }}</td><td colspan="2">4. CUTI MELAHIRKAN</td></tr>
        <tr><td colspan="3"></td><td colspan="2">5. CUTI KARENA ALASAN PENTING</td></tr>
        <tr><td colspan="3"></td><td colspan="2">6. CUTI DI LUAR TANGGUNGAN NEGARA</td></tr>
    </table>

    <table>
        <tr><td colspan="2" class="sec-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
        <tr>
            <td width="55%">Alamat: {{ $d['alamat_cuti'] ?? '-' }}<br>Telp / HP: {{ $d['no_telp'] ?? $d['no_hp'] ?? '-' }}</td>
            <td width="45%" style="text-align:center;">
                Hormat Saya,<br>Pemohon<br>
                <div style="margin:8px 18px; padding:4px; border:1px dashed #64748b; font-size:8.5px;">Diajukan secara elektronik<br>{{ optional($p->created_at)->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA</div>
                <u><strong>{{ $pegawai->nama ?? '-' }}</strong></u><br>NIP. {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">VII. PERTIMBANGAN ATASAN LANGSUNG</td></tr>
        <tr><td width="25%">DISETUJUI</td><td width="25%">PERUBAHAN</td><td width="25%">DITANGGUHKAN</td><td width="25%">TIDAK DISETUJUI</td></tr>
        <tr><td colspan="2" style="height:34px;"></td><td colspan="2" style="text-align:center;">Atasan Langsung<br><br><br><u>{{ $pegawai->atasan->nama ?? '........................................' }}</u><br>NIP. {{ $pegawai->atasan->nip ?? '........................' }}</td></tr>
    </table>

    <table>
        <tr><td colspan="4" class="sec-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td></tr>
        <tr><td width="25%">DISETUJUI</td><td width="25%">PERUBAHAN</td><td width="25%">DITANGGUHKAN</td><td width="25%">TIDAK DISETUJUI</td></tr>
        <tr>
            <td colspan="2">Status Pemrosesan: <strong>{{ $p->labelStatus() }}</strong><br>Posisi Meja: <strong>{{ $p->meja_saat_ini }}</strong></td>
            <td colspan="2" style="text-align:center;">
                Camat Tamalate<br>
                @if(!empty($signatures['camat']))
                    <div style="border:1.5px solid #059669; background:#ecfdf5; padding:5px; margin:5px 8px; font-size:8px; text-align:left; line-height:1.3;">
                        <strong>DITANDATANGANI ELEKTRONIK</strong><br>
                        {{ $signatures['camat']['nama'] }} · NIP. {{ $signatures['camat']['nip'] }}<br>
                        {{ $signatures['camat']['waktu_wita'] }}<br>
                        Token: <span style="font-family:monospace;">{{ substr($signatures['camat']['token'], 0, 24) }}...</span>
                    </div>
                    <u><strong>{{ $signatures['camat']['nama'] }}</strong></u><br>NIP. {{ $signatures['camat']['nip'] }}
                @else
                    <div style="margin:12px 0; color:#6b7280; font-size:9px;">[Menunggu Pengesahan]</div>
                    <u>( ________________________ )</u>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

