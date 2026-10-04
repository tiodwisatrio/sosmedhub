<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Scheduler\Models\ScheduledPost;

/**
 * Mencatat kapan scheduler terakhir berjalan, supaya cron yang mati diam-diam ketahuan.
 */
class SchedulerHeartbeat
{
    private const KEY = 'scheduler:last_run_at';

    public function beat(): void
    {
        Cache::forever(self::KEY, now()->getTimestamp());
    }

    public function lastRunAt(): ?Carbon
    {
        $timestamp = Cache::get(self::KEY);

        return $timestamp ? Carbon::createFromTimestamp((int) $timestamp) : null;
    }

    /**
     * Dianggap macet bila lebih dari dua slot terlewat tanpa satu putaran pun.
     */
    public function isStale(): bool
    {
        $last = $this->lastRunAt();

        return ! $last || $last->lt(now()->subMinutes(ScheduledPost::slotMinutes() * 2 + 1));
    }
}
