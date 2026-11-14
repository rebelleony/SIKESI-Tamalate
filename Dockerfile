FROM php:8.3-apache

# Install dependencies sistem & ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
       pdo \
       pdo_mysql \
       pdo_sqlite \
       mbstring \
       bcmath \
       exif \
       pcntl \
       intl \
       zip \
       gd \
       opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Konfigurasi Apache Document Root ke /public dan izinkan .htaccess (mod_rewrite)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Konfigurasi PHP optimal untuk production di AWS
RUN echo "upload_max_filesize = 64M\n\
post_max_size = 64M\n\
memory_limit = 256M\n\
max_execution_time = 120\n\
date.timezone = Asia/Makassar\n\
opcache.enable = 1\n\
opcache.enable_cli = 0\n\
opcache.memory_consumption = 128\n\
opcache.interned_strings_buffer = 8\n\
opcache.max_accelerated_files = 10000\n" > /usr/local/etc/php/conf.d/laravel.ini

# Pasang Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin source code aplikasi
COPY app-laravel/ .

# Install dependencies Composer
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Siapkan direktori storage & database awal
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    storage/app/surat_masuk \
    bootstrap/cache \
    database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

# Salin script entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
