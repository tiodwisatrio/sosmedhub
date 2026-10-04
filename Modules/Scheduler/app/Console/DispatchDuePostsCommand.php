<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;

class DispatchDuePostsCommand extends Command
{
    protected $signature = 'scheduler:dispatch-due';

    protected $description = 'Kirim postingan terjadwal yang sudah waktunya ke antrean publikasi';

    public function handle(): int
    {
        $ids = ScheduledPost::query()
            ->where('status', ScheduledPost::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->pluck('id');

        foreach ($ids as $id) {
            PublishScheduledPostJob::dispatch($id);
        }

        $this->info("{$ids->count()} postingan dikirim ke antrean.");

        return self::SUCCESS;
    }
}
