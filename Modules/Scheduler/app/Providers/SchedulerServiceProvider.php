<?php

namespace Modules\Scheduler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Scheduler\Console\DispatchDuePostsCommand;
use Modules\Scheduler\Console\ProcessExistingMediaCommand;
use Modules\Scheduler\Console\PruneMediaCommand;
use Modules\Scheduler\Console\PruneOrphanMediaCommand;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SchedulerServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Scheduler';

    protected string $nameLower = 'scheduler';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    protected array $commands = [
        DispatchDuePostsCommand::class,
        PruneMediaCommand::class,
        PruneOrphanMediaCommand::class,
        ProcessExistingMediaCommand::class,
    ];

    /**
     * Urutan penting: post jatuh tempo dikirim ke antrean dulu, lalu antrean diproses
     * di putaran yang sama. Dengan cron tiap 15 menit, urutan terbalik berarti telat satu putaran.
     *
     * Kunci withoutOverlapping diberi masa kedaluwarsa pendek. Bawaannya 24 jam, sehingga
     * proses yang dibunuh host akan menahan scheduler seharian.
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('scheduler:dispatch-due')
            ->everyMinute()
            ->withoutOverlapping(10);

        if (config('scheduler.run_worker', true)) {
            $maxSeconds = (int) config('scheduler.worker_max_seconds', 240);

            $schedule->command('queue:work', [
                '--stop-when-empty',
                '--tries' => 1,
                '--max-time' => $maxSeconds,
            ])
                ->everyMinute()
                ->withoutOverlapping((int) ceil($maxSeconds / 60) + 5);
        }

        // File tanpa baris media (unggahan yang gagal di tengah jalan); hanya yang berumur lebih dari 24 jam.
        $schedule->command('scheduler:prune-orphans')
            ->dailyAt('02:00')
            ->withoutOverlapping(60);

        // 01.00 tetap terkena cron tiap 15 menit.
        $schedule->command('scheduler:prune-media')
            ->dailyAt('01:00')
            ->withoutOverlapping(60);
    }
}
