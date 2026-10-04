<?php

namespace Modules\SocialAccount\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\SocialAccount\Console\RefreshTokensCommand;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SocialAccountServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'SocialAccount';

    protected string $nameLower = 'social-account';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    protected array $commands = [
        RefreshTokensCommand::class,
    ];

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('social-accounts:refresh-tokens')->daily();
    }
}
