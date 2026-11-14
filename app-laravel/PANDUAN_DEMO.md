# Panduan Demo & Presentasi — SIP-TAMALATE
**Sistem Informasi Pelayanan Kepegawaian, Persuratan & Presensi Terpadu Kecamatan Tamalate, Kota Makassar**

---

## 1. Persiapan Data Demo

Jalankan perintah berikut di dalam folder `app-laravel` untuk mengisi akun demo dan data transaksi realistis (idempotent, aman dijalankan ulang):

```bash
php artisan migrate --force
php artisan db:seed --class=DemoTamalateSeeder
```

Untuk memverifikasi ulang seluruh aturan RBAC (41 pengujian):

```bash
php artisan rbac:verify
```

---

## 2. Daftar Akun Login Demo

Seluruh akun demo menggunakan **Kode Unik Demo non-sensitif** (sebagai pengganti NIP asli) atau **Username**, dengan kata sandi seragam: **`password`**.

| No | Peran / Jabatan Demo | Kode Login (NIP Demo) | Username | Password | Role RBAC | Status Pegawai | Unit Kerja |
|---|---|---|---|---|---|---|---|
| 1 | **Camat Tamalate** | `CAMAT-01` | `camat.tamalate` | `password` | `pimpinan` | PNS | Kecamatan Tamalate |
| 2 | **Lurah Maccini Sombala** | `LURAH-01` | `lurah.maccini` | `password` | `pimpinan` | PNS | Kelurahan Maccini Sombala |
| 3 | **Sekretaris Camat (Sekcam)** | `SEKCAM-01` | `sekcam.tamalate` | `password` | `verifikator_pimpinan` | PNS | Kecamatan Tamalate |
| 4 | **Kasubag Umum & Kepegawaian** | `KASUBAG-01` | `kasubag.umum` | `password` | `verifikator_pimpinan` | PNS | Subbag Umum & Kepegawaian |
| 5 | **Operator Kepegawaian & Arsip Kec.** | `OP-KEC-01` | `operator.kec` | `password` | `operator` | PNS | Subbag Umum & Kepegawaian |
| 6 | **Operator Kelurahan Maccini Sombala** | `OP-KEL-01` | `operator.kel` | `password` | `operator` | PNS | Kelurahan Maccini Sombala |
| 7 | **Pegawai PNS Kecamatan** | `PEG-KEC-01` | `pegawai.kec` | `password` | `pegawai` | PNS | Seksi Pemerintahan |
| 8 | **Pegawai PNS Kelurahan** | `PEG-KEL-01` | `pegawai.kel` | `password` | `pegawai` | PNS | Kelurahan Maccini Sombala |
| 9 | **Pegawai PPPK Paruh Waktu** | `PPPK-PW-01` | `pppk.paruhwaktu` | `password` | `pegawai` | `PPPK_PARUH_WAKTU` | Seksi Ketentraman & Ketertiban Umum |
| 10 | **Super Admin** | `ADMIN-01` | `admin.tamalate` | `password` | `super_admin` | PNS | Subbag Umum & Kepegawaian |

---

## 3. Skenario Urutan Pengujian Saat Presentasi / Expo

### Tahap 1 — Pembatasan Hak Akses `PPPK_PARUH_WAKTU` (1 Menit)
1. Login sebagai **Pegawai PPPK Paruh Waktu** (`PPPK-PW-01` / `password`).
2. Tunjukkan pada bilah navigasi bahwa menu **Kepegawaian (Cuti, KGB, Pangkat, Pensiun)** dan tombol ajukan layanan tidak ditampilkan.
3. Uji coba akses langsung ke URL `/kepegawaian` atau `/kepegawaian/create` — sistem memblokir dengan **HTTP 403 (Akses Ditolak)**.
4. Buka menu **Presensi Apel** (`/presensi`), aktifkan **Mode Testing** (bila di luar jam apel melalui akun Operator) atau tunjukkan antarmuka kamera mobile `capture="environment"` dan pengajuan Izin/DL.

