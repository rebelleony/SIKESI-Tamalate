# SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

## PROJECT REKAYASA PERANGKAT LUNAK
### Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu (Kecamatan Tamalate)

**Disusun oleh:** Muh. Fauzan Rusda · Rebel Leony Dy Agustia Djokja · Meilany Olivia Rosari · Putri Dwi
**Konsentrasi:** Cloud Computing
**Semester / TA:** Semester 7 / 2026

---

## IDENTITAS TIM PROYEK

| Item | Keterangan |
|---|---|
| Nama Proyek | Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu |
| Klien / Pengguna | Kantor Kecamatan Tamalate dan Kelurahan di wilayah Tamalate |

**Anggota Tim:**

| No | Nama | NIM | Peran / Jobdesk |
|---|---|---|---|
| 1 | Muh. Fauzan Rusda | D121231035 | Backend Developer & Database Architect |
| 2 | Meilany Olivia Rosari | D121231075 | Project Manager & System Analyst |
| 3 | Putri Dwi | D121231063 | UI/UX Designer & Frontend Developer |
| 4 | Rebel Leony Dy Agustia Djokja | D121231039 | Quality Assurance (QA) & Technical Writer |

---

# BAB 1 – PENDAHULUAN

## 1.1 Latar Belakang Masalah

Kecamatan Tamalate yang membawahi sejumlah kelurahan dengan banyak aparatur sipil dan pegawai memiliki tata kelola administrasi kepegawaian—seperti presensi apel, pengajuan cuti, Kenaikan Gaji Berkala (KGB), kenaikan pangkat, penanganan surat masuk, dan pengajuan pensiun—yang masih dilakukan secara manual berbasis berkas fisik/kertas.

Kondisi tersebut menimbulkan beberapa kendala:

- Risiko berkas antar-pegawai tertukar atau hilang.
- Alur verifikasi dan persetujuan berkas yang tidak terpantau (tidak ada jejak status).
- Pencatatan presensi apel secara manual yang tidak efektif, memakan waktu, dan rentan kekeliruan rekapitulasi.
- Proses penomoran dan klasifikasi surat yang masih manual sehingga rawan duplikasi.

Selama ini Kecamatan Tamalate belum memiliki aplikasi lokal tersendiri; data pegawai diambil dari aplikasi terpusat BKPSDMD Kota Makassar (diperoleh secara berkala dalam bentuk berkas, lalu diimpor ke sistem ini — bukan integrasi/sinkronisasi daring langsung). Oleh karena itu, diperlukan sebuah perangkat lunak berbasis web dengan **login tunggal internal (Single Sign-On antar-modul dalam aplikasi ini)** untuk mengotomatisasi pengajuan layanan kepegawaian, alur persetujuan berjenjang, dan pencatatan presensi apel secara terpusat.

## 1.2 Masalah dan Tujuan

### 1.2.1 Rumusan Masalah

1. Bagaimana merancang sistem pengajuan cuti, KGB, kenaikan pangkat, dan pensiun secara digital agar meminimalisir risiko berkas tertukar dan hilang?
2. Bagaimana membangun alur verifikasi dan persetujuan berjenjang yang sesuai dengan struktur organisasi di tingkat kecamatan dan kelurahan?
3. Bagaimana membangun fitur pencatatan dan validasi presensi apel berbasis unggah bukti foto secara terpusat?
4. Bagaimana mengelola penanganan surat masuk beserta klasifikasi dan penomorannya secara digital?

### 1.2.2 Tujuan Proyek

1. Membangun modul pengajuan layanan kepegawaian (cuti, KGB, kenaikan pangkat, pensiun) berbasis formulir dan unggah berkas digital.
2. Mengimplementasikan alur multi-tier approval dan verifikasi berkas otomatis sesuai peran pengguna (Role-Based Access Control).
3. Mengembangkan sistem presensi apel berbasis foto visual yang memudahkan verifikasi dan rekapitulasi kehadiran.
4. Menyediakan modul penanganan surat masuk dengan klasifikasi dan penomoran otomatis.

## 1.3 Cakupan Proyek (Project Scope)

### 1.3.1 Dalam Cakupan (In Scope)

- **Modul Autentikasi dan Akun:** manajemen profil pengguna dan autentikasi **login tunggal internal (SSO antar-modul)** berbasis NIP/Username dengan RBAC.
- **Modul Pengajuan Layanan:** cuti, KGB, kenaikan pangkat, pensiun, dan penanganan surat masuk.
- **Modul Verifikasi dan Alur Approval:** verifikasi berkas, persetujuan tingkat pertama (Kasi/Kasubag), dan persetujuan akhir (Camat/Lurah).
- **Modul Presensi Apel:** pencatatan kehadiran harian berbasis unggah foto.
- **Modul Laporan & Rekapitulasi:** rekap presensi dan layanan kepegawaian per periode.
- **Target Platform:** aplikasi web responsif (PC/Laptop/Smartphone/Tablet).

### 1.3.2 Di Luar Cakupan (Out of Scope)

- Transaksi transfer finansial atau penggajian langsung.
- Integrasi API mesin absensi biometrik / facial recognition otomatis.
- Pengelolaan kepegawaian di luar wilayah kerja Kecamatan Tamalate dan kelurahan binaannya.
- Proses penetapan SK di tingkat BKPSDMD (sistem berhenti pada penerbitan surat/usulan yang diteruskan ke BKPSDMD).

