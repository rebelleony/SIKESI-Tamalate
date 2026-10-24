<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan — {{ $jenisList[$jenis] }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .summary { margin: 8px 0; }
        .summary span { display: inline-block; margin-right: 18px; }
        .foot { margin-top: 24px; color: #555; }
        @media print { .noprint { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $jenisList[$jenis] }}</h1>
    <div class="meta">
        Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu — Kecamatan Tamalate<br>
        Periode: {{ \Illuminate\Support\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Illuminate\Support\Carbon::parse($sampai)->format('d/m/Y') }}
        · Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="summary">
        @foreach($summary as $label => $nilai)
            <span><strong>{{ $label }}:</strong> {{ $nilai }}</span>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>@foreach($columns as $c)<th>{{ $c }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>@foreach($r as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">Tidak ada rekaman data pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">Dokumen dihasilkan sistem untuk keperluan rekapitulasi dan arsip.</div>
    <button class="noprint" onclick="window.print()" style="margin-top:16px;padding:6px 12px;">Cetak Ulang</button>
</body>
</html>
