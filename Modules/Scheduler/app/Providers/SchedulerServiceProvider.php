<?php

namespace Modules\Scheduler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Scheduler\Console\DispatchDuePostsCommand;
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
    ];

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('scheduler:dispatch-due')->everyMinute()->withoutOverlapping();
    }
}
