#!/bin/sh
set -e

echo "=== Memulai Inisialisasi SI Kepegawaian Tamalate ==="

# 1. Pastikan direktori storage & bootstrap/cache ada
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    storage/app/surat_masuk \
    bootstrap/cache \
    database

# 2. Tangani koneksi database
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ ! -f "database/database.sqlite" ]; then
        echo "Membuat database SQLite..."
        touch database/database.sqlite
    fi
    chown -R www-data:www-data database
elif [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    echo "Menunggu koneksi database MySQL di ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}..."
    MAX_TRIES=30
    COUNT=0
    until php -r "
        \$host = getenv('DB_HOST') ?: '127.0.0.1';
        \$port = getenv('DB_PORT') ?: 3306;
        \$fp = @fsockopen(\$host, (int)\$port, \$errno, \$errstr, 2);
        if (\$fp) { fclose(\$fp); exit(0); }
        exit(1);
    " 2>/dev/null || [ $COUNT -eq $MAX_TRIES ]; do
        COUNT=$((COUNT + 1))
        echo "Menunggu MySQL siap ($COUNT/$MAX_TRIES)..."
        sleep 2
    done

    if [ $COUNT -eq $MAX_TRIES ]; then
        echo "Peringatan: Tidak dapat terhubung ke MySQL dalam waktu yang ditentukan. Melanjutkan proses..."
    else
        echo "Berhasil terhubung ke database MySQL!"
    fi
fi

# 3. Pastikan hak akses direktori storage dan cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 4. Storage Link (symlink public/storage -> storage/app/public)
if [ ! -L "public/storage" ]; then
    echo "Membuat symbolic link public/storage..."
    php artisan storage:link --force || true
fi

# 5. Generate APP_KEY jika belum terisi
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY belum disetel. Menghasilkan APP_KEY baru..."
    php artisan key:generate --force || true
fi

# 6. Database Migrations & Seeding
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Menjalankan migrasi database..."
    php artisan migrate --force || echo "Peringatan: Migrasi database gagal."
fi

if [ "${RUN_SEEDER:-false}" = "true" ]; then
    echo "Menjalankan seeder database..."
    php artisan db:seed --force || echo "Peringatan: Seeder database gagal."
fi

# 7. Optimasi Caching untuk Production
if [ "${APP_ENV}" = "production" ]; then
    echo "Mengoptimalkan cache konfigurasi, route, dan view untuk production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "=== Inisialisasi selesai. Menjalankan web server Apache ==="

exec "$@"
