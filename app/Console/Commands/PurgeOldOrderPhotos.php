<?php

namespace App\Console\Commands;

use App\Models\OrderPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOldOrderPhotos extends Command
{
    protected $signature = 'photos:purge {--months=1 : Umur foto (bulan) sebelum dihapus} {--dry-run : Hanya hitung, tidak menghapus}';

    protected $description = 'Hapus foto barang pesanan yang sudah lebih dari 1 bulan sejak diunggah';

    public function handle(): int
    {
        $cutoff = now()->subMonths((int) $this->option('months'));
        $dry    = (bool) $this->option('dry-run');

        $query = OrderPhoto::where('created_at', '<', $cutoff);
        $total = $query->count();

        if ($total === 0) {
            $this->info('Tidak ada foto yang perlu dihapus.');
            return self::SUCCESS;
        }

        if ($dry) {
            $this->info("[Dry run] {$total} foto lebih lama dari {$cutoff->toDateTimeString()} akan dihapus.");
            return self::SUCCESS;
        }

        $deleted = 0;

        // chunkById aman untuk data banyak dan tetap benar saat baris dihapus
        $query->chunkById(100, function ($photos) use (&$deleted) {
            foreach ($photos as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
                $deleted++;
            }
        });

        $this->info("{$deleted} foto dihapus.");

        return self::SUCCESS;
    }
}