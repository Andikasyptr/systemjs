#!/bin/bash
set -e
cd /var/www/html

# Pastikan folder penyimpanan ada dan bisa ditulis
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
         storage/app/public/gallery storage/app/public/qris storage/app/public/order-photos
chown -R www-data:www-data storage bootstrap/cache

# Cek apakah DB_HOST terbaca oleh container
echo "Mencoba menyambungkan ke Database Host: $DB_HOST"

art() { runuser -u www-data -- php artisan "$@"; }

art config:clear
art route:clear
art view:clear

# Migrasi otomatis setiap start
art migrate --force

# ... (lanjutan script berikutnya)