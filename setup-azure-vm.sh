#!/bin/bash
# ====================================================================
# SCRIPT SETUP OTOMATIS AZURE VIRTUAL MACHINE (VM)
# SI KEPEGAWAIAN TAMALATE
# Mendukung: Ubuntu 22.04 / 24.04 LTS di Microsoft Azure
# Cocok untuk Azure VM Standard_B1s (Azure Free Tier) / Standard_B2s
# ====================================================================

set -e

echo "=== [1/6] Memulai Persiapan Azure Virtual Machine ==="

# 1. Setup Swap Memory (2GB) agar server VM tidak kehabisan RAM saat proses build/database
if [ ! -f /swapfile ]; then
    echo "Membuat Swap Memory 2GB..."
    fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    echo "Swap memory 2GB berhasil diaktifkan."
fi

# 2. Update sistem & Install Docker & Docker Compose
echo "=== [2/6] Memperbarui Paket & Menginstal Docker Engine ==="

apt-get update -y
apt-get install -y ca-certificates curl gnupg lsb-release git

install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor --yes -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

apt-get update -y
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Tambahkan user default azure (azureuser / ubuntu) ke grup docker
if id -u azureuser >/dev/null 2>&1; then
    usermod -aG docker azureuser
fi
if id -u ubuntu >/dev/null 2>&1; then
    usermod -aG docker ubuntu
fi

systemctl enable docker
systemctl start docker

echo "=== [3/6] Menyiapkan Direktori Proyek ==="
TARGET_DIR="/home/azureuser/SIKESI-Tamalate"
if [ ! -d "/home/azureuser" ]; then
    if [ -d "/home/ubuntu" ]; then
        TARGET_DIR="/home/ubuntu/SIKESI-Tamalate"
    else
        TARGET_DIR="$HOME/SIKESI-Tamalate"
    fi
fi

if [ ! -d "$TARGET_DIR" ]; then
    echo "Mengkloning repository dari GitHub..."
    git clone https://github.com/rebelleony/SIKESI-Tamalate.git "$TARGET_DIR" || mkdir -p "$TARGET_DIR"
fi

cd "$TARGET_DIR"

echo "=== [4/6] Konfigurasi Lingkungan (.env) ==="
if [ ! -f .env ]; then
    if [ -f .env.azure.example ]; then
        cp .env.azure.example .env
    else
        echo "Menyiapkan file .env dasar..."
        cat << 'EOF' > .env
APP_NAME="SI Kepegawaian Tamalate"
APP_ENV=production
APP_KEY=base64:4YAIZ+lxuXrTb7wjHeAA9WvlW2b5smipOWaqmwOL8VM=
APP_DEBUG=false
APP_URL=http://localhost
APP_TIMEZONE=Asia/Makassar
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=si_kepegawaian_tamalate
DB_USERNAME=tamalate_user
DB_PASSWORD=tamalate_secret_2026
DB_ROOT_PASSWORD=root_secret_2026
RUN_MIGRATIONS=true
RUN_SEEDER=true
EOF
    fi
fi

# Dapatkan IP Publik Azure VM
PUBLIC_IP=$(curl -s https://api.ipify.org || curl -s http://checkip.amazonaws.com || echo "localhost")
sed -i "s!APP_URL=http://localhost!APP_URL=http://${PUBLIC_IP}!g" .env
sed -i "s!APP_URL=https://tamalate-app.azurewebsites.net!APP_URL=http://${PUBLIC_IP}!g" .env

echo "=== [5/6] Membangun dan Menjalankan Kontainer Docker ==="
docker compose up -d --build

echo "=== [6/6] Verifikasi Status Kontainer ==="
docker compose ps

echo "===================================================================="
echo "SELAMAT! Aplikasi SI Kepegawaian Tamalate Berhasil Di-deploy di Azure VM!"
echo "Akses aplikasi via browser di: http://${PUBLIC_IP}"
echo "===================================================================="

