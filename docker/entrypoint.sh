#!/bin/bash
set -e
cd /var/www/html

# Pastikan folder penyimpanan ada dan bisa ditulis
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
         storage/app/public/gallery storage/app/public/qris storage/app/public/order-photos
chown -R www-data:www-data storage bootstrap/cache

echo "Mencoba menyambungkan ke Database Host: $DB_HOST"

art() { runuser -u www-data -- php artisan "$@"; }

art config:clear
art route:clear
art view:clear

# Migrasi otomatis setiap start
art migrate --force

# Isi data awal HANYA sekali jika RUN_SEED=true
if [ "$RUN_SEED" = "true" ]; then
  art db:seed --force
fi

# Penjadwal di background
( while true; do runuser -u www-data -- php artisan schedule:run >> /dev/null 2>&1; sleep 60; done ) &

# INI BARIS PALING KRUSIAL AGAR RENDER TIDAK "EXITED EARLY"
exec apache2-foreground