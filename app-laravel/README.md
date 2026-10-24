# Prototype — SI Kepegawaian & Presensi Terpadu (Laravel + Blade)

Kode aplikasi ditulis **from scratch** oleh tim (bukan template/boilerplate jadi).
Framework Laravel dipakai sebagai kerangka dasar saja.

> Prototype ini mencakup alur inti: **Login + RBAC → Pengajuan (Cuti, KGB, Kenaikan Pangkat, Pensiun) → Verifikasi → Approval Berjenjang → Presensi Apel → Surat Masuk → Arsip → Laporan & Rekapitulasi**.
> Alur Surat Masuk mengikuti SOP: catat agenda → paraf Sekcam → disposisi Camat → distribusi → arsip.
> Arsip menyimpan kode tata naskah, status dokumen (aktif/tidak aktif/penting), lokasi (lemari/gudang/lurah),
> dan nomor Daftar Arsip yang ditandatangani Sekcam/Camat.
> Modul KGB / Kenaikan Pangkat / Pensiun memakai aturan bisnis sendiri (lihat `app/Support/LayananKepegawaian.php`):
> KGB = TMT golongan + 24 bulan; Kenaikan Pangkat = minimal 24 bulan di pangkat terakhir & usulan satu tingkat di atas;
> Pensiun = TMT dihitung dari tanggal lahir + Batas Usia Pensiun (58/60).
> Pengajuan yang sudah **selesai** dapat dicetak menjadi **SK / surat izin resmi (PDF)** lewat `app/Support/DokumenKepegawaian.php`
> + dompdf; kop surat & pejabat penandatangan diatur di `config/dokumen.php` (isi alamat/telepon/logo asli via `.env`:
> `DOKUMEN_ALAMAT`, `DOKUMEN_TELEPON`, `DOKUMEN_LOGO`, `DOKUMEN_TTD_NAMA`, `DOKUMEN_TTD_NIP`).
> **Data Master (UC-03)** khusus super admin di `/admin`: kelola akun Pegawai (cari/tambah/edit/nonaktifkan,
> set peran & atasan, reset sandi), Unit Kerja (kecamatan/kelurahan/seksi/subbag + hierarki induk), dan Peran.
> Pengaman: tidak bisa menonaktifkan/menurunkan akun sendiri atau super admin aktif terakhir; unit/peran yang masih
> dipakai tidak bisa dihapus. Username pegawai default = NIP bila dikosongkan.
> **Profil Saya (UC-02)** di `/profil` untuk semua aktor: identitas master baca-saja, ubah kontak/email/alamat/foto,
> dan ganti kata sandi mandiri (wajib cocokkan sandi lama).

---

## Prasyarat

Folder ini sekarang **proyek Laravel 12 utuh** (skeleton + kode tim), jadi tidak perlu
`composer create-project` lagi. Cukup pasang toolchain:
- **Laragon** (Windows, paling mudah — sudah termasuk PHP, Composer, MySQL), atau
- **XAMPP** + Composer terpisah, atau PHP 8.2+ & Composer 2+ saja.

Versi minimal: **PHP 8.2+**, **Composer 2+**. Untuk database pilih salah satu:
- **SQLite** (paling cepat, tanpa install apa pun — default di `.env.example`), atau
- **MySQL 8 / MariaDB 10.6+**.

## Cara Setup

1. Install dependency PHP:
   ```bash
   cd app-laravel
   composer install
   ```
2. Buat `.env` dari contoh, lalu generate app key:
   ```bash
   cp .env.example .env        # Windows: copy .env.example .env
   php artisan key:generate
   ```
3. Pilih database:
   - **SQLite (default):** `.env` sudah `DB_CONNECTION=sqlite` dan file
     `database/database.sqlite` sudah disediakan — tidak perlu diubah.
   - **MySQL:** di `.env` comment bagian SQLite, buka bagian MySQL, isi
     `DB_DATABASE/DB_USERNAME/DB_PASSWORD`, lalu buat DB-nya:
     `CREATE DATABASE si_kepegawaian_tamalate;`
4. Jalankan migrasi + seeder, lalu link storage:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
5. Jalankan server:
   ```bash
   php artisan serve
   ```
6. Buka `http://localhost:8000` dan login dengan akun demo di bawah.

> Semua akun demo memakai kata sandi `password`.
> Catatan: karena PHP/Composer/MySQL tidak terpasang di mesin tempat kode ini
> dibuat, proyek **belum diuji runtime** di sana — jalankan `composer install`
> lalu `php artisan migrate --seed` di komputer Anda untuk memverifikasi.

## Akun Demo (hasil seeder)