> **Catatan domain:** Pegawai PPPK Paruh Waktu tidak memperoleh hak kenaikan pangkat, pensiun, KGB, dan cuti. Mereka hanya mengikuti **presensi apel**. Kelurahan tidak memiliki SOP tersendiri; pegawai kelurahan hanya mengumpulkan berkas persyaratan, sedangkan pengurusan induk tetap di Subbag Umum & Kepegawaian Kecamatan.

## 1.4 Glosarium

| Istilah / Singkatan | Definisi / Keterangan |
|---|---|
| KGB | Kenaikan Gaji Berkala; penyesuaian gaji PNS berkala berdasarkan masa kerja dan SK. |
| Kenaikan Pangkat | Proses usulan kenaikan pangkat/golongan PNS sesuai masa kerja dan angka kredit. |
| Kasubag | Kepala Sub-bagian (atasan langsung di sekretariat kecamatan). |
| Kasi | Kepala Seksi (atasan langsung pada seksi kerja kecamatan/kelurahan). |
| Sekcam | Sekretaris Camat. |
| Camat / Lurah | Pimpinan tertinggi di tingkat kecamatan / kelurahan (pemberi persetujuan akhir). |
| Verifikator | Staf kepegawaian (spesifiknya staf arsip) yang memeriksa kelengkapan berkas. |
| SSO | Single Sign-On; **login satu pintu untuk seluruh modul internal sistem ini** (satu akun & satu sesi untuk semua layanan). Catatan: SSO terfederasi dengan sistem BKPSDMD **di luar cakupan implementasi saat ini** — data pegawai diperoleh lewat impor berkas, bukan sinkronisasi daring. |
| RBAC | Role-Based Access Control; pembatasan hak akses berdasarkan peran pengguna. |
| BKPSDMD | Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Daerah Kota Makassar. |
| NIP | Nomor Induk Pegawai. |
| WITA | Waktu Indonesia Tengah. |

---

# BAB 2 – DESKRIPSI KEBUTUHAN SISTEM

## 2.1 System Environment

Sistem yang akan dikembangkan merupakan sistem informasi terpadu untuk mendukung pelayanan administrasi pada Subbag Umum dan Kepegawaian Kecamatan Tamalate. Sistem berbasis web dan digunakan untuk mengelola layanan administrasi kepegawaian: pengajuan kenaikan pangkat, KGB, cuti, pensiun, penanganan surat masuk, serta presensi apel.

Sistem ditujukan untuk membantu proses pengajuan, pemeriksaan, verifikasi, persetujuan, dan pemantauan status layanan secara lebih terstruktur dan terdokumentasi. Sistem dapat diakses melalui komputer, laptop, maupun smartphone menggunakan web browser yang terhubung ke jaringan.

Data yang dikelola meliputi: data pegawai, data pengajuan layanan, dokumen persyaratan, data verifikasi dan persetujuan, data presensi apel, serta data surat masuk. Sistem menggunakan basis data terpusat sehingga informasi tersimpan dan terkelola secara terintegrasi dalam satu sistem.

Dalam pelaksanaannya, sistem melibatkan beberapa pengguna sesuai hak akses dan tugas masing-masing: **Pegawai/Staf, Verifikator, Kasi/Kasubag, Camat/Lurah, dan Super Admin**. Setiap pengguna hanya dapat mengakses fungsi yang sesuai dengan perannya (RBAC).

### 2.1.1 Arsitektur Sistem

Sistem menggunakan arsitektur **client–server tiga lapisan (three-tier architecture)**:

1. **Presentation Layer (Client):** Web browser pada PC/laptop/smartphone. Antarmuka dibangun responsif; modul presensi apel memanfaatkan API kamera browser (`getUserMedia`) untuk penangkapan foto.
2. **Application Layer (Server):** Backend berbasis **Laravel (PHP)** yang menyediakan RESTful API, logika bisnis alur approval berjenjang, autentikasi berbasis sesi (login tunggal internal/SSO), RBAC, validasi form, serta penanganan unggah berkas yang aman.
3. **Data Layer (Database):** DBMS relasional **MySQL/PostgreSQL** yang menyimpan data master, transaksi pengajuan, berkas, dan presensi. File dokumen disimpan pada storage server (terisolasi) dan hanya direferensikan oleh basis data.

```
[Browser / Mobile]  --HTTPS/TLS 1.3-->  [Web Server: Nginx]  -->  [App: Laravel API + RBAC]  -->  [DB: MySQL/PostgreSQL]
                                                                        |
                                                                        +--> [File Storage terisolasi: berkas & foto apel]
```

Deployment pada **Ubuntu Linux VPS + Nginx** (opsi kontainerisasi Docker), dengan SSL/HTTPS mandiri, pencadangan otomatis harian, dan pemantauan uptime.

> Diagram arsitektur visual (Mermaid) tersedia di berkas `diagrams/`.

### 2.1.2 Teknologi yang Digunakan

