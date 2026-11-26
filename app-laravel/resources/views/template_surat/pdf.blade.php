<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $d['meta']['judul'] }} · Kecamatan Tamalate</title>
    <style>
        @page { margin: 18mm 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #111827; line-height: 1.45; }
        .kop { text-align: center; border-bottom: 3px double #111827; padding-bottom: 8px; margin-bottom: 14px; }
        .kop .pemkot { font-size: 13px; font-weight: bold; letter-spacing: 0.4px; }
        .kop .kec { font-size: 15px; font-weight: bold; margin-top: 2px; }
        .kop .addr { font-size: 9px; color: #374151; margin-top: 2px; }
        .title { text-align: center; font-weight: bold; font-size: 12.5px; text-decoration: underline; margin-top: 6px; }
        .nomor-sub { text-align: center; font-weight: bold; font-size: 10.5px; margin-bottom: 12px; }
        .right { text-align: right; }
        .center { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.bordered th, table.bordered td { border: 1px solid #1f2937; padding: 5px 7px; vertical-align: top; }
        table.plain td { border: none; padding: 2px 4px; vertical-align: top; }
        th.head { background: #e5e7eb; font-weight: bold; text-align: left; }
        .mt-12 { margin-top: 12px; }
        .mb-8 { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="pemkot">PEMERINTAH KOTA MAKASSAR</div>
        <div class="kec">KECAMATAN TAMALATE</div>
        <div class="addr">Jalan Danau Tanjung Bunga No. 1, Makassar 90224 · Pos-el: kecamatantamalate@makassarkota.go.id</div>
    </div>

    @if($jenis === 'cuti')
        <div class="title">FORMULIR PERMINTAAN CUTI PEGAWAI</div>
        <div class="nomor-sub">Nomor: {{ $d['nomor_surat'] }}</div>

        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>
        <p>
            Kepada Yth.<br>
            <strong>Camat Tamalate Kota Makassar</strong><br>
            c.q. Kasubag Umum dan Kepegawaian<br>
            di - Makassar
        </p>

        <p class="mb-8">Saya yang bertanda tangan di bawah ini:</p>
        <table class="bordered">
            <tr><td width="30%">Nama Lengkap</td><td width="3%" class="center">:</td><td>{{ $d['nama_pegawai'] }}</td></tr>
            <tr><td>NIP / NIPPPK</td><td class="center">:</td><td>{{ $d['nip'] }}</td></tr>
            <tr><td>Pangkat / Gol. Ruang</td><td class="center">:</td><td>{{ $d['pangkat_gol'] }}</td></tr>
            <tr><td>Jabatan</td><td class="center">:</td><td>{{ $d['jabatan'] }}</td></tr>
            <tr><td>Satuan Organisasi</td><td class="center">:</td><td>{{ $d['unit_kerja'] }}</td></tr>
        </table>

        <p>
            Dengan ini mengajukan permohonan cuti untuk tahun {{ $d['tahun'] }} selama <strong>{{ $d['lama_hari'] }}</strong> hari kerja,
            terhitung mulai tanggal <strong>{{ $d['tanggal_mulai'] }}</strong> s.d. <strong>{{ $d['tanggal_selesai'] }}</strong>,
            dengan rincian jenis cuti sebagai berikut:
        </p>

        @php
            $mark = static fn (string $lbl) => ($d['mode'] === 'filled' && $d['jenis_cuti'] === $lbl) ? '[ ✓ ]' : '[   ]';
        @endphp
        <table class="bordered">
            <tr>
                <td width="50%">{{ $mark('Cuti Tahunan') }} Cuti Tahunan</td>
                <td width="50%">{{ $mark('Cuti Besar') }} Cuti Besar (Masa kerja &gt; 5 tahun)</td>
            </tr>
            <tr>
                <td>{{ $mark('Cuti Sakit') }} Cuti Sakit (Lampiran Surat Keterangan Dokter)</td>
                <td>{{ $mark('Cuti Alasan Penting') }} Cuti Alasan Penting</td>
            </tr>
            <tr>
                <td>{{ $mark('Cuti Melahirkan') }} Cuti Melahirkan</td>
                <td>{{ $mark('Cuti di Luar Tanggungan Negara (CLTN)') }} Cuti di Luar Tanggungan Negara (CLTN)</td>
            </tr>
        </table>

        <table class="bordered">
            <tr><td width="30%">Alasan Pengajuan Cuti</td><td width="3%" class="center">:</td><td>{{ $d['alasan'] }}</td></tr>
            <tr><td>Nomor Telepon / HP</td><td class="center">:</td><td>{{ $d['no_hp'] }}</td></tr>
        </table>

        <p>Demikian permohonan ini saya buat untuk dapat dipertimbangkan sebagaimana mestinya.</p>

        <table class="bordered mt-12">
            <tr>
                <td width="50%">
                    <strong>Catatan Kasubag Kepegawaian:</strong><br><br>
                    Sisa Hak Cuti: [ {{ $d['sisa_cuti'] }} ] Hari<br><br>
                    Paraf Verifikasi: ..............................
                </td>
                <td width="50%" class="center">
                    Hormat saya,<br>
                    Pemohon Cuti,<br><br><br><br>
                    <u><strong>({{ $d['nama_pegawai'] }})</strong></u><br>
                    NIP. {{ $d['nip'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'kgb')
        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>

        <table class="plain">
            <tr>
                <td width="60%">
                    <table class="plain">
                        <tr><td width="24%">Nomor</td><td width="4%">:</td><td>{{ $d['nomor_surat'] }}</td></tr>
                        <tr><td>Sifat</td><td>:</td><td>Biasa</td></tr>
                        <tr><td>Lampiran</td><td>:</td><td>1 (satu) Berkas</td></tr>
                        <tr><td>Perihal</td><td>:</td><td><strong>Usulan Kenaikan Gaji Berkala (KGB) Pegawai A.n. {{ $d['nama_pegawai'] }}</strong></td></tr>
                    </table>
                </td>
                <td width="40%">
                    Kepada Yth.<br>
                    <strong>Kepala BKPSDMD Kota Makassar</strong><br>
                    di -<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Makassar
                </td>
            </tr>
        </table>

        <p>Dengan hormat, bersama ini kami sampaikan usulan Kenaikan Gaji Berkala (KGB) Pegawai Negeri Sipil / PPPK instansi Kecamatan Tamalate Kota Makassar atas nama:</p>

        <table class="bordered">
            <tr><td width="35%">1. Nama Pegawai</td><td width="3%" class="center">:</td><td>{{ $d['nama_pegawai'] }}</td></tr>
            <tr><td>2. NIP / NIPPPK</td><td class="center">:</td><td>{{ $d['nip'] }}</td></tr>
            <tr><td>3. Pangkat / Gol. Ruang</td><td class="center">:</td><td>{{ $d['pangkat_gol'] }}</td></tr>
            <tr><td>4. Jabatan</td><td class="center">:</td><td>{{ $d['jabatan'] }}</td></tr>
            <tr><td>5. Unit Kerja</td><td class="center">:</td><td>{{ $d['unit_kerja'] }}</td></tr>
            <tr><td>6. Gaji Pokok Lama</td><td class="center">:</td><td>Rp {{ $d['gaji_lama'] }} (SK KGB Terakhir)</td></tr>
            <tr><td>7. TMT KGB Lama</td><td class="center">:</td><td>{{ $d['tmt_kgb_lama'] }}</td></tr>
            <tr><td>8. Gaji Pokok Baru</td><td class="center">:</td><td>Rp {{ $d['gaji_baru'] }}</td></tr>
            <tr><td>9. TMT KGB Baru</td><td class="center">:</td><td>{{ $d['tmt_kgb_baru'] }}</td></tr>
            <tr><td>10. Masa Kerja Golongan</td><td class="center">:</td><td>{{ $d['masa_kerja_tahun'] }} Tahun {{ $d['masa_kerja_bulan'] }} Bulan</td></tr>
        </table>

        <p>
            Sebagai bahan pertimbangan, terlampir disertakan: (1) Fotokopi SK Pangkat Terakhir, (2) Fotokopi SK KGB Terakhir, (3) Fotokopi SKP 2 Tahun Terakhir.<br>
            Demikian pengusulan ini disampaikan, atas perhatiannya diucapkan terima kasih.
        </p>

        <table class="plain mt-12">
            <tr>
                <td width="52%"></td>
                <td width="48%" class="center">
                    <strong>Camat Tamalate Kota Makassar,</strong><br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'spt')
        <div class="title">SURAT PERINTAH TUGAS</div>
        <div class="nomor-sub">NOMOR: {{ $d['nomor_surat'] }}</div>

        <table class="plain">
            <tr>
                <td width="16%"><strong>Dasar</strong></td>
                <td width="3%">:</td>
                <td>
                    1. Peraturan Wali Kota Makassar Nomor 16 Tahun 2017 tentang Tata Naskah Dinas.<br>
                    2. Surat {{ $d['dasar_instansi'] }} Nomor: {{ $d['dasar_nomor'] }} Tanggal {{ $d['dasar_tanggal'] }} Hal {{ $d['dasar_perihal'] }}.
                </td>
            </tr>
        </table>

        <p class="center"><strong>MEMERINTAHKAN:</strong></p>

        <table class="bordered">
            <thead>
                <tr>
                    <th class="head center" width="6%">No</th>
                    <th class="head" width="27%">Nama Pegawai</th>
                    <th class="head" width="24%">NIP / NIPPPK</th>
                    <th class="head" width="16%">Pangkat / Gol.</th>
                    <th class="head" width="27%">Jabatan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td>{{ $d['nama_pegawai'] }}</td>
                    <td>{{ $d['nip'] }}</td>
                    <td>{{ $d['pangkat_gol'] }}</td>
                    <td>{{ $d['jabatan'] }}</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td>{{ $d['pegawai2_nama'] }}</td>
                    <td>{{ $d['pegawai2_nip'] }}</td>
                    <td>{{ $d['pegawai2_gol'] }}</td>
                    <td>{{ $d['pegawai2_jabatan'] }}</td>
                </tr>
            </tbody>
        </table>

        <table class="plain">
            <tr>
                <td width="16%"><strong>Untuk</strong></td>
                <td width="3%">:</td>
                <td>
                    1. Melaksanakan {{ $d['maksud_tugas'] }} yang bertempat di {{ $d['lokasi_tugas'] }} pada tanggal {{ $d['tanggal_tugas'] }}.<br>
                    2. Melaporkan hasil pelaksanaan penugasan kepada Camat Tamalate setelah kegiatan selesai dilaksanakan.<br>
                    3. Melaksanakan perintah ini dengan penuh rasa tanggung jawab.
                </td>
            </tr>
        </table>

        <table class="plain mt-12">
            <tr>
                <td width="52%"></td>
                <td width="48%" class="center">
                    Ditetapkan di Makassar<br>
                    Pada tanggal {{ $d['tanggal_surat'] }}<br><br>
                    <strong>Camat Tamalate Kota Makassar,</strong><br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @else
        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>

        <table class="plain">
            <tr>
                <td width="60%">
                    <table class="plain">
                        <tr><td width="24%">Nomor</td><td width="4%">:</td><td>{{ $d['nomor_surat'] }}</td></tr>
                        <tr><td>Sifat</td><td>:</td><td>Biasa</td></tr>
                        <tr><td>Lampiran</td><td>:</td><td>1 (satu) Berkas</td></tr>
                        <tr>
                            <td>Perihal</td><td>:</td>
                            <td>
                                <strong>
                                    {{ $jenis === 'kenaikan_pangkat'
                                        ? "Usulan Kenaikan Pangkat PNS A.n. {$d['nama_pegawai']}"
                                        : "Usulan Pemberhentian / Pensiun PNS ({$d['jenis_pensiun']}) A.n. {$d['nama_pegawai']}" }}
                                </strong>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%">
                    Kepada Yth.<br>
                    <strong>Kepala BKPSDMD Kota Makassar</strong><br>
                    di -<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Makassar
                </td>
            </tr>
        </table>

        <p>Dengan hormat, bersama ini kami teruskan berkas usulan administrasi kepegawaian Kecamatan Tamalate atas nama:</p>

        <table class="bordered">
            <tr><td width="35%">1. Nama Pegawai</td><td width="3%" class="center">:</td><td>{{ $d['nama_pegawai'] }}</td></tr>
            <tr><td>2. NIP</td><td class="center">:</td><td>{{ $d['nip'] }}</td></tr>
            <tr><td>3. Pangkat / Gol. Saat Ini</td><td class="center">:</td><td>{{ $d['pangkat_gol'] }}</td></tr>
            <tr><td>4. Jabatan</td><td class="center">:</td><td>{{ $d['jabatan'] }}</td></tr>
            <tr><td>5. Unit Kerja</td><td class="center">:</td><td>{{ $d['unit_kerja'] }}</td></tr>
            @if($jenis === 'kenaikan_pangkat')
                <tr><td>6. Golongan Diusulkan</td><td class="center">:</td><td>{{ $d['golongan_usulan'] }}</td></tr>
                <tr><td>7. TMT Pangkat Diusulkan</td><td class="center">:</td><td>{{ $d['tmt_usulan'] }}</td></tr>
            @else
                <tr><td>6. Jenis Pensiun</td><td class="center">:</td><td>{{ $d['jenis_pensiun'] }}</td></tr>
                <tr><td>7. TMT Pensiun</td><td class="center">:</td><td>{{ $d['tmt_usulan'] }}</td></tr>
            @endif
        </table>

        <p>
            Sebagai bahan pertimbangan, terlampir disertakan berkas persyaratan sesuai ketentuan:<br>
            {{ implode('; ', $d['meta']['persyaratan']) }}.
        </p>

        <table class="plain mt-12">
            <tr>
                <td width="52%"></td>
                <td width="48%" class="center">
                    <strong>Camat Tamalate Kota Makassar,</strong><br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
