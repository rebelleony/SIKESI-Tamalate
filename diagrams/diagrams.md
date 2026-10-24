# Diagram UML & ERD — Sistem Informasi Kepegawaian & Presensi Terpadu (Tamalate)

Semua diagram dalam format **Mermaid**. Render di: VS Code (ekstensi Mermaid), https://mermaid.live, atau GitHub/GitLab.
Untuk dimasukkan ke Word: render di mermaid.live → Export PNG/SVG → sisipkan sebagai gambar.

---

## 1. Use Case Diagram

```mermaid
flowchart TB
    subgraph Sistem["Sistem Informasi Kepegawaian & Presensi Terpadu"]
        UC01([UC-01 Login SSO])
        UC02([UC-02 Kelola Profil])
        UC03([UC-03 Kelola Data Master & Akun])
        UC04([UC-04 Pengajuan Layanan])
        UC05([UC-05 Pemantauan Status])
        UC06([UC-06 Presensi Apel])
        UC07([UC-07 Verifikasi Berkas])
        UC08([UC-08 Persetujuan Pertama])
        UC09([UC-09 Persetujuan Tier-2 & Akhir])
        UC10([UC-10 Rekapitulasi & Laporan])
    end

    Pegawai([Pegawai / Staf])
    Verifikator([Verifikator])
    Kasi([Kasi / Kasubag])
    Sekcam([Sekretaris Camat])
    Camat([Camat / Lurah])
    Admin([Super Admin])

    Pegawai --> UC01
    Pegawai --> UC02
    Pegawai --> UC04
    Pegawai --> UC05
    Pegawai --> UC06

    Verifikator --> UC01
    Verifikator --> UC02
    Verifikator --> UC07
    Verifikator --> UC10

    Kasi --> UC01
    Kasi --> UC02
    Kasi --> UC08

    Sekcam --> UC01
    Sekcam --> UC02
    Sekcam --> UC09

    Camat --> UC01
    Camat --> UC02
    Camat --> UC09

    Admin --> UC01
    Admin --> UC02
    Admin --> UC03
    Admin --> UC10
```

---

## 2. Activity Diagram — Alur Pengajuan & Approval Berjenjang

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Pegawai login SSO]
    Login --> Pilih[Jenis layanan: Cuti / KGB / Pangkat / Pensiun]
    Pilih --> HakAkses{Berhak atas layanan?}
    HakAkses -- Tidak --> TolakHak[Akses ditolak / opsi dinonaktifkan] --> End1([Selesai])
    HakAkses -- Ya --> Form[Isi form & unggah berkas persyaratan]
    Form --> ValidasiForm{Field & berkas lengkap?}
    ValidasiForm -- Tidak --> Form
    ValidasiForm -- Ya --> Submit[Kirim pengajuan - status: Menunggu Verifikasi]

    Submit --> Verif[Verifikator memeriksa berkas]
    Verif --> CekVerif{Berkas valid?}
    CekVerif -- Tidak --> Revisi[Kembalikan untuk revisi + catatan]
    Revisi --> NotifPeg[Notifikasi ke pegawai] --> Form
    CekVerif -- Ya --> Teruskan[Teruskan - status: Menunggu Persetujuan Kasi/Kasubag]

    Teruskan --> Appr1[Kasi/Kasubag evaluasi tier-1]
    Appr1 --> Cek1{Setuju?}
    Cek1 -- Tidak --> Ditolak1[Status: Ditolak Kasi/Kasubag] --> End2([Selesai])
    Cek1 -- Ya --> Teruskan2[Status: Menunggu Paraf Sekcam tier-2]

    Teruskan2 --> ApprSek[Sekretaris Camat verifikasi & paraf]
    ApprSek --> CekSek{Setuju?}
    CekSek -- Tidak --> DitolakSek[Status: Ditolak Sekcam] --> End2b([Selesai])
    CekSek -- Ya --> Teruskan3[Status: Menunggu Persetujuan Camat/Lurah]

    Teruskan3 --> Appr2[Camat/Lurah validasi & tanda tangan]
    Appr2 --> Cek2{Setujui akhir?}
    Cek2 -- Tidak --> Ditolak2[Status: Ditolak Final] --> End3([Selesai])
    Cek2 -- Ya --> Kirim[Status: Menunggu Pengiriman]
    Kirim --> Reg[Staf registrasi, nomor, stempel]
    Reg --> Selesai[Status: Dikirim ke BKPSDMD]
    Selesai --> Unduh[Pegawai unduh surat] --> End4([Selesai])
