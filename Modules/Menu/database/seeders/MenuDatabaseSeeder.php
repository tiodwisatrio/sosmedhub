<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Menu\Models\Menu;

class MenuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $icons = \Modules\Menu\Http\Controllers\Admin\MenuController::$icons;

        Menu::create([
            'label'          => 'Menu',
            'route_name'     => 'admin.menus.index',
            'active_pattern' => 'admin.menus.*',
            'permission'     => 'menu.view',
            'icon'           => $icons['menu']['path'],
            'urutan'         => 0,
            'is_active'      => 1,
        ]);

        Menu::create([
            'label'          => 'Dashboard',
            'route_name'     => 'admin.dashboard',
            'active_pattern' => 'admin.dashboard',
            'permission'     => null,
            'icon'           => $icons['home']['path'],
            'urutan'         => 1,
            'is_active'      => 1,
        ]);

        Menu::create([
            'label'          => 'Layanan',
            'route_name'     => 'admin.layanans.index',
            'active_pattern' => 'admin.layanans.*',
            'permission'     => 'layanan.view',
            'icon'           => $icons['wrench']['path'],
            'urutan'         => 2,
            'is_active'      => 1,
        ]);

        $tim = Menu::create([
            'label'      => 'Tim',
            'permission' => 'team.view',
            'icon'       => $icons['team']['path'],
            'urutan'     => 3,
            'is_active'  => 1,
        ]);

        Menu::create([
            'parent_id'      => $tim->id,
            'label'          => 'Data Tim',
            'route_name'     => 'admin.teams.index',
            'active_pattern' => 'admin.teams.*',
            'permission'     => 'team.view',
            'urutan'         => 0,
            'is_active'      => 1,
        ]);

        Menu::create([
            'parent_id'      => $tim->id,
            'label'          => 'Kategori Tim',
            'route_name'     => 'admin.categories.index',
            'route_params'   => ['type' => 'team'],
            'active_pattern' => 'admin.categories.*',
            'permission'     => 'category.view',
            'urutan'         => 1,
            'is_active'      => 1,
        ]);

        Menu::create([
            'label'          => 'Pengguna',
            'route_name'     => 'admin.users.index',
            'active_pattern' => 'admin.users.*',
            'permission'     => 'user.view',
            'icon'           => $icons['person']['path'],
            'urutan'         => 4,
            'is_active'      => 1,
        ]);

        Menu::create([
            'label'          => 'Role & Akses',
            'route_name'     => 'admin.roles.index',
            'active_pattern' => 'admin.roles.*',
            'permission'     => 'role.view',
            'icon'           => $icons['shield']['path'],
            'urutan'         => 5,
            'is_active'      => 1,
        ]);
    }
}