### Tahap 2 — Layanan Kepegawaian: Alur Kelurahan vs Kecamatan (3 Menit)
1. **Alur Kelurahan (`KEP-DEMO-KEL-001`)**:
   - Login sebagai **Lurah Maccini Sombala** (`LURAH-01`), buka `/kepegawaian`.
   - Tunjukkan pengajuan Cuti Tahunan dari Pegawai Kelurahan yang sudah lolos Verifikasi Operator Kelurahan dan sedang berada di meja **Lurah**.
   - Klik **Setujui** — status otomatis berpindah ke **Antrean Operator Subbag Umum & Kepegawaian Kecamatan**.
2. **Alur Kecamatan (`KEP-DEMO-KEC-002`)**:
   - Login sebagai **Operator Kecamatan** (`OP-KEC-01`), buka `/kepegawaian`.
   - Tunjukkan bahwa pengajuan KGB dari Pegawai Kecamatan langsung masuk ke antrean **Operator Kecamatan** (tanpa melewati Lurah).
   - Demonstrasikan tombol **Ekspor Rekap KGB (.xlsx)** dan **Daftar Nominatif** (Kenaikan Pangkat / Pensiun).
3. **Cetak Form Cuti Baku BKPSDMD (`KEP-DEMO-KEC-003`)**:
   - Buka detail pengajuan Cuti yang berstatus `Selesai`, tunjukkan **Timeline Visual** posisi berkas per meja, lalu klik **Cetak Form Cuti (PDF)**.

### Tahap 3 — Persuratan & Kearsipan Dinamis Permendagri 83/2022 (3 Menit)
1. **Surat Masuk & Disposisi Berjenjang**:
   - Login sebagai **Sekcam** (`SEKCAM-01`), buka `/surat-masuk`.
   - Pilih surat berstatus `Dicatat`, beri **Paraf Digital** (atau uji fitur **Tolak Surat** dengan alasan wajib).
   - Login sebagai **Camat** (`CAMAT-01`), pilih surat berstatus `Diparaf Sekcam`, tentukan unit tujuan disposisi dan tandatangani **Lembar Disposisi**.
2. **Surat Keluar & Penomoran Otomatis**:
   - Login sebagai **Operator Kecamatan** (`OP-KEC-01`), buka `/surat-keluar/create`.
   - Pilih Kode Klasifikasi (misal `800` Kepegawaian) dan tanggal surat, tunjukkan nomor surat otomatis terbentuk sesuai standar Pemkot Makassar: `[Kode Klasifikasi]/[No. Urut]/KT/[Bulan Romawi]/[Tahun]` (contoh: `800/4/KT/XI/2026`).
3. **Portal Layanan & Tracking Surat Mandiri (Tanpa Login)**:
   - Buka `/layanan-surat` di tab incognito/logout, ajukan permohonan surat pengantar untuk mendapatkan **Kode Tracking Unik**, lalu lacak secara real-time di `/tracking-surat`.

### Tahap 4 — Presensi Apel & Peringatan Pembinaan Pegawai (2 Menit)
1. Login sebagai **Operator Kecamatan** (`OP-KEC-01`), buka `/presensi`.
2. Tunjukkan **Grid Validasi Foto Apel Harian** dengan tombol cepat **Valid / Tolak** serta panel **Pengaturan Jam Sesi Apel & Mode Testing**.
3. Buka menu **Rekapitulasi & Pembinaan** (`/presensi/rekap`).
4. Tunjukkan banner **Peringatan Pembinaan** yang otomatis menandai pegawai dengan akumulasi **Tanpa Keterangan (TK) >= 3 kali** dalam bulan berjalan (`Muhammad Akbar`), lalu klik **Buat Draf Surat Panggilan Pembinaan (PDF)** dan **Ekspor Excel/PDF Rekapitulasi**.

