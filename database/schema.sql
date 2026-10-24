-- =====================================================================
-- SKEMA BASIS DATA
-- Sistem Informasi Pelayanan Administrasi Kepegawaian & Presensi Terpadu
-- Kecamatan Tamalate
-- Target DBMS: MySQL 8+ (kompatibel MariaDB 10.6+)
-- Charset: utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS si_kepegawaian_tamalate
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE si_kepegawaian_tamalate;

-- ---------------------------------------------------------------------
-- 1. ROLE  (RBAC)
-- ---------------------------------------------------------------------
CREATE TABLE role (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_role     VARCHAR(50)  NOT NULL UNIQUE,   -- pegawai, verifikator, kasi_kasubag, seksam, camat_lurah, super_admin
  deskripsi     VARCHAR(255) NULL,
  permissions   JSON         NULL,              -- daftar permission granular
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. UNIT_KERJA  (kecamatan / kelurahan / seksi / subbag) - self ref
-- ---------------------------------------------------------------------
CREATE TABLE unit_kerja (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(120) NOT NULL,
  tipe          ENUM('kecamatan','kelurahan','seksi','subbag') NOT NULL,
  induk_id      INT UNSIGNED NULL,              -- hierarki unit (FK ke dirinya)
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_unit_induk FOREIGN KEY (induk_id) REFERENCES unit_kerja(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. USER  (pegawai + akun login)
-- ---------------------------------------------------------------------
CREATE TABLE user (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nip             VARCHAR(30)  NOT NULL UNIQUE,
  nama            VARCHAR(120) NOT NULL,
  username        VARCHAR(60)  NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,        -- Bcrypt / Argon2
  role_id         INT UNSIGNED NOT NULL,
  unit_kerja_id   INT UNSIGNED NULL,
  unit_kerja_nama VARCHAR(160) NULL,            -- nama unit kerja mentah dari data mitra
  atasan_id       INT UNSIGNED NULL,            -- atasan langsung (self ref)
  jabatan         VARCHAR(120) NULL,
  tmt_jabatan     DATE         NULL,
  pangkat_gol     VARCHAR(30)  NULL,            -- mis. Pembina, IV/a
  tmt_golongan    DATE         NULL,
  eselon          VARCHAR(30)  NULL,
  jenis_pegawai   ENUM('PNS','PPPK_PENUH','PPPK_PARUH_WAKTU') NOT NULL DEFAULT 'PNS',
  status_pegawai  VARCHAR(30)  NULL,            -- status mentah dari data (mis. "PPPK PW")
  tmt_pegawai     DATE         NULL,
  tanggal_lahir   DATE         NULL,
  tempat_lahir    VARCHAR(80)  NULL,
  tmt_cpns        DATE         NULL,            -- terhitung mulai tanggal CPNS
  tmt_pns         DATE         NULL,
  masa_kerja_tahun INT         NOT NULL DEFAULT 0,
  masa_kerja_bulan INT         NOT NULL DEFAULT 0,
  gaji_pokok      DECIMAL(12,2) NULL,
  jenis_kelamin   ENUM('L','P') NULL,
  agama           VARCHAR(30)  NULL,
  status_perkawinan VARCHAR(20) NULL,
  pendidikan_awal VARCHAR(60)  NULL,
  pendidikan_akhir VARCHAR(60) NULL,
  no_askes        VARCHAR(40)  NULL,
  npwp            VARCHAR(30)  NULL,
  no_ktp          VARCHAR(20)  NULL,
  alamat          VARCHAR(255) NULL,
  no_telp         VARCHAR(25)  NULL,
  no_hp           VARCHAR(20)  NULL,
  email           VARCHAR(120) NULL,
  foto_profil     VARCHAR(255) NULL,
  status          ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  login_gagal     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  terkunci_sampai DATETIME     NULL,
  last_login      DATETIME     NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_role  FOREIGN KEY (role_id)       REFERENCES role(id)       ON DELETE RESTRICT,
  CONSTRAINT fk_user_unit  FOREIGN KEY (unit_kerja_id) REFERENCES unit_kerja(id) ON DELETE SET NULL,
  CONSTRAINT fk_user_atasan FOREIGN KEY (atasan_id)    REFERENCES user(id)       ON DELETE SET NULL,
  INDEX idx_user_unit (unit_kerja_id),
  INDEX idx_user_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. MASTER JENIS CUTI  (referensi form cuti)
-- ---------------------------------------------------------------------
CREATE TABLE jenis_cuti (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(80) NOT NULL UNIQUE,    -- Cuti Tahunan, Cuti Besar, Cuti Sakit, dst.
  max_hari      INT NULL,
  keterangan    VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. PENGAJUAN  (cuti / kgb / kenaikan pangkat / pensiun)
-- ---------------------------------------------------------------------
CREATE TABLE pengajuan (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_ajuan     VARCHAR(50)  NULL UNIQUE,     -- nomor otomatis saat submit
  pegawai_id      INT UNSIGNED NOT NULL,
  unit_kerja_id   INT UNSIGNED NULL,
  jenis_layanan   ENUM('cuti','kgb','kenaikan_pangkat','pensiun') NOT NULL,
  data_form       JSON         NOT NULL,        -- isi form spesifik per jenis layanan
  status          ENUM('draf','menunggu_verifikasi','perlu_revisi',
                       'menunggu_approval_tier1','menunggu_approval_tier2','menunggu_approval_final',
                       'menunggu_pengiriman','dikirim_bkpsdm',
                       'disetujui','ditolak_tier1','ditolak_tier2','ditolak_final') NOT NULL DEFAULT 'draf',
  tanggal_ajuan   DATE         NULL,
  catatan         TEXT         NULL,
  file_output     VARCHAR(255) NULL,            -- surat/SK hasil generate (PDF)
  nomor_agenda    VARCHAR(60)  NULL,             -- nomor agenda surat keluar saat kirim ke BKPSDMD
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pengajuan_pegawai FOREIGN KEY (pegawai_id)    REFERENCES user(id)       ON DELETE CASCADE,
  CONSTRAINT fk_pengajuan_unit    FOREIGN KEY (unit_kerja_id) REFERENCES unit_kerja(id) ON DELETE SET NULL,
  INDEX idx_pengajuan_status (status),
  INDEX idx_pengajuan_jenis (jenis_layanan),
  INDEX idx_pengajuan_tanggal (tanggal_ajuan)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. BERKAS  (dokumen persyaratan terlampir pada pengajuan)
-- ---------------------------------------------------------------------
CREATE TABLE berkas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pengajuan_id  INT UNSIGNED NOT NULL,
  nama_file     VARCHAR(160) NOT NULL,
  path          VARCHAR(255) NOT NULL,          -- path di storage terisolasi
  tipe          VARCHAR(50)  NULL,              -- mis. application/pdf, image/jpeg
  ukuran        INT UNSIGNED NULL,              -- bytes
  kategori      VARCHAR(80)  NULL,              -- mis. SK_terakhir, ijazah, surat_permohonan
  versi         SMALLINT UNSIGNED NOT NULL DEFAULT 1,  -- untuk berkas hasil revisi
  uploaded_by   INT UNSIGNED NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_berkas_pengajuan FOREIGN KEY (pengajuan_id) REFERENCES pengajuan(id) ON DELETE CASCADE,
  CONSTRAINT fk_berkas_user      FOREIGN KEY (uploaded_by)  REFERENCES user(id)       ON DELETE RESTRICT,
  INDEX idx_berkas_pengajuan (pengajuan_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. RIWAYAT_APPROVAL  (jejak tiap tahap: verifikasi / tier1 / tier2 / final / pengiriman)
-- ---------------------------------------------------------------------
CREATE TABLE riwayat_approval (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pengajuan_id  INT UNSIGNED NOT NULL,
  pejabat_id    INT UNSIGNED NOT NULL,
  tahap         ENUM('verifikasi','approval_tier1','approval_tier2','approval_final','pengiriman') NOT NULL,
  keputusan     ENUM('teruskan','setuju','tolak','revisi','dikirim') NOT NULL,
  catatan       TEXT NULL,
  waktu         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_riwayat_pengajuan FOREIGN KEY (pengajuan_id) REFERENCES pengajuan(id) ON DELETE CASCADE,
  CONSTRAINT fk_riwayat_pejabat   FOREIGN KEY (pejabat_id)   REFERENCES user(id)       ON DELETE RESTRICT,
  INDEX idx_riwayat_pengajuan (pengajuan_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. PRESENSI_APEL  (absensi apel pagi & sore berbasis foto)
-- ---------------------------------------------------------------------
CREATE TABLE presensi_apel (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pegawai_id      INT UNSIGNED NOT NULL,
  tanggal         DATE NOT NULL,
  sesi            ENUM('pagi','sore') NOT NULL DEFAULT 'pagi',  -- apel pagi 07.00-08.30 / sore 15.30-17.00 WITA
  jam             TIME NOT NULL,                -- timestamp saat kirim
  foto_path       VARCHAR(255) NOT NULL,        -- foto terkompresi di storage
  status_validasi ENUM('menunggu','hadir','izin','sakit','alpa') NOT NULL DEFAULT 'menunggu',
  validasi_oleh   INT UNSIGNED NULL,
  waktu_validasi  DATETIME NULL,
  keterangan      VARCHAR(255) NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_presensi_pegawai  FOREIGN KEY (pegawai_id)    REFERENCES user(id) ON DELETE CASCADE,
  CONSTRAINT fk_presensi_verifier FOREIGN KEY (validasi_oleh) REFERENCES user(id) ON DELETE SET NULL,
  UNIQUE KEY uq_presensi_sesi (pegawai_id, tanggal, sesi),  -- 1 pegawai 1 apel per sesi per hari
  INDEX idx_presensi_tanggal (tanggal)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. SURAT_MASUK  (penanganan + klasifikasi & penomoran)
-- ---------------------------------------------------------------------
CREATE TABLE surat_masuk (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_surat     VARCHAR(60)  NOT NULL,
  nomor_agenda    VARCHAR(60)  NULL UNIQUE,     -- nomor agenda internal otomatis
  klasifikasi     VARCHAR(60)  NULL,            -- sesuai SE Klasifikasi & Penomoran
  tanggal_surat   DATE         NOT NULL,
  tanggal_terima  DATE         NOT NULL,
  asal_surat      VARCHAR(160) NULL,
  tujuan          VARCHAR(160) NULL,
  perihal         VARCHAR(255) NOT NULL,
  sifat           ENUM('biasa','penting','segera','rahasia') NOT NULL DEFAULT 'biasa',
  file_path       VARCHAR(255) NULL,
  disposisi       TEXT         NULL,
  status          ENUM('dicatat','diparaf_sekcam','didisposisi','didistribusikan','diarsipkan')
                               NOT NULL DEFAULT 'dicatat',  -- alur SOP surat masuk
  tujuan_disposisi TEXT        NULL,            -- isi disposisi Camat
  paraf_oleh      INT UNSIGNED NULL,            -- Sekretaris Camat (paraf)
  paraf_waktu     DATETIME     NULL,
  disposisi_oleh  INT UNSIGNED NULL,            -- Camat (tanda tangan disposisi)
  disposisi_waktu DATETIME     NULL,
  distribusi_oleh INT UNSIGNED NULL,            -- pelaksana yang mendistribusikan
  distribusi_waktu DATETIME    NULL,
  dicatat_oleh    INT UNSIGNED NOT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_surat_user FOREIGN KEY (dicatat_oleh) REFERENCES user(id) ON DELETE RESTRICT,
  CONSTRAINT fk_surat_paraf FOREIGN KEY (paraf_oleh) REFERENCES user(id) ON DELETE SET NULL,
  CONSTRAINT fk_surat_disposisi FOREIGN KEY (disposisi_oleh) REFERENCES user(id) ON DELETE SET NULL,
  CONSTRAINT fk_surat_distribusi FOREIGN KEY (distribusi_oleh) REFERENCES user(id) ON DELETE SET NULL,
  INDEX idx_surat_tanggal (tanggal_terima),
  INDEX idx_surat_klasifikasi (klasifikasi),
  INDEX idx_surat_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9b. ARSIP  (pengarsipan surat masuk/keluar & dokumen lain)
-- ---------------------------------------------------------------------
CREATE TABLE arsip (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  surat_masuk_id  INT UNSIGNED NULL,            -- sumber: surat masuk
  pengajuan_id    INT UNSIGNED NULL,            -- sumber: output pengajuan (surat keluar)
  jenis_dokumen   ENUM('surat_masuk','surat_keluar','dokumen_lain') NOT NULL DEFAULT 'surat_masuk',
  judul           VARCHAR(160) NOT NULL,
  kode_arsip      VARCHAR(60)  NOT NULL,        -- kode sesuai tata naskah
  kategori        VARCHAR(80)  NULL,
  status_dokumen  ENUM('aktif','tidak_aktif','penting') NOT NULL DEFAULT 'aktif',
  lokasi          ENUM('lemari_arsip','gudang_arsip','lurah') NOT NULL DEFAULT 'lemari_arsip',
  nomor_daftar_arsip VARCHAR(60) NULL,          -- nomor pada Daftar Arsip
  ditandatangai_oleh INT UNSIGNED NULL,         -- Sekcam/Camat penanda tangan Daftar Arsip
  dicatat_oleh    INT UNSIGNED NOT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_arsip_surat FOREIGN KEY (surat_masuk_id) REFERENCES surat_masuk(id) ON DELETE SET NULL,
  CONSTRAINT fk_arsip_pengajuan FOREIGN KEY (pengajuan_id) REFERENCES pengajuan(id) ON DELETE SET NULL,
  CONSTRAINT fk_arsip_ttd FOREIGN KEY (ditandatangai_oleh) REFERENCES user(id) ON DELETE SET NULL,
  CONSTRAINT fk_arsip_user FOREIGN KEY (dicatat_oleh) REFERENCES user(id) ON DELETE RESTRICT,
  INDEX idx_arsip_kode (kode_arsip)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. AUDIT_LOG  (jejak aktivitas sistem - opsional tapi dianjurkan)
-- ---------------------------------------------------------------------
CREATE TABLE audit_log (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NULL,
  aksi          VARCHAR(80)  NOT NULL,          -- login, create_pengajuan, approve, dll
  entitas       VARCHAR(60)  NULL,
  entitas_id    INT UNSIGNED NULL,
  deskripsi     VARCHAR(255) NULL,
  ip_address    VARCHAR(45)  NULL,
  waktu         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL,
  INDEX idx_audit_waktu (waktu)
) ENGINE=InnoDB;

-- =====================================================================
-- DATA AWAL (SEED)
-- =====================================================================
INSERT INTO role (nama_role, deskripsi) VALUES
  ('super_admin',   'Administrator sistem: kelola data master & akun'),
  ('verifikator',   'Staf kepegawaian/arsip: verifikasi berkas & laporan'),
  ('kasi_kasubag',  'Kepala Seksi / Kepala Sub-bagian: persetujuan tingkat pertama'),
  ('seksam',        'Sekretaris Camat: verifikasi & paraf draf (persetujuan tingkat kedua)'),
  ('camat_lurah',   'Camat / Lurah: persetujuan akhir'),
  ('pegawai',       'Pegawai/staf: pengajuan layanan & presensi apel');

INSERT INTO jenis_cuti (nama, max_hari, keterangan) VALUES
  ('Cuti Tahunan', 12, 'Maksimal 12 hari kerja per tahun'),
  ('Cuti Besar', NULL, 'Hak setelah masa kerja tertentu'),
  ('Cuti Sakit', NULL, 'Berdasarkan surat keterangan dokter'),
  ('Cuti Melahirkan', 90, 'Untuk pegawai wanita, 3 bulan'),
  ('Cuti Karena Alasan Penting', NULL, 'Sesuai ketentuan BKN'),
  ('Cuti Di Luar Tanggungan Negara', NULL, 'Maksimal 3 tahun');

INSERT INTO unit_kerja (nama, tipe, induk_id) VALUES
  ('Kecamatan Tamalate', 'kecamatan', NULL);

-- =====================================================================
-- CATATAN DESAIN
-- =====================================================================
-- 1. Status alur pada tabel `pengajuan` mengikuti rantai:
--    draf -> menunggu_verifikasi -> (perlu_revisi <-> menunggu_verifikasi)
--    -> menunggu_approval_tier1 -> menunggu_approval_final -> disetujui/ditolak.
-- 2. Aturan khusus pejabat (cuti Camat oleh Sekda, cuti Sekcam oleh Camat,
--    Camat/Sekcam tidak menyetujui diri sendiri) diimplementasikan pada
--    service layer backend, bukan pada skema DB.
-- 3. PPPK Paruh Waktu hanya dapat melakukan presensi_apel; pembatasan jenis
--    layanan diverifikasi pada backend berdasarkan kolom jenis_pegawai.
-- 4. File (berkas & foto apel) disimpan pada storage server terisolasi;
--    DB hanya menyimpan path/referensinya.
-- =====================================================================
