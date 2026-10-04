<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Services\MediaProcessor;
use Throwable;

/**
 * Sekali jalan setelah deploy: mengolah foto yang diunggah sebelum ada thumbnail.
 */
class ProcessExistingMediaCommand extends Command
{
    protected $signature = 'scheduler:process-existing-media';

    protected $description = 'Buat versi terbit dan thumbnail untuk foto lama yang belum diolah';

    public function handle(MediaProcessor $processor): int
    {
        $disk = Storage::disk('public');
        $done = 0;
        $failed = 0;

        ScheduledPostMedia::query()
            ->whereNull('thumbnail_path')
            ->whereNotNull('media_path')
            ->chunkById(50, function ($items) use ($disk, $processor, &$done, &$failed) {
                foreach ($items as $item) {
                    if (! $disk->exists($item->media_path)) {
                        continue;
                    }

                    try {
                        $result = $processor->process($disk->path($item->media_path));
                        $oldPath = $item->media_path;

                        $item->update($result);
                        $disk->delete($oldPath);
                        $done++;
                    } catch (Throwable $e) {
                        report($e);
                        $this->warn("Gagal mengolah media #{$item->id}: {$e->getMessage()}");
                        $failed++;
                    }
                }
            });

        $this->info("{$done} foto diolah, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