| Peran | Username | Password |
|---|---|---|
| Super Admin | `admin` | `password` |
| Verifikator (staf arsip) | `verifikator` | `password` |
| Kasi/Kasubag | `kasi` | `password` |
| Sekretaris Camat | `seksam` | `password` |
| Camat | `camat` | `password` |
| Pegawai | `pegawai` | `password` |

> Akun di atas adalah **data demo/anonim** untuk pengujian. Data pegawai asli
> (PPPK Paruh Waktu, ±199 baris) **tidak di-commit** ke repo — lihat bagian impor.

## Impor Data Pegawai Asli (Excel)

Data pegawai asli mengandung PII berat (NIP, KTP, NPWP, alamat, telepon, email).
Karena itu berkasnya **sengaja di-gitignore** dan hanya dibaca saat runtime.

1. Taruh berkas `.xlsx` di folder yang diabaikan git, mis.:
   ```
   storage/app/imports/DATA PEGAWAI PPPK PW.xlsx
   ```
2. Pastikan migrasi profil sudah jalan (`2026_01_01_000002_add_profile_fields_to_user`)
   dan seeder role sudah diisi (`php artisan db:seed`).
3. Uji dulu tanpa menulis (disarankan):
   ```bash
   php artisan pegawai:import "storage/app/imports/DATA PEGAWAI PPPK PW.xlsx" --jenis=PPPK_PARUH_WAKTU --dry-run
   ```
4. Jalankan impor sungguhan:
   ```bash
   php artisan pegawai:import "storage/app/imports/DATA PEGAWAI PPPK PW.xlsx" --jenis=PPPK_PARUH_WAKTU
   ```

Perilaku importer:
- Header terdeteksi otomatis (baris berisi kolom **NIP** & **Nama**; di berkas mitra = baris 5).
- **Upsert berdasarkan NIP** — aman dijalankan berulang.
- Semua 26 kolom dipetakan ke tabel `user` (tempat/TTL, eselon, TMT golongan/jabatan/pegawai,
  masa kerja tahun+bulan, jenis kelamin, agama, status perkawinan, pendidikan, No Askes,
  NPWP, KTP, alamat, telp, HP, email, unit kerja).
- `jenis_pegawai` diturunkan dari kolom **Status Pegawai**. Karena berkas mitra hanya
  menulis "PPPK" (tanpa keterangan paruh/penuh), **pakai `--jenis=PPPK_PARUH_WAKTU`**
  untuk berkas PPPK PW ini agar tidak salah jadi `PPPK_PENUH`. PPPK Paruh Waktu di sistem
  hanya berhak **presensi apel**, bukan cuti/KGB/pangkat/pensiun.
- Akun baru: `role = pegawai`, `username = NIP`, kata sandi default `password`
  (ubah lewat `--password=`). `unit_kerja_id` dicocokkan otomatis bila nama unit ada di master.

> **Privasi:** jangan pernah menaruh/meng-commit berkas Excel asli atau hasil dump
> berisi PII ke repo. `.gitignore` sudah memblokir `*.xlsx`, `*.xls`, dan
> `storage/app/imports/`. Seeder yang di-commit hanya berisi akun demo anonim.

## Middleware Role (Laravel 12)

Alias middleware `role` **sudah didaftarkan** di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

## Struktur Alur Status Pengajuan (rantai berjenjang sesuai SOP)

```
draf → menunggu_verifikasi → (perlu_revisi ↔ menunggu_verifikasi)
     → menunggu_approval_tier1   (Kasi/Kasubag)
     → menunggu_approval_tier2   (Sekretaris Camat: paraf)
     → menunggu_approval_final   (Camat/Lurah: tanda tangan)
     → menunggu_pengiriman       (registrasi & kirim ke BKPSDMD oleh staf)
     → dikirim_bkpsdm / ditolak_tierX
```

Presensi apel dicatat **dua sesi per hari** — pagi (07.00–08.30) dan sore
(15.30–17.00 WITA) — dan direkap otomatis **per pekan**. Satu record unik per
pegawai per sesi per hari.

## Catatan Keamanan (sudah diterapkan)

- Password di-hash dengan Bcrypt (`Hash::make`).
- Proteksi CSRF pada seluruh form (`@csrf`).
- Otorisasi berbasis role di setiap controller + middleware.
- File berkas persyaratan disimpan pada disk `local` (`storage/app/private`) yang **tidak dapat diakses publik**; pengunduhan lewat route terautentikasi `berkas.download`.
- Foto presensi apel disimpan pada disk `public` (`storage:link`) agar dapat ditampilkan pada halaman.
- Validasi tipe & ukuran berkas pada sisi server.
