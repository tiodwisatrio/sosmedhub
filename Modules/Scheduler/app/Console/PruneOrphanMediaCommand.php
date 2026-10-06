<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPostMedia;

class PruneOrphanMediaCommand extends Command
{
    protected $signature = 'scheduler:prune-orphans
        {--hours=24 : Hanya hapus file yang lebih tua dari sekian jam}
        {--dry-run : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus file di scheduled-posts yang tidak dirujuk baris media mana pun (sisa unggahan yang gagal)';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $cutoff = now()->subHours(max(1, (int) $this->option('hours')))->getTimestamp();

        $referenced = ScheduledPostMedia::query()
            ->get(['media_path', 'thumbnail_path'])
            ->flatMap(fn ($media) => [$media->media_path, $media->thumbnail_path])
            ->filter()
            ->flip();

        $removed = 0;
        $bytes = 0;

        foreach ($disk->allFiles('scheduled-posts') as $file) {
            // Dirujuk, atau masih baru (bisa jadi unggahan yang sedang berjalan): jangan disentuh.
            if (isset($referenced[$file]) || $disk->lastModified($file) > $cutoff) {
                continue;
            }

            $bytes += $disk->size($file);
            $removed++;

            if ($this->option('dry-run')) {
                $this->line("  akan dihapus: {$file}");
            } else {
                $disk->delete($file);
            }
        }

        $size = number_format($bytes / 1048576, 2, ',', '.');
        $this->info(($this->option('dry-run') ? 'Akan menghapus ' : 'Menghapus ')."{$removed} file yatim ({$size} MB).");

        return self::SUCCESS;
    }
}