| Komponen | Teknologi / Tools | Keterangan |
|---|---|---|
| Frontend | Tailwind CSS & Blade / React.js | Antarmuka responsif, modern, ramah perangkat mobile untuk penangkapan foto apel. |
| Backend | Laravel (PHP) Framework | Autentikasi RBAC, ORM Eloquent, penanganan unggah berkas yang aman. |
| Database | MySQL / PostgreSQL | Transaksi ACID-compliant, integritas relasional, kecepatan query tinggi. |
| Deployment | Ubuntu Linux VPS + Nginx / Docker | Kontrol privasi berkas, konfigurasi SSL/HTTPS mandiri, uptime stabil. |

> **Kebijakan pengembangan:** Seluruh kode aplikasi (logika bisnis, model, controller, view, skema basis data) ditulis **from scratch oleh tim**, tanpa menggunakan template/boilerplate proyek jadi atau menyalin sistem open-source yang sudah utuh. Framework dan library dipakai hanya sebagai kerangka dasar, bukan sebagai aplikasi jadi.

## 2.2 Spesifikasi Kebutuhan Fungsional (Use Case Diagram)

### 2.2.1 Daftar Aktor

| Aktor | Deskripsi |
|---|---|
| **Pegawai / Staf** | Mengajukan permohonan layanan kepegawaian (cuti, KGB, pangkat, pensiun), mengunggah foto apel, dan memantau progres status berkas. |
| **Verifikator** | Staf kepegawaian (staf arsip) yang memeriksa kelengkapan dan keabsahan dokumen persyaratan sebelum diteruskan ke atasan. |
| **Kasi / Kasubag** | Kepala Seksi / Kepala Subbagian Umum & Kepegawaian; memberikan evaluasi dan persetujuan tingkat pertama (first-tier approval). |
| **Sekretaris Camat (Sekcam)** | Atasan langsung Kasubag; memverifikasi dan memaraf draf usulan sebagai persetujuan tingkat kedua sebelum diteruskan ke Camat. |
| **Camat / Lurah** | Memberikan persetujuan akhir (final approval) berupa validasi dan tanda tangan. Cuti Camat diproses Sekda (kecamatan hanya membuatkan surat); cuti Sekcam diajukan ke atasan langsung (Camat). |
| **BKPSDMD Kota Makassar** | Instansi induk di luar sistem; tujuan akhir pengiriman usulan (KGB, pangkat, pensiun, cuti). Status pengajuan berakhir pada tahap "Dikirim ke BKPSDMD". |
| **Super Admin** | Mengelola data master pegawai, unit kerja, seksi/sub-bagian, klasifikasi surat, dan pengaturan akun/peran. |

### 2.2.2 Use Case Diagram

> Diagram Use Case (Mermaid) tersedia di berkas `diagrams/use-case.md`.

### 2.2.3 Daftar Use Case

| UC-ID | Nama Use Case | Aktor | Deskripsi Singkat |
|---|---|---|---|
| UC-01 | Autentikasi / Login SSO | Semua aktor | Autentikasi berbasis NIP/Username dan kata sandi dengan kontrol akses RBAC. |
| UC-02 | Kelola Profil Pengguna | Semua aktor | Melihat identitas diri, memperbarui kontak, dan mengubah kata sandi mandiri. |
| UC-03 | Kelola Data Master & Akun | Super Admin | Menambah/mengedit/menonaktifkan akun pegawai, menyusun struktur unit kerja dan seksi. |
| UC-04 | Pengajuan Layanan Kepegawaian | Pegawai/Staf | Mengajukan Cuti, KGB, Kenaikan Pangkat, atau Pensiun beserta unggahan dokumen. |
| UC-05 | Pemantauan Status Berkas | Pegawai/Staf | Melacak progres verifikasi, catatan perbaikan, dan status persetujuan. |
| UC-06 | Pencatatan Presensi Apel | Pegawai/Staf | Absensi apel dengan mengambil dan mengunggah foto bukti visual. |
| UC-07 | Verifikasi Berkas Persyaratan | Verifikator | Memeriksa keabsahan dokumen, meneruskan, atau memberi catatan pengembalian. |
| UC-08 | Persetujuan Tingkat Pertama | Kasi/Kasubag | Mengevaluasi kelayakan permohonan staf (first-tier approval). |
| UC-09 | Persetujuan Tingkat Kedua & Akhir | Sekcam, Camat/Lurah | Sekcam memaraf draf (tier-2), Camat memvalidasi & menandatangani sebagai persetujuan akhir (final). |
| UC-10 | Rekapitulasi Presensi & Laporan | Verifikator, Super Admin | Mencetak/mengunduh laporan rekap kehadiran apel dan berkas kepegawaian. |

### 2.2.4 Rantai Persetujuan Berjenjang (Pemetaan SOP Mitra)

Seluruh layanan kepegawaian (pensiun, KGB, kenaikan pangkat, cuti) mengikuti pola
berjenjang yang sama sesuai SOP asli mitra (rinci di `docs/sop-activity-diagrams.md`):

| Tahap | Status di sistem | Aktor | Aksi |
|---|---|---|---|
| 0 | `menunggu_verifikasi` | Pelaksana / Staf | Input usulan, pendataan, kumpulkan berkas |
| 1 | `menunggu_tier1` | Kasubag Umum & Kepegawaian | Verifikasi kelengkapan berkas |
| 2 | `menunggu_tier2` | Sekretaris Camat | Verifikasi & memaraf draf usulan |
| 3 | `menunggu_final` | Camat / Lurah | Validasi & tanda tangan |
| 4 | `dikirim_bkpsdm` | Pelaksana / Staf | Registrasi, penomoran, stempel, kirim ke BKPSDMD |
| — | `selesai` / `ditolak` / `perlu_revisi` | Sistem | Status akhir atau pengembalian ke pemohon |

