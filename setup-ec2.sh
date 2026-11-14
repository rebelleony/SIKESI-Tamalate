#!/bin/bash
# ====================================================================
# SCRIPT SETUP OTOMATIS AWS EC2 UNTUK SI KEPEGAWAIAN TAMALATE
# Mendukung: Ubuntu 22.04/24.04 LTS & Amazon Linux 2023
# Cocok untuk EC2 t2.micro / t3.micro (AWS Free Tier)
# ====================================================================

set -e

echo "=== [1/6] Memulai Persiapan Server AWS EC2 ==="

# 1. Setup Swap Memory (Sangat penting untuk EC2 Free Tier RAM 1GB)
if [ ! -f /swapfile ]; then
    echo "Membuat Swap Memory 2GB agar server tidak kehabisan RAM..."
    fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    echo "Swap memory 2GB berhasil diaktifkan."
fi

# 2. Update sistem & Install Docker
echo "=== [2/6] Memperbarui Paket & Menginstal Docker ==="

if command -v apt-get >/dev/null 2>&1; then
    # Ubuntu / Debian
    apt-get update -y
    apt-get install -y ca-certificates curl gnupg lsb-release git

    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    # Tambahkan user ubuntu ke grup docker
    if id -u ubuntu >/dev/null 2>&1; then
        usermod -aG docker ubuntu
    fi

elif command -v dnf >/dev/null 2>&1; then
    # Amazon Linux 2023
    dnf update -y
    dnf install -y docker git
    systemctl enable --now docker
    
    # Install docker compose plugin
    mkdir -p /usr/local/lib/docker/cli-plugins
    curl -SL https://github.com/docker/compose/releases/latest/download/docker-compose-linux-$(uname -m) -o /usr/local/lib/docker/cli-plugins/docker-compose
    chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

    if id -u ec2-user >/dev/null 2>&1; then
        usermod -aG docker ec2-user
    fi
fi

systemctl enable docker
systemctl start docker

echo "=== [3/6] Menyiapkan Direktori Proyek ==="
TARGET_DIR="/home/ubuntu/SIKESI-Tamalate"
if [ ! -d "/home/ubuntu" ]; then
    TARGET_DIR="/home/ec2-user/SIKESI-Tamalate"
fi

if [ ! -d "$TARGET_DIR" ]; then
    echo "Mengkloning repository dari GitHub..."
    git clone https://github.com/rebelleony/SIKESI-Tamalate.git "$TARGET_DIR" || mkdir -p "$TARGET_DIR"
fi

cd "$TARGET_DIR"

echo "=== [4/6] Konfigurasi Lingkungan (.env) ==="
if [ ! -f .env ]; then
    if [ -f .env.aws.example ]; then
        cp .env.aws.example .env
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

# Dapatkan IP Publik EC2
PUBLIC_IP=$(curl -s http://checkip.amazonaws.com || curl -s https://api.ipify.org || echo "localhost")
sed -i "s!APP_URL=http://localhost!APP_URL=http://${PUBLIC_IP}!g" .env

echo "=== [5/6] Membangun dan Menjalankan Kontainer Docker ==="
docker compose up -d --build

echo "=== [6/6] Verifikasi Status Kontainer ==="
docker compose ps

echo "===================================================================="
echo "SELAMAT! Aplikasi SI Kepegawaian Tamalate Berhasil Di-deploy di AWS!"
echo "Akses aplikasi via browser di: http://${PUBLIC_IP}"
echo "===================================================================="

