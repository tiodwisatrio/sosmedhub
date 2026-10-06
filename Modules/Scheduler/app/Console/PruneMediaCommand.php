<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Models\ScheduledPostPublication;

class PruneMediaCommand extends Command
{
    protected $signature = 'scheduler:prune-media';

    protected $description = 'Hapus versi terbit foto dari postingan yang sudah terbit lewat masa simpan; thumbnail tetap disimpan';

    public function handle(): int
    {
        $days = (int) config('scheduler.media.publish_retention_days', 30);
        $pruned = 0;

        $cutoff = now()->subDays($days);

        ScheduledPostMedia::query()
            ->whereNotNull('media_path')
            // Foto tanpa thumbnail (unggahan lama) tidak dihapus agar tetap ada gambar yang tersisa;
            // video memang tidak punya thumbnail dan boleh dihapus.
            ->where(fn ($query) => $query->whereNotNull('thumbnail_path')->orWhere('type', 'video'))
            ->where(function ($query) use ($cutoff) {
                // Format ini sudah terbit melewati masa simpan...
                $query->whereExists(fn ($sub) => $sub->selectRaw('1')
                    ->from('scheduled_post_publications')
                    ->whereColumn('scheduled_post_publications.scheduled_post_id', 'scheduled_post_media.scheduled_post_id')
                    ->whereColumn('scheduled_post_publications.format', 'scheduled_post_media.format')
                    ->where('scheduled_post_publications.status', ScheduledPostPublication::STATUS_PUBLISHED)
                    ->where('scheduled_post_publications.published_at', '<=', $cutoff))
                    // ...atau jadwal lama tanpa baris publikasi yang berstatus terbit.
                    ->orWhereHas('scheduledPost', fn ($post) => $post
                        ->where('status', ScheduledPost::STATUS_PUBLISHED)
                        ->where('published_at', '<=', $cutoff)
                        ->whereDoesntHave('publications'));
            })
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