Aturan khusus yang harus ditegakkan sistem:
- **Pejabat tidak dapat menyetujui usulannya sendiri** (validasi `pegawai_id ≠ approver_id`).
- **KGB staf** diperiksa Kasubag; **KGB Kasi/Kasubag** ditangani Sekcam.
- **Cuti Camat** diproses Sekda (sistem hanya membuatkan surat pengantar); **cuti Sekcam** diajukan ke Camat.
- **PPPK Paruh Waktu** hanya dapat mengikuti presensi apel — seluruh layanan kepegawaian ditolak otomatis.

## 2.3 Kebutuhan Pengguna (User Requirements)

- **Pegawai** membutuhkan: akses cepat mengunggah berkas tanpa risiko tertukar, kemudahan memantau status/persetujuan secara transparan, dan form absensi apel harian yang mudah diakses dari perangkat mobile.
- **Verifikator** membutuhkan: dashboard berkas masuk untuk memvalidasi kelengkapan dokumen per pemohon, serta fitur catatan/komentar revisi bila dokumen belum lengkap/salah.
- **Kasi/Kasubag dan Camat/Lurah** membutuhkan: notifikasi dan ringkasan pengajuan bawahan yang siap disetujui, serta fitur persetujuan sistem sekali klik (one-click approval).
- **Super Admin** membutuhkan: antarmuka pengelolaan data master pegawai, struktur unit kerja/seksi, klasifikasi & penomoran surat, serta pengaturan akun/peran.

## 2.4 Kebutuhan Non-Fungsional

| Kategori | Deskripsi Kebutuhan |
|---|---|
| Performa | Waktu respons pemuatan halaman rata-rata < 2 detik; kompresi unggah foto apel selesai < 3 detik. |
| Keamanan | Koneksi terenkripsi HTTPS/TLS 1.3, hashing kata sandi Bcrypt/Argon2, proteksi CSRF, dan otorisasi sesi. |
| Ketersediaan | Uptime minimal 99% selama jam kerja operasional; pencadangan otomatis harian. |
| Skalabilitas | Menangani sedikitnya 100 pengguna bersamaan; DB menampung ±50.000 riwayat transaksi/tahun. |
| Kemudahan Penggunaan | Antarmuka responsif (PC & mobile), navigasi ringkas maksimal 3 langkah menuju menu utama. |
| Kompatibilitas | Berjalan pada browser modern (Chrome, Firefox, Safari, Edge). |

---

# BAB 3 – SPESIFIKASI KEBUTUHAN

## 3.1 Kebutuhan Antarmuka Eksternal

### 3.1.1 Antarmuka Pengguna (User Interface)

Antarmuka dirancang menggunakan kaidah *User-Centered Design* dengan tata letak minimalis dan responsif. Navigasi utama menggunakan sidebar/menu atas; setiap halaman menampilkan konteks peran pengguna.

### 3.1.2 Antarmuka Perangkat Keras

Sistem berbasis web sehingga tidak memerlukan perangkat keras khusus. Perangkat pengguna cukup menjalankan web browser dan terhubung jaringan. Modul presensi apel memerlukan kamera (webcam/kamera ponsel).

**Perangkat yang didukung:** PC/Desktop, Laptop, Smartphone, Tablet.

**Spesifikasi minimum perangkat:**
- PC/Laptop: prosesor minimal Dual Core, RAM minimal 4 GB, penyimpanan tersedia minimal 10 GB, monitor resolusi minimal 1366×768, koneksi internet.
- Smartphone/Tablet: OS Android/iOS dengan browser modern, kamera, dan koneksi internet.

### 3.1.3 Antarmuka Perangkat Lunak

- Web server (Nginx) dan runtime PHP untuk backend Laravel.
- DBMS MySQL/PostgreSQL sebagai penyimpanan data.
- Browser modern yang mendukung HTML5, `getUserMedia` (kamera), dan JavaScript ES6+.

### 3.1.4 Antarmuka Komunikasi

Seluruh komunikasi client–server melalui protokol **HTTPS/TLS 1.3**. Pertukaran data menggunakan format **JSON** melalui RESTful API.

## 3.2 Spesifikasi Kebutuhan Fungsional (Detail)

### 3.2.1 Daftar Kebutuhan Fungsional (Functional Requirements)

