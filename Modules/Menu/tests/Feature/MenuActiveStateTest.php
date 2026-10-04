<?php

use App\Models\User;
use Modules\Menu\Models\Menu;
use Spatie\Permission\Models\Permission;

function userViewer(): User
{
    $permission = Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->givePermissionTo($permission);

    return $user;
}

test('menu dengan route params aktif hanya saat query cocok', function () {
    $this->actingAs(userViewer())->get('/admin/users?status=active')->assertOk();

    $activeUsers = new Menu([
        'route_name' => 'admin.users.index',
        'active_pattern' => 'admin.users.*',
        'route_params' => ['status' => 'active'],
    ]);
    $inactiveUsers = new Menu([
        'route_name' => 'admin.users.index',
        'active_pattern' => 'admin.users.*',
        'route_params' => ['status' => 'inactive'],
    ]);

    expect($activeUsers->isActive())->toBeTrue();
    expect($inactiveUsers->isActive())->toBeFalse();
});

test('menu tanpa route_params tetap aktif hanya dari active_pattern', function () {
    $this->actingAs(userViewer())->get('/admin/users?status=active')->assertOk();

    $menu = new Menu([
        'route_name' => 'admin.users.index',
        'active_pattern' => 'admin.users.*',
        'route_params' => null,
    ]);

    expect($menu->isActive())->toBeTrue();
});