```

---

## 3. Activity Diagram — Presensi Apel

```mermaid
flowchart TD
    S([Mulai]) --> L[Pegawai login]
    L --> J{Dalam jendela apel?<br/>pagi 07.00-08.30 / sore 15.30-17.00 WITA}
    J -- Tidak --> Off[Tombol presensi dinonaktifkan + notifikasi] --> E1([Selesai])
    J -- Ya --> Sesi[Tentukan sesi aktif: PAGI / SORE]
    Sesi --> Dup{Sudah presensi sesi ini hari ini?}
    Dup -- Ya --> Tolak[Tolak: presensi ganda] --> E1
    Dup -- Tidak --> Buka[Buka menu Presensi Apel]
    Buka --> Izin{Izin kamera diberikan?}
    Izin -- Tidak --> Upload[Unggah foto dari galeri]
    Izin -- Ya --> Foto[Ambil foto via kamera]
    Foto --> Prev[Pratinjau foto]
    Upload --> Prev
    Prev --> Kirim[Tekan Kirim Absensi]
    Kirim --> Simpan[Simpan sesi + timestamp + foto terkompresi]
    Simpan --> Status[Status: Hadir - Menunggu Validasi]
    Status --> Rekap[Rekap otomatis per pekan] --> E2([Selesai])
```

---

## 4. Sequence Diagram — Login SSO

```mermaid
sequenceDiagram
    actor U as Pengguna
    participant V as View (Browser)
    participant C as AuthController
    participant M as User Model
    participant DB as Database

    U->>V: Masukkan NIP/Username + Password
    V->>C: POST /login (kredensial)
    C->>M: cari user by NIP/username
    M->>DB: SELECT user + role
    DB-->>M: data user
    M-->>C: user (hash password)
    C->>C: verifikasi hash (Bcrypt/Argon2)
    alt kredensial valid & akun aktif
        C->>V: buat sesi + muat role, redirect dashboard
        V-->>U: Tampilkan dashboard sesuai peran
    else gagal / akun nonaktif
        C->>V: pesan error (+hitung percobaan)
        V-->>U: Tampilkan notifikasi gagal login
    end
```

---

## 5. Sequence Diagram — Pengajuan Layanan + Approval

```mermaid
sequenceDiagram
    actor P as Pegawai
    actor Vr as Verifikator
    actor K as Kasi/Kasubag
    actor Sk as Sekcam
    actor Cm as Camat/Lurah
    participant FE as Frontend
    participant API as Laravel API
    participant DB as Database

    P->>FE: Isi form + unggah berkas
    FE->>API: POST /pengajuan (multipart)
    API->>API: validasi field & berkas wajib
    API->>DB: INSERT pengajuan + berkas (status: Menunggu Verifikasi)
    DB-->>API: OK (id pengajuan)
    API-->>FE: sukses + nomor pengajuan
    FE-->>P: Tampilkan status

    Vr->>FE: Buka antrean verifikasi
    FE->>API: GET /verifikasi
    Vr->>API: POST /verifikasi/{id} (validasi & teruskan)
    API->>DB: UPDATE status -> Menunggu Persetujuan Kasi/Kasubag
    K->>API: POST /approval/tier1/{id} (setujui)
    API->>DB: UPDATE status -> Menunggu Paraf Sekcam
    Sk->>API: POST /approval/tier2/{id} (verifikasi & paraf)
    API->>DB: UPDATE status -> Menunggu Persetujuan Camat/Lurah
    Cm->>API: POST /approval/final/{id} (setujui akhir)
    API->>DB: UPDATE status -> Menunggu Pengiriman
    Vr->>API: POST /approval/kirim/{id} (registrasi & kirim)
    API->>DB: UPDATE status -> Dikirim ke BKPSDMD
    API-->>P: Notifikasi selesai + tautan unduh surat
