#!/bin/bash
set -e
cd /var/www/html

# Pastikan folder penyimpanan ada dan bisa ditulis
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
         storage/app/public/gallery storage/app/public/qris storage/app/public/order-photos
chown -R www-data:www-data storage bootstrap/cache

art() { runuser -u www-data -- php artisan "$@"; }

art config:cache
art route:cache
art view:cache

# Migrasi otomatis setiap start (aman, hanya menjalankan yang belum)
art migrate --force

# Isi data awal HANYA sekali: set RUN_SEED=true di deploy pertama, lalu hapus
if [ "$RUN_SEED" = "true" ]; then
  art db:seed --force
fi

# Penjadwal (hapus foto > 1 bulan), jalan tiap menit selama container hidup
( while true; do runuser -u www-data -- php artisan schedule:run >> /dev/null 2>&1; sleep 60; done ) &

exec apache2-foreground