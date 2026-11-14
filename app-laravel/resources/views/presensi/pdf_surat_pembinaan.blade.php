<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Panggilan Pembinaan · {{ $pegawai->nama }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111; line-height: 1.6; margin: 28px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 18px; }
        .meta { margin-bottom: 18px; }
        .ttd { width: 45%; margin-left: 55%; text-align: center; margin-top: 32px; }
    </style>
</head>
<body>
    <div class="kop">
        <div style="font-size: 13px; font-weight: bold;">PEMERINTAH KOTA MAKASSAR</div>
        <div style="font-size: 17px; font-weight: bold;">KECAMATAN TAMALATE</div>
        <div style="font-size: 10px;">Jl. Danau Tanjung Bunga Utara · Kota Makassar</div>
    </div>

    <div class="meta">
        <table style="width:100%; border:none;">
            <tr>
                <td width="55%">
                    Nomor : 800/_____/KT/{{ now()->format('Y') }}<br>
                    Sifat : Penting<br>
                    Hal   : <strong>Panggilan Pembinaan Disiplin Pegawai</strong>
                </td>
                <td width="45%">
                    Makassar, {{ now()->format('d F Y') }}<br><br>
                    Kepada Yth.<br>
                    <strong>{{ $pegawai->nama }}</strong><br>
                    NIP. {{ $pegawai->nip }}<br>
                    di - {{ $pegawai->unitKerja->nama ?? 'Kecamatan Tamalate' }}
                </td>
            </tr>
        </table>
    </div>

    <p>Dengan hormat,</p>
    <p>
        Berdasarkan hasil rekapitulasi presensi apel pagi dan apel sore pada bulan <strong>{{ $bulan }}</strong>,
        diketahui bahwa Saudara/i tercatat memiliki akumulasi ketidakhadiran <strong>Tanpa Keterangan (TK)</strong> sebanyak
        <strong>{{ $jumlahTk }} kali</strong>.
    </p>
    <p>
        Sehubungan dengan hal tersebut, dalam rangka penegakan disiplin pegawai di lingkungan Pemerintah Kecamatan Tamalate
        Kota Makassar, diminta kehadiran Saudara/i untuk mengikuti <strong>Pembinaan Disiplin Pegawai</strong> pada:
    </p>
    <table style="margin-left: 24px; margin-bottom: 14px;">
        <tr><td width="110">Hari / Tanggal</td><td>: ______________________________</td></tr>
        <tr><td>Waktu</td><td>: 09.00 WITA s.d. Selesai</td></tr>
        <tr><td>Tempat</td><td>: Ruang Sekretaris Camat / Subbag Umum &amp; Kepegawaian Kecamatan Tamalate</td></tr>
    </table>
    <p>Demikian surat panggilan ini disampaikan untuk dilaksanakan sebagaimana mestinya.</p>

    <div class="ttd">
        Camat Tamalate,<br><br><br><br>
        <u>( ________________________ )</u><br>
        NIP. ______________________
    </div>
</body>
</html>

