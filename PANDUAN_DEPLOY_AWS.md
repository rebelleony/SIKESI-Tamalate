# Panduan Lengkap Deploy SI Kepegawaian Tamalate ke AWS

Dokumen ini berisi panduan langkah demi langkah untuk men-deploy aplikasi **Sistem Informasi Pelayanan Administrasi Kepegawaian dan Presensi Terpadu Kecamatan Tamalate (SIKESI Tamalate)** ke layanan cloud **Amazon Web Services (AWS)**.

---

## Daftar Isi
1. [Pilihan Arsitektur Deployment AWS](#1-pilihan-arsitektur-deployment-aws)
2. [Opsi 1: Deploy di AWS EC2 (Paling Direkomendasikan & Hemat Biaya)](#2-opsi-1-deploy-di-aws-ec2-rekomendasi-utama)
   - [Langkah 1: Membuat Instance EC2 (Free Tier)](#langkah-1-membuat-instance-ec2)
   - [Langkah 2: Mengatur Security Group (Firewall)](#langkah-2-mengatur-security-group)
   - [Langkah 3: Koneksi ke Server dan Menjalankan Script Otomatis](#langkah-3-koneksi-ke-server-dan-deploy-otomatis)
   - [Langkah 4: Deploy Manual (Alternatif)](#langkah-4-deploy-manual-alternatif)
3. [Opsi 2: Deploy di AWS App Runner (Serverless Container)](#3-opsi-2-deploy-di-aws-app-runner-serverless)
4. [Akun Login Bawaan (Hasil Seeder Demo)](#4-akun-login-bawaan-hasil-seeder-demo)
5. [Cara Pointing Domain & Pasang SSL / HTTPS Gratis](#5-cara-pointing-domain--pasang-ssl--https-gratis)
6. [Perawatan, Pemeliharaan & Troubleshooting](#6-perawatan-pemeliharaan--troubleshooting)

---

## 1. Pilihan Arsitektur Deployment AWS

| Opsi | Layanan AWS | Kelebihan | Estimasi Biaya |
| :--- | :--- | :--- | :--- |
| **Opsi 1 (Disarankan)** | **AWS EC2 (Ubuntu 24.04 LTS)** + **Docker Compose** | Sangat mudah, kontrol penuh, persisten volume berkas & DB MySQL di server yang sama. | **Gratis (Free Tier 12 Bulan)** / ~$3 - $5/bulan |
| **Opsi 2** | **AWS Lightsail** + Docker | Sangat simpel, VPS AWS dengan harga tetap dan dashboard minimalis. | Mulai $3.50/bulan |
| **Opsi 3** | **AWS App Runner** + **AWS RDS MySQL** | Serverless, otomatis HTTPS, tidak perlu kelola OS Linux. | Sesuai pemakaian (pay-as-you-go) |

---

## 2. Opsi 1: Deploy di AWS EC2 (Rekomendasi Utama)

### Langkah 1: Membuat Instance EC2
1. Buka [AWS Management Console](https://console.aws.amazon.com/) dan masuk ke layanan **EC2**.
2. Pastikan region yang dipilih dekat dengan pengguna (misalnya: **ap-southeast-1 (Singapura)** atau **ap-southeast-3 (Jakarta)**).
3. Klik tombol **Launch Instance**.
4. Isi konfigurasi instance:
   - **Name**: `tamalate-kepegawaian-server`
   - **Application and OS Images (AMI)**: Pilih **Ubuntu**, versi **Ubuntu Server 24.04 LTS (HVM)** (Free Tier eligible).
   - **Instance Type**: Pilih `t2.micro` atau `t3.micro` (Free Tier eligible: RAM 1 GB, 1 vCPU).
   - **Key pair (login)**:
     - Pilih *Create new key pair* jika belum punya (misal nama: `tamalate-key`), tipe `RSA`, format `.pem` (untuk OpenSSH / Mac / Linux) atau `.ppk` (jika menggunakan PuTTY di Windows).
     - Unduh dan simpan file key pair tersebut di tempat aman.
   - **Configure Storage**: Atur ke **20 GiB** atau **30 GiB** `gp3` (Free Tier mencakup hingga 30 GB EBS).

---

### Langkah 2: Mengatur Security Group
Pada bagian **Network Settings**:
1. Pilih **Create security group**.
2. Centang izin lalu lintas:
   - ✅ **Allow SSH traffic from**: Pilih `My IP` (atau `Anywhere 0.0.0.0/0` jika IP sering berubah).
   - ✅ **Allow HTTP traffic from the internet** (Port 80).
   - ✅ **Allow HTTPS traffic from the internet** (Port 443).

Ringkasan Inbound Rules:
- **Port 22 (SSH)**: Akses remote terminal.
- **Port 80 (HTTP)**: Akses web aplikasi.
- **Port 443 (HTTPS)**: Akses web aman via SSL.

Klik **Launch Instance**. Tunggu sekitar 1–2 menit sampai status instance menjadi **Running**.

---

### Langkah 3: Koneksi ke Server dan Deploy Otomatis

#### Cara A: Menggunakan EC2 Instance Connect (Langsung dari Browser - Paling Mudah)
1. Di daftar instance EC2, pilih instance yang baru dibuat.
2. Klik tombol **Connect** di bagian kanan atas.
3. Pilih tab **EC2 Instance Connect** lalu klik tombol **Connect**. Tab terminal hitam di browser akan terbuka.

#### Cara B: Menggunakan Terminal / SSH (Dari Laptop)
```bash
# Berikan permission yang aman pada file key (Linux / Mac / Git Bash)
chmod 400 tamalate-key.pem

# SSH ke EC2 (Ganti <IP_PUBLIK_EC2> dengan IP Public IPv4 instance Anda)
ssh -i tamalate-key.pem ubuntu@<IP_PUBLIK_EC2>
```

#### Jalankan Perintah Deploy Sekali Jalan:
Setelah masuk ke terminal EC2, jalankan perintah berikut:

```bash
# 1. Kloning repository proyek
git clone https://github.com/rebelleony/SIKESI-Tamalate.git ~/SIKESI-Tamalate

# 2. Masuk ke direktori
cd ~/SIKESI-Tamalate

# 3. Jalankan script setup otomatis
sudo bash setup-ec2.sh
```

**Apa yang dilakukan script `setup-ec2.sh` secara otomatis?**
1. Mengaktifkan **2GB Swap Memory** (Mencegah server EC2 Free Tier kehabisan RAM saat memproses build atau database).
2. Menginstal **Docker** & **Docker Compose**.
3. Menyusun file `.env` dengan konfigurasi database MySQL, migrasi otomatis, dan seeder data.
4. Menjalankan kontainer aplikasi Laravel (Apache + PHP 8.3) dan MySQL 8.0.
5. Menjalankan `php artisan migrate --force` dan `php artisan db:seed --force`.
6. Menampilkan alamat IP publik server Anda.

Buka browser dan akses:
```
http://<IP_PUBLIK_EC2>
```
Aplikasi Anda sudah live di AWS! 🎉

---

### Langkah 4: Deploy Manual (Alternatif)

Jika Anda ingin melakukan setiap langkah konfigurasi secara manual:

```bash
# 1. Update sistem dan install docker
sudo apt-get update && sudo apt-get install -y docker.io docker-compose-v2
sudo usermod -aG docker $USER
newgrp docker

# 2. Buat swap 2GB
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# 3. Kloning dan siapkan konfigurasi
git clone https://github.com/rebelleony/SIKESI-Tamalate.git
cd SIKESI-Tamalate
cp .env.aws.example .env

# 4. Ganti APP_URL dengan IP Publik EC2
PUBLIC_IP=$(curl -s http://checkip.amazonaws.com)
sed -i "s!APP_URL=http://localhost!APP_URL=http://${PUBLIC_IP}!g" .env

# 5. Jalankan Docker Compose
docker compose up -d --build

# 6. Cek status
docker compose ps
docker compose logs -f app
```

---

## 3. Opsi 2: Deploy di AWS App Runner (Serverless)

AWS App Runner sangat cocok jika Anda tidak ingin mengelola server Linux sendiri:

1. Buka konsol **AWS App Runner** > **Create service**.
2. **Source**:
   - Pilih **Source code repository** (hubungkan akun GitHub Anda, pilih repositori `SIKESI-Tamalate`, branch `main`).
3. **Build settings**:
   - Pilih **Use a configuration file** (akan otomatis mendeteksi file `apprunner.yaml` di root proyek) ATAU konfigurasikan manual:
     - Runtime: `Custom` (Docker)
     - Port: `80`
4. **Environment Variables**:
   Tambahkan environment variables berikut di dashboard App Runner:
   - `APP_NAME`: `SI Kepegawaian Tamalate`
   - `APP_ENV`: `production`
   - `APP_KEY`: `base64:4YAIZ+lxuXrTb7wjHeAA9WvlW2b5smipOWaqmwOL8VM=`
   - `APP_DEBUG`: `false`
   - `DB_CONNECTION`: `mysql` (sambungkan ke endpoint AWS RDS MySQL Anda)
   - `DB_HOST`: `<endpoint-rds-anda>.rds.amazonaws.com`
   - `DB_DATABASE`: `si_kepegawaian_tamalate`
   - `DB_USERNAME`: `admin`
   - `DB_PASSWORD`: `<password-rds>`
   - `RUN_MIGRATIONS`: `true`
   - `RUN_SEEDER`: `true`
5. Klik **Deploy**. App Runner akan otomatis membangun image Docker dan memberikan domain HTTPS resmi AWS seperti:
   `https://xyz123.ap-southeast-1.awsapprunner.com`.

---

## 4. Akun Login Bawaan (Hasil Seeder Demo)

Setelah server aktif, database otomatis terisi data awal dan akun demo untuk menguji 5 peran RBAC (*Role-Based Access Control*):

> **Password default untuk semua akun di bawah adalah:** `password`

| Username | Peran (*Role*) | Deskripsi Hak Akses |
| :--- | :--- | :--- |
| **admin** | Super Admin | Kelola user, data master unit kerja, audit log & distribusi pengajuan |
| **camat** | Camat / Lurah | Persetujuan akhir (*approval*) dan pengesahan tanda tangan elektronik |
| **seksam** | Camat / Lurah | Sekretaris Camat (persetujuan tingkat akhir kedua) |
| **kasi** | Kasi / Kasubag | Verifikasi awal dan persetujuan tingkat pertama |
| **verifikator** | Verifikator | Verifikasi berkas dokumen pengajuan & rekapitulasi presensi |
| **pegawai** | Pegawai / Staf | Melakukan pengajuan izin/cuti/surat dan presensi apel |

---

## 5. Cara Pointing Domain & Pasang SSL / HTTPS Gratis

Jika Anda memiliki domain (misalnya `kepegawaian.tamalate.id`):

### 1. Pointing DNS
Di penyedia DNS (Route 53, Cloudflare, Niagahoster, dll.), buat **A Record**:
- **Name / Host**: `@` atau subdomain (misal: `kepegawaian`)
- **Type**: `A`
- **Value / IPv4 Address**: `<IP_PUBLIK_EC2_ANDA>`
- **TTL**: Auto / 300

### 2. Pasang SSL Gratis (Let's Encrypt / Certbot)
Di dalam server EC2, jalankan:
```bash
# Install Certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Atau gunakan konfigurasi SSL yang sudah disediakan di folder nginx/
# Ganti nama domain di nginx/nginx.conf, lalu jalankan:
docker compose -f docker-compose.prod-ssl.yml up -d
```

Jika menggunakan **Cloudflare**, Anda cukup mengaktifkan fitur **Proxy (Orange Cloud)** dan set SSL ke **Flexible** atau **Full**, maka HTTPS aktif otomatis tanpa perlu konfigurasi certbot di server!

---

## 6. Perawatan, Pemeliharaan & Troubleshooting

### Perintah Berguna (Docker Compose):

- **Melihat status kontainer**:
  ```bash
  docker compose ps
  ```

- **Melihat live logs aplikasi**:
  ```bash
  docker compose logs -f app
  ```

- **Melihat logs database MySQL**:
  ```bash
  docker compose logs -f db
  ```

- **Restart seluruh layanan**:
  ```bash
  docker compose restart
  ```

- **Memperbarui aplikasi setelah push ke GitHub**:
  ```bash
  git pull origin main
  docker compose up -d --build
  ```

- **Masuk ke terminal di dalam kontainer Laravel**:
  ```bash
  docker compose exec app bash
  ```

- **Membersihkan cache Laravel secara manual**:
  ```bash
  docker compose exec app php artisan optimize:clear
  docker compose exec app php artisan optimize
  ```

- **Backup Database MySQL ke file SQL**:
  ```bash
  docker exec -t tamalate_db mysqldump -u root -proot_secret_2026 si_kepegawaian_tamalate > backup_$(date +%F).sql
  ```

- **Restore Database MySQL dari file SQL**:
  ```bash
  docker exec -i tamalate_db mysql -u root -proot_secret_2026 si_kepegawaian_tamalate < backup_file.sql
  ```

### Troubleshooting Umum di AWS:

1. **Aplikasi tidak bisa diakses dari browser (`Connection Timed Out`)**:
   - Periksa **Security Group** di AWS EC2 Console. Pastikan Inbound Rule memiliki Port **80 (HTTP)** dan Port **443 (HTTPS)** dengan Source `0.0.0.0/0`.
2. **Halaman CSS atau JS berantakan saat dibuka lewat HTTPS**:
   - Proyek ini sudah otomatis menyetel `$middleware->trustProxies(at: '*')` di `app-laravel/bootstrap/app.php`. Pastikan `APP_URL` di `.env` sudah menggunakan prefix `https://`.
3. **Database Error / Connection Refused**:
   - Jika menggunakan Docker Compose, pastikan `DB_HOST=db` (bukan `localhost` atau `127.0.0.1`), karena kontainer aplikasi berkomunikasi dengan kontainer database melalui jaringan internal docker.
4. **Kehabisan Memori (Out of Memory / OOM)**:
   - Pastikan swap memory aktif dengan memeriksa perintah `free -h`. Jika swap 0, jalankan kembali langkah swap memory dari `setup-ec2.sh`.

