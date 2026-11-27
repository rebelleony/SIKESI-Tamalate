<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kop Surat / Kepala Dokumen
    |--------------------------------------------------------------------------
    |
    | Identitas instansi yang tampil di kepala SK & surat. Isi alamat dan nomor
    | telepon yang benar pada file .env (DOKUMEN_ALAMAT, dst.) sebelum dokumen
    | dipakai secara resmi. Nilai bawaan sengaja generik agar tidak memuat data
    | yang belum terverifikasi.
    |
    */

    'instansi_induk' => env('DOKUMEN_INSTANSI_INDUK', 'PEMERINTAH KOTA MAKASSAR'),
    'instansi'       => env('DOKUMEN_INSTANSI', 'KECAMATAN TAMALATE'),
    'alamat'         => env('DOKUMEN_ALAMAT', 'Jl. Danau Tanjung Bunga No. 181, Kel. Maccini Sombala, Kec. Tamalate, Kota Makassar 90224'),
    'telepon'        => env('DOKUMEN_TELEPON', '+62411 - 879 249'),
    'email'          => env('DOKUMEN_EMAIL', 'tamalate.bekerja22@gmail.com'),
    'kode_pos'       => env('DOKUMEN_KODE_POS', '90224'),
    'homepage'       => env('DOKUMEN_HOMEPAGE', 'http://www.kectamalate.com'),
    'logo'           => env('DOKUMEN_LOGO'),

    // Kota tempat dokumen ditandatangani (untuk baris tanggal "... , 9 Oktober 2026").
    'kota_ttd' => env('DOKUMEN_KOTA_TTD', 'Makassar'),

    /*
    | Jabatan & nama pejabat penandatangan. Bila kosong, sistem memakai user
    | dengan peran camat_lurah yang ada di basis data.
    */
    'penandatangan' => [
        'jabatan' => env('DOKUMEN_TTD_JABATAN', 'CAMAT TAMALATE'),
        'nama'    => env('DOKUMEN_TTD_NAMA'),
        'nip'     => env('DOKUMEN_TTD_NIP'),
    ],

];
