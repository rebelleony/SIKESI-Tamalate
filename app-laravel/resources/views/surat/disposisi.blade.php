<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi · {{ $surat->nomor_agenda }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="bg-slate-100 py-8 px-4 text-slate-900">
    <div class="max-w-3xl mx-auto mb-4 flex justify-between items-center no-print">
        <a href="{{ route('surat.show', $surat) }}" class="text-sm font-semibold text-blue-700 hover:underline">← Kembali ke Detail Surat</a>
        <button onclick="window.print()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-5 py-2 rounded-lg text-sm">
            Cetak Lembar Disposisi
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white border-2 border-slate-800 p-8 shadow-sm">
        <div class="text-center border-b-4 border-double border-slate-900 pb-4 mb-5">
            <div class="text-sm font-bold uppercase tracking-wider">Pemerintah Kota Makassar</div>
            <div class="text-xl font-extrabold uppercase tracking-wide">Kecamatan Tamalate</div>
            <div class="text-xs text-slate-600">Jl. Danau Tanjung Bunga Utara · Kota Makassar</div>
        </div>

        <h1 class="text-center text-base font-extrabold uppercase underline tracking-wider mb-5">
            LEMBAR DISPOSISI
        </h1>

        <table class="w-full text-sm border-collapse border border-slate-800 mb-5">
            <tr>
                <td class="border border-slate-800 p-2.5 w-1/2">
                    <div class="text-xs text-slate-500">Surat Dari / Instansi Pengirim:</div>
                    <div class="font-semibold">{{ $surat->asal_surat ?? '-' }}</div>
                </td>
                <td class="border border-slate-800 p-2.5 w-1/2">
                    <div class="text-xs text-slate-500">Diterima Tanggal / No. Agenda:</div>
                    <div class="font-semibold">{{ optional($surat->tanggal_terima)->format('d/m/Y') }} · <span class="font-mono">{{ $surat->nomor_agenda }}</span></div>
                </td>
            </tr>
            <tr>
                <td class="border border-slate-800 p-2.5">
                    <div class="text-xs text-slate-500">Nomor Surat:</div>
                    <div class="font-mono font-semibold">{{ $surat->nomor_surat }}</div>
                </td>
                <td class="border border-slate-800 p-2.5">
                    <div class="text-xs text-slate-500">Tanggal Surat / Sifat:</div>
                    <div class="font-semibold">{{ optional($surat->tanggal_surat)->format('d/m/Y') }} · {{ ucfirst($surat->sifat) }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="border border-slate-800 p-2.5">
                    <div class="text-xs text-slate-500">Perihal:</div>
                    <div class="font-semibold">{{ $surat->perihal }}</div>
                </td>
            </tr>
            <tr>
                <td class="border border-slate-800 p-2.5 align-top h-28">
                    <div class="text-xs font-bold uppercase text-slate-700 mb-1">Diteruskan Kepada (Unit Tujuan):</div>
                    <div class="font-semibold text-blue-900">{{ optional($surat->tujuanUnit)->nama ?? '-' }}</div>
                </td>
                <td class="border border-slate-800 p-2.5 align-top h-28">
                    <div class="text-xs font-bold uppercase text-slate-700 mb-1">Isi Disposisi Camat:</div>
                    <div>{{ $surat->tujuan_disposisi ?? ($surat->disposisi ?? '-') }}</div>
                </td>
            </tr>
            <tr>
                <td class="border border-slate-800 p-3">
                    <div class="text-xs text-slate-500 mb-1">Paraf Digital Sekretaris Camat:</div>
                    @if($surat->pemaraf)
                        <div class="font-bold text-emerald-800">✓ {{ $surat->pemaraf->nama }}</div>
                        <div class="text-[11px] text-slate-500">{{ optional($surat->paraf_waktu)->format('d/m/Y H:i') }}</div>
                        <div class="text-[10px] font-mono text-slate-400">Token: {{ Str::limit($surat->paraf_token, 20) }}</div>
                    @else
                        <div class="text-slate-400 italic">Menunggu paraf</div>
                    @endif
                </td>
                <td class="border border-slate-800 p-3">
                    <div class="text-xs text-slate-500 mb-1">Tanda Tangan Digital Camat Tamalate:</div>
                    @if($surat->pendisposisi)
                        <div class="font-bold text-blue-900">✓ {{ $surat->pendisposisi->nama }}</div>
                        <div class="text-[11px] text-slate-500">{{ optional($surat->disposisi_waktu)->format('d/m/Y H:i') }}</div>
                        <div class="text-[10px] font-mono text-slate-400">Token: {{ Str::limit($surat->disposisi_token, 20) }}</div>
                    @else
                        <div class="text-slate-400 italic">Menunggu tanda tangan disposisi</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

