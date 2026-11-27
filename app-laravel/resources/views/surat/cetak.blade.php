<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $doc['judul'] }} - {{ $doc['nomor'] }}</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 20mm 17mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: "Times New Roman", "DejaVu Serif", serif; font-size: 11pt; line-height: 1.35; }
        .toolbar { margin: 16px auto; text-align: center; }
        .print-button { border: 0; border-radius: 4px; background: #193b30; color: #fff; cursor: pointer; font: 600 14px Arial, sans-serif; padding: 10px 18px; }
        .paper { width: 100%; }
        .letterhead { width: 100%; border-collapse: collapse; border-bottom: 3px solid #111; }
        .letterhead td { padding: 0 4px 8px; vertical-align: middle; }
        .letterhead .logo { width: 82px; text-align: center; }
        .letterhead .logo img { max-width: 70px; max-height: 76px; }
        .letterhead .identity { text-align: center; }
        .identity .top { font-size: 13pt; font-weight: bold; }
        .identity .district { font-size: 16pt; font-weight: bold; }
        .identity .address { font-size: 8.5pt; line-height: 1.2; }
        .thin-rule { border-top: 1px solid #111; margin-top: 2px; }
        .heading { margin: 19px 0 14px; text-align: center; }
        .heading .title { font-size: 13pt; font-weight: bold; text-decoration: underline; }
        .heading .subtitle { margin-top: 3px; font-size: 11pt; font-weight: bold; }
        .number { margin-top: 4px; }
        p { margin: 8px 0; text-align: justify; }
        .data { width: 100%; margin: 12px 0; border-collapse: collapse; }
        .data td { padding: 2px 3px; vertical-align: top; }
        .data .label { width: 34%; }
        .body-title { margin: 10px 0 4px; font-weight: bold; }
        .clauses { width: 100%; border-collapse: collapse; }
        .clauses td { padding: 3px 2px; vertical-align: top; text-align: justify; }
        .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures td { width: 50%; padding: 4px; vertical-align: bottom; }
        .auth-note { width: 92%; border: 1px solid #555; padding: 7px; font-size: 8.5pt; }
        .auth-hash { margin-top: 4px; font-family: monospace; font-size: 9pt; }
        .signer { text-align: center; }
        .signer .qr { display: block; width: 90px; height: 90px; margin: 5px auto; }
        .signer .electronic { font-size: 8.5pt; font-style: italic; }
        .signer .waiting { margin: 28px 0; font-weight: bold; }
        .signer .name { margin-top: 8px; font-weight: bold; text-decoration: underline; }
        .signature-error { color: #8b1e1e; font-weight: bold; }
        @media screen {
            body { background: #e8ece9; }
            .paper { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 18mm 20mm 17mm; background: #fff; box-shadow: 0 2px 12px #0002; }
        }
        @media print {
            .toolbar { display: none !important; }
            body { background: #fff; }
            .paper { width: auto; min-height: 0; padding: 0; box-shadow: none; }
            .letterhead, .data, .signatures { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button class="print-button" type="button" onclick="window.print()">Cetak Surat</button></div>
    <main class="paper">
        <table class="letterhead">
            <tr>
                <td class="logo">@if($logoKiri)<img src="{{ $logoKiri }}" alt="Lambang Kota Makassar">@endif</td>
                <td class="identity">
                    <div class="top">PEMERINTAH KOTA MAKASSAR</div>
                    <div class="district">KECAMATAN TAMALATE</div>
                    <div class="address">{{ config('dokumen.alamat', 'Jl. Danau Tanjung Bunga No. 181, Kel. Maccini Sombala, Kec. Tamalate, Kota Makassar 90224') }}</div>
                    <div class="address">Telp. {{ config('dokumen.telepon', '+62411 - 879 249') }} | {{ config('dokumen.homepage', 'http://www.kectamalate.com') }} | Pos-el: {{ config('dokumen.email', 'tamalate.bekerja22@gmail.com') }}</div>
                </td>
                <td class="logo">@if($logoKanan)<img src="{{ $logoKanan }}" alt="Lambang Kecamatan Tamalate">@endif</td>
            </tr>
        </table>
        <div class="thin-rule"></div>

        <header class="heading">
            <div class="title">{{ $doc['judul'] }}</div>
            <div class="subtitle">{!! nl2br(e(str_replace('\\n', "\n", $doc['subjudul']))) !!}</div>
            <div class="number">Nomor: {{ $doc['nomor'] }}</div>
        </header>

        <p>Dengan hormat, berdasarkan permohonan layanan kepegawaian yang diajukan oleh pegawai berikut, bersama ini disampaikan dokumen resmi untuk dipergunakan sebagaimana mestinya.</p>
        <div class="body-title">Data Pemohon/Pegawai</div>
        <table class="data">
            @foreach($doc['data'] as $label => $nilai)
                <tr><td class="label">{{ $label }}</td><td>: {{ $nilai }}</td></tr>
            @endforeach
        </table>

        <div class="body-title">Isi dan Ketetapan</div>
        <table class="clauses">
            @foreach($doc['memutuskan'] as $i => $baris)
                @if($i === 0)
                    <tr><td colspan="2">{{ $baris }}</td></tr>
                @else
                    @php $separator = strpos($baris, ':'); @endphp
                    <tr><td style="width: 76px; white-space: nowrap;"><strong>{{ $separator !== false ? substr($baris, 0, $separator + 1) : '' }}</strong></td><td>{{ $separator !== false ? trim(substr($baris, $separator + 1)) : $baris }}</td></tr>
                @endif
            @endforeach
        </table>
        @if($pengajuan->catatan)
            <p><strong>Keterangan:</strong> {{ $pengajuan->catatan }}</p>
        @endif
        <p>Demikian disampaikan untuk menjadi perhatian dan dilaksanakan sesuai ketentuan yang berlaku.</p>

        <table class="signatures">
            <tr>
                <td>
                    <div class="auth-note">Catatan keaslian dokumen<br>Verifikasi HMAC-SHA256:
                        <div class="auth-hash">{{ $signatureValid ? substr($pengajuan->tanda_tangan_token, 0, 12) : 'Belum tersedia' }}</div>
                    </div>
                </td>
                <td class="signer">
                    <div>{{ $doc['ttd']['kota'] }}, {{ $doc['ttd']['tanggal'] }}</div>
                    <div><strong>{{ $doc['ttd']['jabatan'] }}</strong></div>
                    @if($signatureValid && $qrCode)
                        <img class="qr" src="{{ $qrCode }}" alt="QR verifikasi tanda tangan elektronik">
                        <div class="electronic">Ditandatangani secara elektronik</div>
                    @elseif($signaturePresent)
                        <div class="waiting signature-error">Tanda tangan digital tidak dapat diverifikasi</div>
                    @else
                        <div class="waiting">[Menunggu Pengesahan]</div>
                    @endif
                    <div class="name">{{ $doc['ttd']['nama'] ?? '........................................' }}</div>
                    @if($doc['ttd']['nip'])<div>NIP. {{ $doc['ttd']['nip'] }}</div>@endif
                </td>
            </tr>
        </table>
    </main>
</body>
</html>