```

---

## 6. Class Diagram

```mermaid
classDiagram
    class User {
        +int id
        +string nip
        +string nama
        +string username
        +string password_hash
        +int role_id
        +int unit_kerja_id
        +int atasan_id
        +enum status
        +login()
        +updateProfil()
        +ubahPassword()
    }
    class Role {
        +int id
        +string nama_role
        +json permissions
    }
    class UnitKerja {
        +int id
        +string nama
        +enum tipe
        +int induk_id
    }
    class Pengajuan {
        +int id
        +int pegawai_id
        +enum jenis_layanan
        +json data_form
        +enum status
        +date tanggal_ajuan
        +string nomor_ajuan
        +buat()
        +perbaruiStatus()
    }
    class Berkas {
        +int id
        +int pengajuan_id
        +string nama_file
        +string path
        +string tipe
    }
    class RiwayatApproval {
        +int id
        +int pengajuan_id
        +int pejabat_id
        +enum tahap
        +enum keputusan
        +text catatan
        +datetime waktu
    }
    class PresensiApel {
        +int id
        +int pegawai_id
        +date tanggal
        +time jam
        +string foto_path
        +enum status_validasi
        +int validasi_oleh
    }
    class SuratMasuk {
        +int id
        +string nomor_surat
        +string klasifikasi
        +date tanggal
        +string asal
        +string perihal
    }
    class Laporan {
        +generatePresensi()
        +generateLayanan()
        +eksporPDF()
        +eksporExcel()
    }

    User "1" --> "1" Role : memiliki
    User "*" --> "1" UnitKerja : bertugas di
    User "1" --> "0..1" User : atasan langsung
    User "1" --> "*" Pengajuan : mengajukan
    Pengajuan "1" --> "*" Berkas : melampirkan
    Pengajuan "1" --> "*" RiwayatApproval : diproses
    User "1" --> "*" PresensiApel : mencatat
    Laporan ..> PresensiApel : merekap
    Laporan ..> Pengajuan : merekap
```

---

## 7. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    ROLE ||--o{ USER : memiliki
    UNIT_KERJA ||--o{ USER : menaungi
    USER ||--o{ USER : "atasan langsung"
    USER ||--o{ PENGAJUAN : mengajukan
    PENGAJUAN ||--o{ BERKAS : melampirkan
    PENGAJUAN ||--o{ RIWAYAT_APPROVAL : diproses
    USER ||--o{ RIWAYAT_APPROVAL : memutuskan
    USER ||--o{ PRESENSI_APEL : mencatat
    USER ||--o{ PRESENSI_APEL : memvalidasi
    UNIT_KERJA ||--o{ PENGAJUAN : berasal

    ROLE {
        int id PK
        string nama_role
        json permissions
    }
    UNIT_KERJA {
        int id PK
        string nama
        enum tipe "kecamatan/kelurahan/seksi/subbag"
        int induk_id FK
    }
    USER {
        int id PK
        string nip UK
        string nama
        string username UK
        string password_hash
        int role_id FK
        int unit_kerja_id FK
        int atasan_id FK
        string jabatan
        enum jenis_pegawai "PNS/PPPK-Penuh/PPPK-Paruh"
        enum status "aktif/nonaktif"
    }
    PENGAJUAN {
        int id PK
        int pegawai_id FK
        int unit_kerja_id FK
        enum jenis_layanan "cuti/kgb/pangkat/pensiun"
        json data_form
        enum status
        string nomor_ajuan
        date tanggal_ajuan
        string nomor_agenda
    }
    BERKAS {
        int id PK
        int pengajuan_id FK
        string nama_file
        string path
        string tipe
        int ukuran
    }
    RIWAYAT_APPROVAL {
        int id PK
        int pengajuan_id FK
        int pejabat_id FK
        enum tahap "verifikasi/tier1/tier2/final/pengiriman"
        enum keputusan "setuju/tolak/revisi/dikirim"
        text catatan
        datetime waktu
    }
    PRESENSI_APEL {
        int id PK
        int pegawai_id FK
        date tanggal
        enum sesi "pagi/sore"
        time jam
        string foto_path
        enum status_validasi "menunggu/hadir/izin/alpa"
        int validasi_oleh FK
    }
```