| ID | Kebutuhan | Aktor | Prioritas |
|---|---|---|---|
| FR-01 | Sistem menyediakan autentikasi login berbasis NIP/Username + kata sandi dengan RBAC. | Semua | Wajib |
| FR-02 | Sistem memungkinkan pengguna mengelola profil dan mengubah kata sandi mandiri. | Semua | Wajib |
| FR-03 | Super Admin dapat menambah/mengedit/menonaktifkan akun, unit kerja, dan peran. | Super Admin | Wajib |
| FR-04 | Pegawai dapat membuat pengajuan cuti, KGB, kenaikan pangkat, atau pensiun dengan unggah berkas. | Pegawai | Wajib |
| FR-05 | Sistem memvalidasi kelengkapan field & berkas wajib sebelum pengajuan disimpan. | Sistem | Wajib |
| FR-06 | Pegawai dapat memantau status dan riwayat pengajuan secara transparan. | Pegawai | Wajib |
| FR-07 | Pegawai dapat mencatat presensi apel **pagi dan sore** dengan unggah/kamera foto pada jendela jam apel masing-masing; sistem merekap otomatis per pekan. | Pegawai | Wajib |
| FR-08 | Verifikator dapat memvalidasi berkas, meneruskan, atau mengembalikan untuk revisi dengan catatan. | Verifikator | Wajib |
| FR-09 | Kasi/Kasubag dapat memberi persetujuan/penolakan tingkat pertama (verifikasi kelengkapan berkas). | Kasi/Kasubag | Wajib |
| FR-10 | Sekcam memverifikasi & memaraf (tier-2); Camat/Lurah memberi validasi & tanda tangan akhir dan menerbitkan draf surat/SK. | Sekcam, Camat/Lurah | Wajib |
| FR-11 | Sistem menerbitkan output surat (mis. form cuti baku BKPSDMD, surat pengantar/usulan) sesuai format. | Sistem | Wajib |
| FR-12 | Sistem merekap presensi dan layanan kepegawaian, ekspor PDF/Excel. | Verifikator/Admin | Wajib |
| FR-13 | Sistem mencatat surat masuk dengan klasifikasi & penomoran otomatis. | Verifikator/Admin | Opsional |
| FR-14 | Sistem mengirim notifikasi status ke pengguna terkait pada setiap perubahan tahap. | Sistem | Dianjurkan |

### 3.2.2 Deskripsi Use Case Detail

#### UC-01: Autentikasi / Login SSO

| | |
|---|---|
| **Deskripsi** | Pengguna melakukan autentikasi ke sistem berbasis NIP/Username dan kata sandi dengan kontrol akses RBAC. |
| **Aktor** | Semua aktor (Pegawai, Verifikator, Kasi/Kasubag, Camat/Lurah, Super Admin) |
| **Precondition** | Pengguna memiliki akun aktif yang terdaftar oleh Super Admin; berada pada halaman login. |
| **Main Flow** | 1. Pengguna mengakses halaman login sistem. 2. Sistem menampilkan formulir login (NIP/Username + kata sandi). 3. Pengguna mengisi kredensial dan menekan 'Masuk'. 4. Sistem memverifikasi kredensial dan mencocokkan status akun (aktif/nonaktif). 5. Sistem membuat sesi, memuat peran (role), dan mengarahkan pengguna ke dashboard sesuai hak aksesnya. |
| **Alternative Flow** | Kata sandi salah → Sistem menampilkan pesan kesalahan dan menambah penghitung percobaan; setelah 5x gagal, akun terkunci sementara (mis. 15 menit). |
| **Postcondition** | Pengguna terautentikasi dan memperoleh akses sesuai perannya. |
| **Exception** | Akun nonaktif/belum terdaftar → Sistem menolak akses dengan pesan menghubungi Super Admin. Koneksi terputus → Sistem menampilkan opsi 'Coba Lagi'. |

> **Catatan Implementasi:** autentikasi diwujudkan sebagai **login lokal berbasis sesi + RBAC** (satu pintu untuk semua modul internal). SSO terfederasi dengan sistem BKPSDMD **tidak** diimplementasikan pada versi ini (di luar cakupan); data pegawai masuk melalui impor berkas Excel.

#### UC-02: Kelola Profil Pengguna

| | |
|---|---|
| **Deskripsi** | Pengguna melihat identitas diri, memperbarui nomor kontak, dan mengubah kata sandi secara mandiri. |
| **Aktor** | Semua aktor |
| **Precondition** | Pengguna telah login. |
| **Main Flow** | 1. Pengguna membuka menu 'Profil Saya'. 2. Sistem menampilkan data identitas (Nama, NIP, Jabatan, Unit Kerja) yang bersifat baca-saja untuk field master. 3. Pengguna memperbarui field yang diizinkan (nomor kontak, email, foto profil). 4. Untuk ganti kata sandi, pengguna memasukkan kata sandi lama + kata sandi baru (2x). 5. Pengguna menekan 'Simpan Perubahan'. 6. Sistem memvalidasi dan menyimpan perubahan dengan hash kata sandi baru. |
| **Alternative Flow** | Kata sandi lama tidak cocok → Sistem menolak perubahan dan menampilkan pesan kesalahan. |
| **Postcondition** | Data profil/kata sandi pengguna berhasil diperbarui. |
| **Exception** | Format input tidak valid (mis. email/nomor) → Sistem menampilkan pesan validasi inline. |

#### UC-03: Kelola Data Master & Akun

| | |
|---|---|
| **Deskripsi** | Super Admin menambah, mengedit, menonaktifkan akun pegawai, serta menyusun struktur unit kerja dan seksi/sub-bagian. |
| **Aktor** | Super Admin |
| **Precondition** | Super Admin telah login dengan hak akses administrator. |
| **Main Flow** | 1. Super Admin membuka menu 'Data Master'. 2. Memilih jenis data (Pegawai / Unit Kerja / Seksi / Peran). 3. Sistem menampilkan tabel data master dengan opsi Cari, Tambah, Edit, Nonaktifkan. 4. Super Admin menambah/mengedit entri (mis. mengimpor data pegawai dari berkas Excel BKPSDMD). 5. Sistem memvalidasi keunikan NIP/username. 6. Super Admin menetapkan peran dan atasan langsung untuk setiap pegawai. 7. Sistem menyimpan perubahan. |
| **Alternative Flow** | Impor massal dari Excel → Sistem memetakan kolom, memvalidasi baris, menampilkan pratinjau baris error sebelum commit. Menonaktifkan akun → status berubah nonaktif tanpa menghapus riwayat. |
| **Postcondition** | Data master dan akun pegawai terkelola serta menjadi acuan RBAC dan alur approval. |
| **Exception** | NIP/username duplikat → Sistem menolak dan menandai baris bermasalah. |

