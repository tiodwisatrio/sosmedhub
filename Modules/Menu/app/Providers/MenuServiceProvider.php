<?php

namespace Modules\Menu\Providers;

use Illuminate\Support\Facades\View;
use Modules\Menu\Models\Menu;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MenuServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Menu';

    protected string $nameLower = 'menu';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();

            $sidebarMenus = Menu::where('is_active', 1)
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->where('is_active', 1)->orderBy('urutan')])
                ->orderBy('urutan')
                ->get()
                ->filter(fn ($menu) => $menu->canSee())
                ->map(function ($menu) use ($user) {
                    $menu->setRelation(
                        'children',
                        $menu->children->filter(fn ($child) => $child->canSee())->values()
                    );

                    return $menu;
                })
                ->filter(fn ($menu) => $menu->route_name || $menu->children->isNotEmpty())
                ->values();

            $view->with('sidebarMenus', $sidebarMenus);
        });
    }
}
