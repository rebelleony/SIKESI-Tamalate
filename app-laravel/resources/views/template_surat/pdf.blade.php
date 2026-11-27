<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $d['meta']['judul'] }} · Kecamatan Tamalate</title>
    <style>
        @page { margin: 16mm 18mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; line-height: 1.45; }
        .kop { text-align: center; border-bottom: 3px double #111827; padding-bottom: 7px; margin-bottom: 12px; }
        .kop .pemkot { font-size: 12.5px; font-weight: bold; letter-spacing: 0.4px; }
        .kop .kec { font-size: 15px; font-weight: bold; margin-top: 1px; }
        .kop .addr { font-size: 8.5px; color: #374151; margin-top: 2px; }
        .title { text-align: center; font-weight: bold; font-size: 12px; text-decoration: underline; margin-top: 4px; }
        .nomor-sub { text-align: center; font-weight: bold; font-size: 10px; margin-bottom: 10px; }
        .right { text-align: right; }
        .center { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.bordered th, table.bordered td { border: 1px solid #1f2937; padding: 4px 6px; vertical-align: top; }
        table.plain td { border: none; padding: 2px 4px; vertical-align: top; }
        th.head { background: #e5e7eb; font-weight: bold; text-align: left; }
        .mt-10 { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="pemkot">PEMERINTAH KOTA MAKASSAR</div>
        <div class="kec">KECAMATAN TAMALATE</div>
        <div class="addr">Jalan Danau Tanjung Bunga Utara No. 181 Makassar 90224 · Telp. +62411 - 879 249</div>
        <div class="addr">Email : tamalate.bekerja22@gmail.com · Home page : http://www.kectamalate.com</div>
    </div>

    @if($jenis === 'cuti')
        <table class="plain">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    Makassar, {{ $d['tanggal_surat'] }}<br><br>
                    Kepada<br>
                    Yth. Wali Kota Makassar<br>
                    Cq. Kepala Badan Kepegawaian dan<br>
                    Pengembangan SDM Daerah Kota Makassar<br>
                    Di - Makassar
                </td>
            </tr>
        </table>

        <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
        <div class="nomor-sub">Nomor: {{ $d['nomor_surat'] }}</div>

        <table class="bordered">
            <tr><th colspan="4" class="head">I. DATA PEGAWAI</th></tr>
            <tr>
                <td width="16%">Nama</td><td width="34%">{{ $d['nama_pegawai'] }}</td>
                <td width="16%">NIP</td><td width="34%">{{ $d['nip'] }}</td>
            </tr>
            <tr>
                <td>Jabatan</td><td>{{ $d['jabatan'] }}</td>
                <td>Masa Kerja</td><td>{{ $d['masa_kerja_teks'] }}</td>
            </tr>
            <tr>
                <td>Unit Kerja</td><td>{{ $d['unit_kerja'] }}</td>
                <td>Pangkat / Gol.</td><td>{{ $d['pangkat_gol'] }}</td>
            </tr>
        </table>

        @php
            $mark = static fn (string $lbl) => ($d['mode'] === 'filled' && $d['jenis_cuti'] === $lbl) ? '[ ✓ ]' : '[   ]';
        @endphp
        <table class="bordered">
            <tr><th colspan="2" class="head">II. JENIS CUTI YANG DIAMBIL</th></tr>
            @foreach(array_chunk($d['jenis_cuti_list'], 2) as $rowIndex => $row)
                <tr>
                    @foreach($row as $columnIndex => $label)
                        <td width="50%">{{ $mark($label) }} {{ $rowIndex * 2 + $columnIndex + 1 }}. {{ $label }}</td>
                    @endforeach
                    @if(count($row) === 1)<td width="50%"></td>@endif
                </tr>
            @endforeach
        </table>

        <table class="bordered">
            <tr><th class="head">III. ALASAN CUTI</th></tr>
            <tr><td>{{ $d['alasan'] }}</td></tr>
        </table>

        <table class="bordered">
            <tr><th class="head">III. ALASAN CUTI</th></tr>
            <tr><td>{{ $d['alasan'] }}</td></tr>
        </table>

        <table class="bordered">
            <tr><th colspan="6" class="head">IV. LAMA CUTI</th></tr>
            <tr>
                <td width="12%">Selama</td><td width="20%">{{ $d['lama_hari'] }}</td>
                <td width="18%">Mulai Tanggal</td><td width="20%">{{ $d['tanggal_mulai'] }}</td>
                <td width="8%" class="center">s/d</td><td width="22%">{{ $d['tanggal_selesai'] }}</td>
            </tr>
        </table>

        <table class="bordered">
            <tr><th colspan="5" class="head">V. CATATAN CUTI</th></tr>
            <tr>
                <td colspan="3" width="52%"><strong>1. CUTI TAHUNAN</strong></td>
                <td width="32%">2. CUTI BESAR</td><td width="16%"></td>
            </tr>
            <tr>
                <td width="14%">Tahun</td><td width="12%">Sisa</td><td width="26%">Keterangan</td>
                <td>3. CUTI SAKIT</td><td></td>
            </tr>
            <tr>
                <td>{{ $d['tahun_cuti'] }}</td><td>{{ $d['sisa_cuti'] }} Hari</td><td>{{ $d['keterangan_cuti'] }}</td>
                <td>4. CUTI MELAHIRKAN</td><td></td>
            </tr>
            <tr>
                <td></td><td></td><td></td>
                <td>5. CUTI KARENA ALASAN PENTING</td><td></td>
            </tr>
            <tr>
                <td></td><td></td><td></td>
                <td>6. CUTI DILUAR TANGGUNGAN NEGARA</td><td></td>
            </tr>
        </table>

        <table class="bordered">
            <tr><th colspan="3" class="head">VI. ALAMAT SELAMA MENJALANKAN CUTI</th></tr>
            <tr>
                <td width="55%">Alamat: {{ $d['alamat_cuti'] }}<br>Telp / HP: {{ $d['no_hp'] }}</td>
                <td width="45%" class="center">
                    Hormat Saya,<br><br><br>
                    <u><strong>{{ $d['nama_pegawai'] }}</strong></u><br>
                    NIP. {{ $d['nip'] }}
                </td>
            </tr>
        </table>

        <table class="bordered">
            <tr><th colspan="4" class="head">VII. PERTIMBANGAN ATASAN LANGSUNG</th></tr>
            <tr>
                <td width="25%">DISETUJUI</td><td width="25%">PERUBAHAN</td><td width="25%">DITANGGUHKAN</td><td width="25%">TIDAK DISETUJUI</td>
            </tr>
            <tr>
                <td colspan="2" style="height:44px;"></td>
                <td colspan="2" class="center">
                    {{ $d['atasan_jabatan'] }}<br><br><br>
                    <u><strong>{{ $d['atasan_nama'] }}</strong></u><br>
                    NIP. {{ $d['atasan_nip'] }}
                </td>
            </tr>
        </table>

        <table class="bordered">
            <tr><th colspan="4" class="head">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</th></tr>
            <tr>
                <td width="25%">DISETUJUI</td><td width="25%">PERUBAHAN</td><td width="25%">DITANGGUHKAN</td><td width="25%">TIDAK DISETUJUI</td>
            </tr>
            <tr>
                <td colspan="2" style="height:44px;"></td>
                <td colspan="2" class="center">
                    Camat Tamalate<br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'pengantar')
        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>
        <p>
            Yth. <strong>{{ $d['tujuan_surat'] }}</strong><br>
            Di -<br>
            &nbsp;&nbsp;&nbsp;&nbsp;Makassar
        </p>

        <div class="title">SURAT PENGANTAR</div>
        <div class="nomor-sub">Nomor : {{ $d['nomor_surat'] }}</div>

        <table class="bordered">
            <thead>
                <tr>
                    <th class="head center" width="7%">NO</th>
                    <th class="head" width="46%">JENIS DOKUMEN YANG DIKIRIM</th>
                    <th class="head center" width="20%">BANYAKNYA</th>
                    <th class="head" width="27%">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td>{!! nl2br(e($d['dokumen_dikirim'])) !!}</td>
                    <td class="center">{{ $d['banyaknya'] }}</td>
                    <td>{{ $d['ket_pengantar'] }}</td>
                </tr>
            </tbody>
        </table>

        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    {!! nl2br(e($d['jabatan_ttd'])) !!}<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP: {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'fasilitasi')
        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>

        <table class="plain">
            <tr>
                <td width="60%">
                    <table class="plain">
                        <tr><td width="24%">Nomor</td><td width="4%">:</td><td>{{ $d['nomor_surat'] }}</td></tr>
                        <tr><td>Sifat</td><td>:</td><td>{{ $d['sifat_surat'] }}</td></tr>
                        <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
                        <tr><td>Hal</td><td>:</td><td><strong>{{ $d['hal_surat'] }}</strong></td></tr>
                    </table>
                </td>
                <td width="40%">
                    Yth. <strong>{{ $d['tujuan_surat'] }}</strong><br>
                    di<br>
                    Makassar
                </td>
            </tr>
        </table>

        <p>Dengan hormat,</p>
        <p>
            Dalam rangka pelaksanaan {{ $d['kegiatan_rangka'] }}, kami memohon kesediaan Bapak/Ibu untuk memfasilitasi penggunaan {{ $d['tempat_kegiatan'] }} untuk pelaksanaan kegiatan yang akan diselenggarakan pada:
        </p>

        <table class="plain" style="margin-left:16px;">
            <tr><td width="22%">Hari, tanggal</td><td width="4%">:</td><td>{{ $d['hari_tanggal'] }}</td></tr>
            <tr><td>Waktu</td><td>:</td><td>{{ $d['waktu_kegiatan'] }}</td></tr>
            <tr><td>Jumlah Peserta</td><td>:</td><td>{{ $d['jumlah_peserta'] }}</td></tr>
        </table>

        <p>
            Sehubungan dengan hal tersebut, kami memohon kesediaan Bapak/Ibu untuk memberikan izin penggunaan tempat guna mendukung kelancaran pelaksanaan kegiatan dimaksud.<br>
            Demikian permohonan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.
        </p>

        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    {!! nl2br(e($d['jabatan_ttd'])) !!}<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'undangan')
        <p class="right">Makassar, {{ $d['tanggal_surat'] }}</p>

        <table class="plain">
            <tr>
                <td width="60%">
                    <table class="plain">
                        <tr><td width="24%">Nomor</td><td width="4%">:</td><td>{{ $d['nomor_surat'] }}</td></tr>
                        <tr><td>Sifat</td><td>:</td><td>{{ $d['sifat_surat'] }}</td></tr>
                        <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
                        <tr><td>Hal</td><td>:</td><td><strong>{{ $d['hal_surat'] }}</strong></td></tr>
                    </table>
                </td>
                <td width="40%">
                    Yth. <strong>{{ $d['tujuan_surat'] }}</strong><br>
                    di<br>
                    Makassar
                </td>
            </tr>
        </table>

        <p>
            {{ $d['kegiatan_rangka'] }}, dengan ini kami mengundang Bapak/Ibu/Saudara(i) untuk menghadiri acara yang akan dilaksanakan pada:
        </p>

        <table class="plain" style="margin-left:16px;">
            <tr><td width="22%">Hari, tanggal</td><td width="4%">:</td><td>{{ $d['hari_tanggal'] }}</td></tr>
            <tr><td>Waktu</td><td>:</td><td>{{ $d['waktu_kegiatan'] }}</td></tr>
            <tr><td>Tempat</td><td>:</td><td>{{ $d['tempat_kegiatan'] }}</td></tr>
            <tr><td>Acara</td><td>:</td><td>{{ $d['acara_kegiatan'] }}</td></tr>
        </table>

        <p>
            Sehubungan dengan hal tersebut, {{ $d['pesan_penutup'] }}<br>
            Demikian disampaikan, atas perhatian dan kehadiran Bapak/Ibu diucapkan terima kasih.
        </p>

        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    {!! nl2br(e($d['jabatan_ttd'])) !!}<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
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
                        <tr><td>Perihal</td><td>:</td><td><strong>Usulan KGB Pegawai A.n. {{ $d['nama_pegawai'] }}</strong></td></tr>
                    </table>
                </td>
                <td width="40%">Kepada Yth.<br><strong>Kepala BKPSDMD Kota Makassar</strong><br>di - Makassar</td>
            </tr>
        </table>
        <p>Dengan hormat, bersama ini kami sampaikan usulan Kenaikan Gaji Berkala (KGB) atas nama:</p>
        <table class="bordered">
            <tr><td width="35%">1. Nama Pegawai</td><td>{{ $d['nama_pegawai'] }}</td></tr>
            <tr><td>2. NIP / NIPPPK</td><td>{{ $d['nip'] }}</td></tr>
            <tr><td>3. Pangkat / Gol. Ruang</td><td>{{ $d['pangkat_gol'] }}</td></tr>
            <tr><td>4. Jabatan</td><td>{{ $d['jabatan'] }}</td></tr>
            <tr><td>5. Unit Kerja</td><td>{{ $d['unit_kerja'] }}</td></tr>
            <tr><td>6. Gaji Pokok Lama</td><td>Rp {{ $d['gaji_lama'] }}</td></tr>
            <tr><td>7. TMT KGB Lama</td><td>{{ $d['tmt_kgb_lama'] }}</td></tr>
            <tr><td>8. Gaji Pokok Baru</td><td>Rp {{ $d['gaji_baru'] }}</td></tr>
            <tr><td>9. TMT KGB Baru</td><td>{{ $d['tmt_kgb_baru'] }}</td></tr>
            <tr><td>10. Masa Kerja Golongan</td><td>{{ $d['masa_kerja_tahun'] }} Tahun {{ $d['masa_kerja_bulan'] }} Bulan</td></tr>
        </table>
        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    Camat Tamalate,<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>

    @elseif($jenis === 'spt')
        <div class="title">SURAT PERINTAH TUGAS</div>
        <div class="nomor-sub">NOMOR: {{ $d['nomor_surat'] }}</div>
        <p><strong>Dasar:</strong> 1. Perwali Makassar No. 16 Tahun 2017. 2. Surat {{ $d['dasar_instansi'] }} Nomor: {{ $d['dasar_nomor'] }}.</p>
        <p class="center"><strong>MEMERINTAHKAN:</strong></p>
        <table class="bordered">
            <thead>
                <tr><th class="head center" width="6%">No</th><th class="head">Nama Pegawai</th><th class="head">NIP / NIPPPK</th><th class="head">Gol.</th><th class="head">Jabatan</th></tr>
            </thead>
            <tbody>
                <tr><td class="center">1</td><td>{{ $d['nama_pegawai'] }}</td><td>{{ $d['nip'] }}</td><td>{{ $d['pangkat_gol'] }}</td><td>{{ $d['jabatan'] }}</td></tr>
                <tr><td class="center">2</td><td>{{ $d['pegawai2_nama'] }}</td><td>{{ $d['pegawai2_nip'] }}</td><td>{{ $d['pegawai2_gol'] }}</td><td>{{ $d['pegawai2_jabatan'] }}</td></tr>
            </tbody>
        </table>
        <p><strong>Untuk:</strong> 1. Melaksanakan {{ $d['maksud_tugas'] }} di {{ $d['lokasi_tugas'] }} pada tanggal {{ $d['tanggal_tugas'] }}. 2. Melaporkan hasilnya kepada Camat Tamalate.</p>
        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    Ditetapkan di Makassar pada tanggal {{ $d['tanggal_surat'] }}<br>
                    Camat Tamalate,<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
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
                        <tr><td>Perihal</td><td>:</td><td><strong>{{ $d['meta']['judul'] }} A.n. {{ $d['nama_pegawai'] }}</strong></td></tr>
                    </table>
                </td>
                <td width="40%">Kepada Yth.<br><strong>Kepala BKPSDMD Kota Makassar</strong><br>di - Makassar</td>
            </tr>
        </table>
        <table class="bordered">
            <tr><td width="35%">1. Nama Pegawai</td><td>{{ $d['nama_pegawai'] }}</td></tr>
            <tr><td>2. NIP</td><td>{{ $d['nip'] }}</td></tr>
            <tr><td>3. Pangkat / Gol.</td><td>{{ $d['pangkat_gol'] }}</td></tr>
            <tr><td>4. Jabatan</td><td>{{ $d['jabatan'] }}</td></tr>
            <tr><td>5. Unit Kerja</td><td>{{ $d['unit_kerja'] }}</td></tr>
            @if($jenis === 'kenaikan_pangkat')
                <tr><td>6. Golongan Diusulkan</td><td>{{ $d['golongan_usulan'] }}</td></tr>
                <tr><td>7. TMT Pangkat Diusulkan</td><td>{{ $d['tmt_usulan'] }}</td></tr>
            @else
                <tr><td>6. Jenis Pensiun</td><td>{{ $d['jenis_pensiun'] }}</td></tr>
                <tr><td>7. TMT Pensiun</td><td>{{ $d['tmt_usulan'] }}</td></tr>
            @endif
        </table>
        <table class="plain mt-10">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="center">
                    Camat Tamalate,<br><br><br><br>
                    <u><strong>{{ $d['nama_camat'] }}</strong></u><br>
                    {{ $d['pangkat_camat'] }}<br>
                    NIP. {{ $d['nip_camat'] }}
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