#### UC-04: Pengajuan Layanan Kepegawaian

| | |
|---|---|
| **Deskripsi** | Pegawai mengajukan permohonan Cuti, KGB, Kenaikan Pangkat, atau Pensiun beserta unggahan dokumen persyaratan lengkap. |
| **Aktor** | Pegawai / Staf |
| **Precondition** | Pegawai telah login; berstatus berhak atas layanan (PPPK Paruh Waktu tidak dapat mengajukan cuti/KGB/pangkat/pensiun). |
| **Main Flow** | 1. Pegawai membuka menu 'Pengajuan Layanan'. 2. Memilih jenis layanan (Cuti / KGB / Kenaikan Pangkat / Pensiun). 3. Sistem menampilkan formulir sesuai jenis (mis. cuti: jenis cuti, alasan, lama, tanggal mulai–selesai, alamat selama cuti). 4. Sistem mengisi otomatis data pegawai (Nama, NIP, Jabatan, Masa Kerja, Unit Kerja) dari data master. 5. Pegawai melengkapi field dan mengunggah dokumen persyaratan (PDF/gambar). 6. Sistem memvalidasi kelengkapan field & berkas wajib. 7. Pegawai menekan 'Kirim Pengajuan'. 8. Sistem menyimpan pengajuan dengan status 'Menunggu Verifikasi' dan memberi nomor/tanggal pengajuan. |
| **Alternative Flow** | Berkas belum lengkap → Sistem menahan pengiriman dan menandai field/berkas yang kurang. Simpan draf → pengajuan tersimpan dengan status 'Draf' untuk dilanjutkan. |
| **Postcondition** | Pengajuan tercatat dan masuk antrean verifikasi. |
| **Exception** | Unggahan melebihi batas ukuran/format → Sistem menolak dengan pesan ketentuan berkas. Pegawai tidak berhak atas layanan → opsi layanan dinonaktifkan. |

#### UC-05: Pemantauan Status Berkas

| | |
|---|---|
| **Deskripsi** | Pegawai melacak progres tahapan verifikasi, catatan perbaikan, serta status persetujuan berkas secara transparan. |
| **Aktor** | Pegawai / Staf |
| **Precondition** | Pegawai telah login dan memiliki minimal satu pengajuan. |
| **Main Flow** | 1. Pegawai membuka menu 'Pengajuan Saya'. 2. Sistem menampilkan daftar pengajuan beserta status terkini (Draf / Menunggu Verifikasi / Perlu Revisi / Menunggu Persetujuan Kasi-Kasubag / Menunggu Persetujuan Camat-Lurah / Disetujui / Ditolak). 3. Pegawai memilih satu pengajuan. 4. Sistem menampilkan linimasa (timeline) riwayat tiap tahap, pejabat yang memproses, waktu, dan catatan. 5. Bila berstatus 'Perlu Revisi', sistem menampilkan catatan kekurangan dan tombol 'Unggah Berkas Perbaikan'. |
| **Alternative Flow** | Pegawai mengajukan ulang perbaikan → status kembali ke 'Menunggu Verifikasi'. |
| **Postcondition** | Pegawai memperoleh informasi akurat terkait posisi dan progres berkasnya. |
| **Exception** | Gagal memuat detail riwayat karena gangguan jaringan → Sistem menampilkan opsi 'Muat Ulang'. |

#### UC-06: Pencatatan Presensi Apel Harian

| | |
|---|---|
| **Deskripsi** | Pencatatan kehadiran **apel pagi dan apel sore** dengan mengunggah bukti foto visual secara langsung via web browser. Sesuai SOP Pembinaan Pegawai, kehadiran diawasi dua sesi per hari dan direkapitulasi per pekan. |
| **Aktor** | Pegawai / Staf (termasuk PPPK Paruh Waktu) |
| **Precondition** | Pegawai login pada jendela apel: **pagi 07.00–08.30 WITA** atau **sore 15.30–17.00 WITA**. |
| **Main Flow** | 1. Pegawai mengakses menu 'Presensi Apel'. 2. Sistem menampilkan sesi aktif (Pagi/Sore) sesuai jam server dan meminta izin akses kamera perangkat. 3. Pegawai mengizinkan akses dan mengambil foto diri saat mengikuti apel. 4. Sistem menampilkan pratinjau foto. 5. Pegawai menekan 'Kirim Absensi'. 6. Sistem menyimpan sesi, stempel waktu (timestamp), dan foto terkompresi ke database (satu record unik per pegawai per sesi per hari). |
| **Alternative Flow** | Izin kamera browser gagal → Sistem membuka antarmuka unggah file foto dari galeri/kamera alternatif. |
| **Postcondition** | Status kehadiran sesi berjalan (pagi/sore) tercatat sebagai 'Hadir (Menunggu Validasi)' dan masuk rekapitulasi pekan berjalan. |
| **Exception** | Presensi di luar jendela waktu apel → tombol dinonaktifkan dengan notifikasi; presensi ganda pada sesi & hari yang sama → ditolak (kendala unik). |

