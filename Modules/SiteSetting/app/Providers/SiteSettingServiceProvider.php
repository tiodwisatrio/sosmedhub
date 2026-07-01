<?php

namespace Modules\SiteSetting\Providers;

use Illuminate\Support\Facades\View;
use Modules\SiteSetting\Models\SiteSetting;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SiteSettingServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'SiteSetting';

    protected string $nameLower = 'sitesetting';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        if (! app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                View::share('siteSetting', SiteSetting::current());
            } catch (\Exception) {
                View::share('siteSetting', new SiteSetting(['app_name' => config('app.name')]));
            }
        }
    }
}
