<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;

class PruneMediaCommand extends Command
{
    protected $signature = 'scheduler:prune-media';

    protected $description = 'Hapus versi terbit foto dari postingan yang sudah terbit lewat masa simpan; thumbnail tetap disimpan';

    public function handle(): int
    {
        $days = (int) config('scheduler.media.publish_retention_days', 30);
        $pruned = 0;

        ScheduledPostMedia::query()
            ->whereNotNull('media_path')
            // Foto tanpa thumbnail (unggahan lama) tidak dihapus agar tetap ada gambar yang tersisa.
            ->whereNotNull('thumbnail_path')
            ->whereHas('scheduledPost', fn ($query) => $query
                ->where('status', ScheduledPost::STATUS_PUBLISHED)
                ->where('published_at', '<=', now()->subDays($days)))
            ->chunkById(200, function ($items) use (&$pruned) {
                foreach ($items as $item) {
                    Storage::disk('public')->delete($item->media_path);
                    $item->update(['media_path' => null, 'media_pruned_at' => now()]);
                    $pruned++;
                }
            });

        $this->info("{$pruned} foto versi terbit dihapus.");

        return self::SUCCESS;
    }
}