#### UC-07: Verifikasi Berkas Persyaratan

| | |
|---|---|
| **Deskripsi** | Pemeriksaan kelengkapan dan keabsahan dokumen persyaratan permohonan kepegawaian oleh petugas verifikator. |
| **Aktor** | Verifikator |
| **Precondition** | Terdapat permohonan masuk dengan status 'Menunggu Verifikasi'. |
| **Main Flow** | 1. Verifikator membuka menu 'Daftar Verifikasi Berkas'. 2. Sistem menyajikan antrean berkas pemohon. 3. Verifikator membuka satu berkas dan memeriksa pratinjau dokumen PDF/lampiran. 4. Dokumen dinyatakan lengkap dan benar. 5. Verifikator menekan tombol 'Validasi & Teruskan'. 6. Sistem memperbarui status menjadi 'Menunggu Persetujuan Kasi/Kasubag'. |
| **Alternative Flow** | Berkas kurang/salah → Verifikator memilih 'Kembalikan untuk Revisi', menulis catatan kekurangan, status berubah menjadi 'Perlu Revisi'. |
| **Postcondition** | Berkas siap diproses atasan (Kasi/Kasubag) atau kembali ke pemohon untuk diperbaiki. |
| **Exception** | Dokumen lampiran korup/tidak terbaca → Sistem memberi peringatan untuk mengembalikan berkas. |

#### UC-08: Persetujuan Tingkat Pertama (First-Tier Approval)

| | |
|---|---|
| **Deskripsi** | Pemberian evaluasi dan persetujuan awal atas permohonan bawahan oleh Kepala Seksi atau Kepala Sub-bagian. |
| **Aktor** | Kasi / Kasubag |
| **Precondition** | Permohonan telah lolos tahap verifikasi berkas oleh Verifikator. |
| **Main Flow** | 1. Kasi/Kasubag membuka menu 'Persetujuan Pengajuan'. 2. Sistem menyajikan daftar staf bawahan yang telah diverifikasi berkasnya. 3. Pejabat meninjau lembar ringkasan permohonan. 4. Pejabat menekan tombol 'Setujui' (one-click approval). 5. Sistem memperbarui status menjadi 'Menunggu Persetujuan Camat/Lurah'. |
| **Alternative Flow** | Pejabat menolak → memasukkan alasan penolakan pada kotak dialog, status menjadi 'Ditolak Kasi/Kasubag'. |
| **Postcondition** | Permohonan diteruskan ke meja Camat/Lurah untuk persetujuan akhir. |
| **Exception** | Sesi pejabat kedaluwarsa saat menekan tombol → Sistem meminta otentikasi ulang. |

#### UC-09: Persetujuan Akhir (Final Approval)

| | |
|---|---|
| **Deskripsi** | Pengesahan resmi dan persetujuan pamungkas terhadap permohonan layanan kepegawaian oleh Camat atau Lurah. |
| **Aktor** | Camat / Lurah |
| **Precondition** | Permohonan telah memperoleh persetujuan tingkat pertama dari Kasi/Kasubag. |
| **Main Flow** | 1. Camat/Lurah membuka menu 'Persetujuan Akhir'. 2. Sistem menampilkan antrean pengajuan yang siap diputuskan. 3. Pejabat memeriksa riwayat verifikasi dan rekomendasi Kasi/Kasubag. 4. Pejabat menekan tombol 'Setujui Akhir'. 5. Sistem mengubah status menjadi 'Disetujui Penuh / Selesai' dan menerbitkan draf lembar pengesahan/surat. |
| **Alternative Flow** | Pejabat menolak → memberikan instruksi penolakan resmi, status berubah menjadi 'Ditolak Final'. |
| **Postcondition** | Permohonan selesai secara administratif; pemohon dapat mengunduh surat persetujuan/SK. |
| **Exception** | Koneksi server terputus saat penetapan → Sistem rollback transaksi dan meminta konfirmasi ulang. |

> **Aturan khusus pejabat:** (a) KGB, kenaikan pangkat, dan pensiun Camat/Sekcam diproses langsung di kecamatan. (b) Cuti Camat diproses Sekda — sistem hanya menerbitkan surat pengajuan. (c) Cuti Sekcam diproses di kecamatan dengan persetujuan atasan langsung (Camat). (d) Bila Camat/Sekcam mengajukan untuk dirinya sendiri, persetujuan dialihkan ke pejabat berwenang di atasnya (tidak menyetujui diri sendiri).

#### UC-10: Rekapitulasi Presensi dan Laporan Kepegawaian

