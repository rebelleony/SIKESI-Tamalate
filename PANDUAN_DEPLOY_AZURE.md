# Panduan Lengkap Deploy SI Kepegawaian Tamalate ke Microsoft Azure

Dokumen ini berisi panduan langkah demi langkah untuk men-deploy aplikasi **Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu Kecamatan Tamalate (SIKESI Tamalate)** ke layanan cloud **Microsoft Azure**.

---

## Daftar Isi
1. [Pilihan Arsitektur Deployment di Azure](#1-pilihan-arsitektur-deployment-di-azure)
2. [Opsi 1: Deploy di Azure App Service (PaaS - Paling Praktis & Resmi)](#2-opsi-1-deploy-di-azure-app-service-rekomendasi-utama)
   - [Langkah 1: Membuat Azure Resource Group & Database](#langkah-1-membuat-resource-group--database)
   - [Langkah 2: Membuat Azure App Service (Web App for Containers)](#langkah-2-membuat-azure-app-service)
   - [Langkah 3: Konfigurasi Environment Variables (Application Settings)](#langkah-3-konfigurasi-environment-variables)
   - [Langkah 4: Deploy Menggunakan GitHub Actions / Docker Hub](#langkah-4-deploy-aplikasi)
3. [Opsi 2: Deploy di Azure Virtual Machine (VM) + Docker Compose (Paling Hemat Biaya)](#3-opsi-2-deploy-di-azure-virtual-machine-vm-paling-hemat-biaya)
   - [Langkah 1: Membuat Instance Azure VM (Free Tier Standard_B1s)](#langkah-1-membuat-instance-azure-vm)
   - [Langkah 2: Mengatur Inbound Port Rules (Firewall)](#langkah-2-mengatur-inbound-port-rules-firewall)
   - [Langkah 3: Menjalankan Script Otomatis (setup-azure-vm.sh)](#langkah-3-menjalankan-script-otomatis)
4. [Akun Login Bawaan (Hasil Seeder Demo)](#4-akun-login-bawaan-hasil-seeder-demo)
5. [Konfigurasi Custom Domain & SSL / HTTPS Gratis](#5-konfigurasi-custom-domain--ssl--https-gratis)
6. [Perawatan, Pemeliharaan & Troubleshooting](#6-perawatan-pemeliharaan--troubleshooting)

---

## 1. Pilihan Arsitektur Deployment di Azure

| Opsi | Layanan Azure | Kelebihan | Estimasi Biaya |
| :--- | :--- | :--- | :--- |
| **Opsi 1 (Disarankan)** | **Azure App Service (Web App for Containers)** | PaaS resmi, otomatis dapat HTTPS (*.azurewebsites.net), auto-scaling, tidak perlu urus OS Linux. | App Service Plan B1 / Basic (atau F1 Free tier untuk web biasa) |
| **Opsi 2 (Hemat Biaya)** | **Azure Virtual Machine (Ubuntu 24.04 LTS)** + **Docker Compose** | Kontrol penuh, database MySQL & storage berkas ada di server yang sama, sangat mirip VPS. | **Gratis (Free Account B1s 12 Bulan)** / ~$4 - $8/bulan |
| **Opsi 3** | **Azure Container Apps (ACA)** | Serverless container modern, scale-to-zero, ingress otomatis port 80. | Pay-as-you-go |

---

## 2. Opsi 1: Deploy di Azure App Service (Rekomendasi Utama)

### Langkah 1: Membuat Resource Group & Database

1. Buka [Azure Portal](https://portal.azure.com/) dan login dengan akun Microsoft Azure Anda.
2. Di kolom pencarian atas, ketik **Resource groups** > klik **Create**:
   - **Subscription**: Pilih langganan Anda (misal: *Azure for Students* atau *Pay-As-You-Go*).
   - **Resource group**: `rg-tamalate-prod`
   - **Region**: `Southeast Asia` (Singapura)
   - Klik **Review + create** > **Create**.

3. *(Opsional - Jika ingin Database MySQL Terkelola)*:
   - Buat **Azure Database for MySQL (Flexible Server)**:
     - Server name: `tamalate-db`
     - Compute + storage: `Burstable, B1ms` (opsi hemat biaya)
     - Authentication: MySQL authentication only (buat username & password, misal `tamalateadmin` / `PasswordKuatAzure2026!`)
     - Pada tab **Networking**: Aktifkan centang **Allow public access from any Azure service within Azure to this server**.
     - Buat database dengan nama: `si_kepegawaian_tamalate`.

---

### Langkah 2: Membuat Azure App Service

1. Di Azure Portal, cari **App Services** > klik **Create** > **Web App**.
2. Masukkan konfigurasi:
   - **Resource Group**: `rg-tamalate-prod`
   - **Name**: `tamalate-app` (akan menjadi `https://tamalate-app.azurewebsites.net`)
   - **Publish**: Pilih **Container** (atau *Docker Container*)
   - **Operating System**: **Linux**
   - **Region**: `Southeast Asia`
   - **Pricing Plan**: Pilih `Basic B1` (atau `Free F1` jika tersedia).
3. Tab **Container**:
   - **Image Source**: Docker Hub atau GitHub Container Registry.
   - (Atau jika sudah push image ke Docker Hub: masukkan image name Anda, misal `username/tamalate-app:latest`).
4. Klik **Review + create** > **Create**.

---

### Langkah 3: Konfigurasi Environment Variables

Buka Web App Anda di Azure Portal > menu bilah kiri **Settings** > **Environment variables** (atau **Configuration** > **Application settings**):

Tambahkan variabel-variabel berikut:

| Name | Value | Keterangan |
| :--- | :--- | :--- |
| `WEBSITES_PORT` | `80` | **Wajib:** Mengarahkan lalu lintas Azure ke Port 80 container Apache |
| `WEBSITES_ENABLE_APP_SERVICE_STORAGE` | `true` | Mempertahankan berkas upload |
| `APP_NAME` | `SI Kepegawaian Tamalate` | Nama aplikasi |
| `APP_ENV` | `production` | Mode production |
| `APP_DEBUG` | `false` | Matikan debug mode |
| `APP_KEY` | `base64:4YAIZ+lxuXrTb7wjHeAA9WvlW2b5smipOWaqmwOL8VM=` | Kunci enkripsi aplikasi |
| `APP_URL` | `https://tamalate-app.azurewebsites.net` | URL resmi Azure Anda |
| `APP_TIMEZONE` | `Asia/Makassar` | Zona waktu WITA |
| `DB_CONNECTION` | `mysql` | Atau `sqlite` jika tanpa MySQL |
| `DB_HOST` | `tamalate-db.mysql.database.azure.com` | Host database Flexible Server |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_DATABASE` | `si_kepegawaian_tamalate` | Nama database |
| `DB_USERNAME` | `tamalateadmin` | User DB |
| `DB_PASSWORD` | `PasswordKuatAzure2026!` | Password DB |
| `RUN_MIGRATIONS` | `true` | Menjalankan migrasi saat start |
| `RUN_SEEDER` | `true` | Menjalankan seeder awal |

Klik **Apply** dan simpan konfigurasi.

---

### Langkah 4: Deploy Aplikasi

Aplikasi Anda kini sudah aktif dan otomatis memiliki sertifikat HTTPS valid dari Microsoft Azure:
```
https://tamalate-app.azurewebsites.net
```

---

## 3. Opsi 2: Deploy di Azure Virtual Machine (VM) (Paling Hemat Biaya)

Opsi ini sangat direkomendasikan jika Anda ingin database MySQL dan penyimpanan berkas berada di dalam satu server tanpa biaya tambahan database terpisah.

### Langkah 1: Membuat Instance Azure VM
1. Buka Azure Portal > ketik **Virtual machines** > klik **Create** > **Azure virtual machine**.
2. Konfigurasi:
   - **Resource group**: `rg-tamalate-prod`
   - **Virtual machine name**: `vm-tamalate-server`
   - **Region**: `Southeast Asia` (Singapura)
   - **Image**: **Ubuntu Server 24.04 LTS - x64 Gen2**
   - **Size**: `Standard_B1s` (1 vCPU, 1 GiB RAM - *Free Tier Eligible*) atau `Standard_B2s` (2 vCPU, 4 GiB RAM)
   - **Authentication type**: SSH public key (atau Password untuk kemudahan login)
   - **Username**: `azureuser`
   - **Disks**: OS disk size `30 GiB` atau `64 GiB` (Standard SSD)

### Langkah 2: Mengatur Inbound Port Rules (Firewall)
Pada tab **Networking** > **Inbound port rules**:
- Centang izin port:
  - ✅ **SSH (22)**
  - ✅ **HTTP (80)**
  - ✅ **HTTPS (443)**

Klik **Review + create** > **Create**.

---

### Langkah 3: Menjalankan Script Otomatis

1. Setelah VM aktif, salin **Public IP address** dari halaman Overview VM.
2. Sambungkan ke VM melalui terminal laptop Anda:
   ```bash
   ssh azureuser@<IP_PUBLIK_AZURE_VM>
   ```
3. Jalankan perintah deploy sekali jalan:
   ```bash
   # 1. Kloning repositori
   git clone https://github.com/rebelleony/SIKESI-Tamalate.git ~/SIKESI-Tamalate

   # 2. Masuk ke direktori
   cd ~/SIKESI-Tamalate

   # 3. Jalankan setup otomatis
   sudo bash setup-azure-vm.sh
   ```

**Skrip `setup-azure-vm.sh` akan otomatis:**
- Mengaktifkan **2GB Swap Memory** agar VM B1s tidak kehabisan RAM.
- Menginstal Docker Engine & Docker Compose plugin.
- Menyusun file `.env` dengan konfigurasi MySQL dan Laravel.
- Menjalankan migrasi database dan seeder data awal.
- Menjalankan kontainer aplikasi di port 80.

Buka browser dan akses:
```text
http://<IP_PUBLIK_AZURE_VM>
```
Aplikasi Anda berhasil live di Microsoft Azure! 🎉

---

## 4. Akun Login Bawaan (Hasil Seeder Demo)

Setelah server aktif, database otomatis terisi data awal dan akun demo untuk menguji 5 peran (*Role-Based Access Control*):

> **Password default untuk semua akun adalah:** `password`

| Username | Peran (*Role*) | Deskripsi Hak Akses |
| :--- | :--- | :--- |
| **admin** | Super Admin | Kelola data master unit kerja, pengguna, audit log & distribusi pengajuan |
| **camat** | Camat / Lurah | Persetujuan akhir (*approval*) dan pengesahan tanda tangan elektronik |
| **seksam** | Camat / Lurah | Sekretaris Camat (persetujuan tingkat akhir kedua) |
| **kasi** | Kasi / Kasubag | Verifikasi awal dan persetujuan tingkat pertama |
| **verifikator** | Verifikator | Verifikasi berkas dokumen pengajuan & rekapitulasi presensi |
| **pegawai** | Pegawai / Staf | Melakukan pengajuan izin/cuti/surat dan presensi apel |

---

## 5. Konfigurasi Custom Domain & SSL / HTTPS Gratis

Jika Anda memiliki domain sendiri (misalnya `kepegawaian.tamalate.id`):

### Jika Menggunakan Azure App Service:
1. Masuk ke Web App Anda di Azure Portal > menu **Custom domains**.
2. Klik **Add custom domain** dan ikuti instruksi DNS (menambahkan CNAME ke `tamalate-app.azurewebsites.net`).
3. Pada tab **Certificates**, klik **Create App Service Managed Certificate**. Azure akan memberikan sertifikat SSL resmi **100% gratis dan diperpanjang otomatis** selamanya!

### Jika Menggunakan Azure Virtual Machine:
Point IP publik VM ke domain Anda (A Record), lalu jalankan Nginx SSL yang telah disediakan:
```bash
# Ganti nama domain di nginx/nginx.conf, lalu jalankan:
docker compose -f docker-compose.prod-ssl.yml up -d
```
Atau jika menggunakan **Cloudflare**, aktifkan Proxy (Orange Cloud) untuk HTTPS instan gratis!

---

## 6. Perawatan, Pemeliharaan & Troubleshooting

### Perintah Berguna di Azure VM:
- **Melihat status kontainer**:
  ```bash
  docker compose ps
  ```
- **Melihat live logs aplikasi**:
  ```bash
  docker compose logs -f app
  ```
- **Update aplikasi setelah push git**:
  ```bash
  git pull origin main
  docker compose up -d --build
  ```
- **Backup database**:
  ```bash
  docker exec -t tamalate_db mysqldump -u root -proot_secret_2026 si_kepegawaian_tamalate > backup_$(date +%F).sql
  ```

### Troubleshooting di Azure App Service:
1. **Container tidak menyala / timeout**:
   - Pastikan variabel `WEBSITES_PORT=80` sudah disetel di Application Settings.
   - Buka menu **Log stream** di Azure Portal untuk melihat error spesifik.
2. **Koneksi ke Azure Database for MySQL Gagal**:
   - Pastikan di menu **Networking** server MySQL Flexible Server, opsi **Allow public access from any Azure service within Azure to this server** sudah dicentang.
3. **Tampilan CSS/JS berantakan**:
   - Proyek ini sudah otomatis menyetel `$middleware->trustProxies(at: '*')` di `app-laravel/bootstrap/app.php`. Pastikan `APP_URL` di Environment Variables diawali dengan `https://`.

