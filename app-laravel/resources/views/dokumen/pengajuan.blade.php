<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $doc['judul'] }}</title>
    <style>
        @page { margin: 18mm 20mm 15mm 20mm; }
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 11pt; color: #000; line-height: 1.45; }

        /* Kop surat */
        .kop { width: 100%; border-bottom: 3px solid #000; padding-bottom: 4px; }
        .kop td.logo { width: 82px; vertical-align: middle; text-align: center; }
        .kop .instansi { text-align: center; vertical-align: middle; }
        .kop .induk { font-size: 13pt; font-weight: bold; letter-spacing: .5px; }
        .kop .nama  { font-size: 18pt; font-weight: bold; letter-spacing: 1px; }
        .kop .alamat{ font-size: 8.5pt; font-style: italic; }

        /* Judul */
        .judul { text-align: center; margin: 18px 0 4px; }
        .judul .j { font-size: 13pt; font-weight: bold; text-decoration: underline; letter-spacing: .5px; }
        .judul .s { font-size: 11.5pt; font-weight: bold; margin-top: 3px; }
        .nomor { text-align: center; font-size: 10.5pt; margin-bottom: 16px; }

        /* Konsideran */
        .bag { font-weight: bold; margin: 10px 0 4px; text-align: center; text-decoration: underline; }
        table.kons { width: 100%; border-collapse: collapse; }
        table.kons td { vertical-align: top; padding: 1px 0; }
        table.kons td.no { width: 26px; }

        /* Data pegawai */
        table.data { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.data th, table.data td { border: 1px solid #444; padding: 4px 7px; font-size: 10.5pt; }
        table.data th { background: #eee; text-align: left; width: 42%; font-weight: bold; }

        /* Tanda tangan */
        table.ttd { width: 100%; margin-top: 22px; }
        table.ttd td { vertical-align: top; }
        .ttdbox { text-align: center; width: 46%; }
        .ttdbox .jab { font-weight: bold; }
        .ruang { height: 66px; }
        .nama-ttd { font-weight: bold; text-decoration: underline; }

        .tembusan { margin-top: 26px; font-size: 10pt; }
        .tembusan .t { font-weight: bold; }
        ol.temb { margin: 3px 0 0 18px; padding: 0; }
    </style>
</head>
<body>

<table class="kop">
    <tr>
        <td class="logo">
            @if($doc['kop']['logo'])
                <img src="{{ $doc['kop']['logo'] }}" height="72">
            @else
                <div style="width:70px;height:70px;border:2px solid #000;border-radius:50%;line-height:70px;text-align:center;font-size:7pt;font-weight:bold;">LOGO</div>
            @endif
        </td>
        <td class="instansi">
            <div class="induk">{{ $doc['kop']['induk'] }}</div>
            <div class="nama">{{ $doc['kop']['instansi'] }}</div>
            <div class="alamat">{{ $doc['kop']['alamat'] }}</div>
            @if($doc['kop']['kontak'])<div class="alamat">{{ $doc['kop']['kontak'] }}</div>@endif
        </td>
        <td class="logo"></td>
    </tr>
</table>

<div class="judul">
    <div class="j">{{ $doc['judul'] }}</div>
    <div class="s">{!! nl2br(e(str_replace('\n', "\n", $doc['subjudul']))) !!}</div>
</div>
<div class="nomor">Nomor: {{ $doc['nomor'] }}</div>

<div class="bag">Menimbang</div>
<table class="kons">
    @foreach($doc['menimbang'] as $m)
        @php $huruf = trim(substr($m, 0, 3)); $isi = trim(substr($m, 3)); @endphp
        <tr><td class="no">{{ $huruf }}</td><td>{{ $isi }}</td></tr>
    @endforeach
</table>

<div class="bag">Mengingat</div>
<table class="kons">
    @foreach($doc['mengingat'] as $ig)
        @php $no = trim(substr($ig, 0, strpos($ig, '.') + 1)); $isi = trim(substr($ig, strpos($ig, '.') + 1)); @endphp
        <tr><td class="no">{{ $no }}</td><td>{{ $isi }}</td></tr>
    @endforeach
</table>

<div class="bag">Memutuskan</div>
<table class="kons">
    @foreach($doc['memutuskan'] as $i => $dk)
        @if($i === 0)
            <tr><td class="no"></td><td><strong>{{ $dk }}</strong></td></tr>
        @else
            @php $pisah = strpos($dk, ':'); $kunci = $pisah ? substr($dk, 0, $pisah) : ''; $isi = $pisah ? trim(substr($dk, $pisah + 1)) : $dk; @endphp
            <tr><td class="no" style="width:auto;padding-right:6px;white-space:nowrap;"><strong>{{ $kunci }}:</strong></td><td>{{ $isi }}</td></tr>
        @endif
    @endforeach
</table>

<table class="data">
    @foreach($doc['data'] as $label => $nilai)
        <tr><th>{{ $label }}</th><td>: {{ $nilai }}</td></tr>
    @endforeach
</table>

<table class="ttd">
    <tr>
        <td></td>
        <td class="ttdbox">
            <div>{{ $doc['ttd']['kota'] }}, {{ $doc['ttd']['tanggal'] }}</div>
            <div class="jab">{{ $doc['ttd']['jabatan'] }}</div>
            <div class="ruang"></div>
            <div class="nama-ttd">{{ $doc['ttd']['nama'] ?? '.................................' }}</div>
            @if($doc['ttd']['nip'])<div>NIP. {{ $doc['ttd']['nip'] }}</div>@endif
        </td>
    </tr>
</table>

<div class="tembusan">
    <div class="t">Tembusan disampaikan kepada Yth.:</div>
    <ol class="temb">
        @foreach($doc['tembusan'] as $t)
            <li>{{ $t }}</li>
        @endforeach
    </ol>
</div>

</body>
</html>