| | |
|---|---|
| **Deskripsi** | Pencetakan dan peninjauan laporan rekap kehadiran apel harian serta rekapitulasi layanan kepegawaian per periode. |
| **Aktor** | Verifikator, Super Admin |
| **Precondition** | Pengguna login dengan hak akses verifikator atau admin. |
| **Main Flow** | 1. Pengguna membuka menu 'Laporan & Rekapitulasi'. 2. Memilih jenis laporan (Presensi Apel / Cuti / KGB / Kenaikan Pangkat / Pensiun / Surat Masuk). 3. Menentukan filter tanggal dan unit kerja/kelurahan. 4. Sistem menampilkan tabel agregasi data dan galeri foto apel. 5. Pengguna menekan 'Cetak PDF' atau 'Ekspor Excel'. 6. Sistem mengunduh dokumen laporan terstruktur. |
| **Alternative Flow** | Pengguna menyaring data pegawai tertentu berdasarkan NIP. |
| **Postcondition** | Laporan terunduh dalam format PDF/Excel siap arsip. |
| **Exception** | Data kosong pada rentang tanggal → Sistem menampilkan info 'Tidak ada rekaman data pada periode ini'. |

## 3.3 Spesifikasi Kebutuhan Non-Fungsional (Detail)

| ID | Kategori | Deskripsi Detail | Metode Verifikasi |
|---|---|---|---|
| NF-01 | Performa | Respons query & render halaman < 2 detik pada beban normal; unggah+kompresi foto apel < 3 detik. | Load testing Apache JMeter, skenario 100 virtual users. |
| NF-02 | Keamanan | HTTPS/TLS 1.3, proteksi CSRF pada seluruh form, enkripsi kata sandi Bcrypt/Argon2, isolasi folder dokumen. | Penetration testing internal, OWASP ZAP. |
| NF-03 | Skalabilitas | DB mampu menangani ≥ 50.000 riwayat transaksi pengajuan & presensi per tahun tanpa penurunan performa indeks. | Stress testing & data simulation script pada DBMS. |
| NF-04 | Ketersediaan & Reliabilitas | Uptime minimal 99% selama jam dinas; pencadangan otomatis harian. | Monitoring uptime (Prometheus/Uptime Kuma) & simulasi pemulihan data. |
| NF-05 | Kompatibilitas Antarmuka | Berjalan pada browser modern (Chrome, Firefox, Safari, Edge) di PC & smartphone. | Cross-browser testing manual & responsive device simulation. |

---

# LAMPIRAN

## Lampiran 1 – Jadwal Pengerjaan Proyek (Milestone)

| Milestone / Kegiatan | Mulai | Selesai | Penanggung Jawab |
|---|---|---|---|
| Kick-off & Pembentukan Tim | Minggu 1 | Minggu 2 | Semua anggota |
| Analisis & Pengumpulan User Requirements | Minggu 1 | Minggu 3 | Meilany Olivia Rosari |
| Penyusunan & Pengumpulan Proposal | Minggu 2 | Minggu 4 | Semua anggota |
| Perancangan Sistem & Prototipe UI/UX | Minggu 3 | Minggu 5 | Putri Dwi |
| Implementasi Backend & Skema Basis Data | Minggu 5 | Minggu 9 | Muh. Fauzan Rusda |
| Implementasi Frontend & Modul Presensi Apel | Minggu 6 | Minggu 10 | Putri Dwi & Muh. Fauzan Rusda |
| Pengujian Sistem (Black-box, JMeter) & Validasi | Minggu 10 | Minggu 12 | Rebel Leony Dy Agustia Djokja |
| Expo Proyek Rekayasa Perangkat Lunak | Minggu 13 | Minggu 13 | Semua anggota |
| Penyusunan & Pengumpulan Laporan Akhir | Minggu 14 | Minggu 14 | Semua anggota |

## Lampiran 2 – Mata Kuliah yang Direkognisi

| No | Mata Kuliah | Pengetahuan / Konsep yang Diaplikasikan |
|---|---|---|
| 1 | Rekayasa Perangkat Lunak | Analisis kebutuhan, identifikasi aktor & use case, perancangan sistem, pemodelan UML, SDLC, pengujian perangkat lunak. |
| 2 | Basis Data | Perancangan skema konseptual & fisikal, normalisasi, relasi multi-tabel, optimasi query, integritas data relasional. |
| 3 | Pemrograman Web/Mobile | RESTful API, routing controller, session management, login terpusat (SSO), unggah file. |
| 4 | Interaksi Manusia-Komputer | Wireframe/mockup, usability testing, prinsip responsif, kemudahan akses mobile. |
| 5 | Jaringan Komputer | Arsitektur client-server, HTTPS/TLS 1.3, manajemen keamanan port web, pengamanan kredensial. |

## Lampiran 3 – Pembagian Jobdesk Anggota Tim

| No | Nama | NIM | Jobdesk / Tanggung Jawab |
|---|---|---|---|
| 1 | Muh. Fauzan Rusda | D121231035 | Backend Developer & Database Architect: merancang basis data relasional, mengimplementasikan REST API, logika autentikasi SSO, dan alur approval berjenjang. |
| 2 | Meilany Olivia Rosari | D121231075 | Project Manager & System Analyst: memimpin perencanaan timeline, mengumpulkan spesifikasi kebutuhan klien, memodelkan use case diagram. |
| 3 | Putri Dwi | D121231063 | UI/UX Designer & Frontend Developer: mendesain wireframe/mockup, mengimplementasikan antarmuka responsif web, dan modul kamera presensi apel. |
| 4 | Rebel Leony Dy Agustia Djokja | D121231039 | Quality Assurance & Technical Writer: menguji fungsionalitas (black-box), pengujian performa (JMeter), menyusun laporan proposal/SRS akhir. |
