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
    'alamat'         => env('DOKUMEN_ALAMAT', 'Kota Makassar, Sulawesi Selatan'),
    'telepon'        => env('DOKUMEN_TELEPON'),
    'email'          => env('DOKUMEN_EMAIL'),
    'kode_pos'       => env('DOKUMEN_KODE_POS'),
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
