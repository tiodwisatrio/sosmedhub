<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Menu\Http\Controllers\Admin\MenuController;
use Modules\Menu\Models\Menu;

class MenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Menu::truncate();
        Schema::enableForeignKeyConstraints();

        $icons = MenuController::$icons;

        Menu::create([
            'label' => 'Menu',
            'route_name' => 'admin.menus.index',
            'active_pattern' => 'admin.menus.*',
            'permission' => 'menu.view',
            'icon' => $icons['menu']['path'],
            'urutan' => 0,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Dashboard',
            'route_name' => 'admin.dashboard',
            'active_pattern' => 'admin.dashboard',
            'permission' => null,
            'icon' => $icons['home']['path'],
            'urutan' => 1,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Pengguna',
            'route_name' => 'admin.users.index',
            'active_pattern' => 'admin.users.*',
            'permission' => 'user.view',
            'icon' => $icons['person']['path'],
            'urutan' => 2,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Role & Akses',
            'route_name' => 'admin.roles.index',
            'active_pattern' => 'admin.roles.*',
            'permission' => 'role.view',
            'icon' => $icons['shield']['path'],
            'urutan' => 3,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Penjadwalan',
            'route_name' => 'admin.scheduled-posts.index',
            // Manajemen Post punya menu sendiri, jadi tidak ikut menyalakan menu ini.
            'active_pattern' => 'admin.scheduled-posts.*,!admin.scheduled-posts.create',
            'permission' => 'scheduler.view',
            'icon' => $icons['calendar']['path'],
            'urutan' => 5,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Manajemen Post',
            'route_name' => 'admin.scheduled-posts.create',
            'active_pattern' => 'admin.scheduled-posts.create',
            'permission' => 'scheduler.create',
            'icon' => $icons['plus']['path'] ?? $icons['calendar']['path'],
            'urutan' => 6,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Riwayat',
            'route_name' => 'admin.post-history.index',
            'active_pattern' => 'admin.post-history.*',
            'permission' => 'scheduler.view',
            'icon' => $icons['list']['path'],
            'urutan' => 7,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Akun Sosial',
            'route_name' => 'admin.social-accounts.index',
            'active_pattern' => 'admin.social-accounts.*',
            'permission' => 'social-account.view',
            'icon' => $icons['link']['path'] ?? $icons['cog']['path'],
            'urutan' => 8,
            'is_active' => 1,
        ]);

        Menu::create([
            'label' => 'Pengaturan Situs',
            'route_name' => 'admin.site-settings.index',
            'active_pattern' => 'admin.site-settings.*',
            'permission' => 'site-setting.view',
            'icon' => $icons['cog']['path'],
            'urutan' => 4,
            'is_active' => 1,
        ]);
    }
}
