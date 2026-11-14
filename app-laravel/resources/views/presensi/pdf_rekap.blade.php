<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi Apel · {{ $bulan }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111; }
        h2 { text-align: center; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px; }
        th { background: #1d4ed8; color: #fff; }
        .center { text-align: center; }
        .warn { background: #fee2e2; color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h2>REKAPITULASI PRESENSI APEL PEGAWAI BULAN {{ $bulan }}<br>KECAMATAN TAMALATE · KOTA MAKASSAR</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Unit Kerja</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Dinas Luar</th>
                <th>TK</th>
                <th>Status Pembinaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap->values() as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $row['pegawai']->nip }}</td>
                    <td>{{ $row['pegawai']->nama }}</td>
                    <td>{{ $row['pegawai']->unitKerja->nama ?? '-' }}</td>
                    <td class="center">{{ $row['hadir'] }}</td>
                    <td class="center">{{ $row['izin'] }}</td>
                    <td class="center">{{ $row['dinas_luar'] }}</td>
                    <td class="center {{ $row['pembinaan'] ? 'warn' : '' }}">{{ $row['tk'] }}</td>
                    <td class="center">{{ $row['pembinaan'] ? 'PERLU PEMBINAAN' : 'Normal' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

