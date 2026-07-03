<?php

use App\Models\User;
use Modules\Menu\Models\Menu;
use Spatie\Permission\Models\Permission;

function categoryViewer(): User
{
    $permission = Permission::firstOrCreate(['name' => 'category.view', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->givePermissionTo($permission);

    return $user;
}

test('menu kategori post aktif hanya saat query type cocok', function () {
    $this->actingAs(categoryViewer())->get('/admin/categories?type=post')->assertOk();

    $kategoriPost = new Menu([
        'route_name' => 'admin.categories.index',
        'active_pattern' => 'admin.categories.*',
        'route_params' => ['type' => 'post'],
    ]);
    $kategoriTim = new Menu([
        'route_name' => 'admin.categories.index',
        'active_pattern' => 'admin.categories.*',
        'route_params' => ['type' => 'team'],
    ]);

    expect($kategoriPost->isActive())->toBeTrue();
    expect($kategoriTim->isActive())->toBeFalse();
});

test('menu tanpa route_params tetap aktif hanya dari active_pattern', function () {
    $this->actingAs(categoryViewer())->get('/admin/categories?type=post')->assertOk();

    $menu = new Menu([
        'route_name' => 'admin.categories.index',
        'active_pattern' => 'admin.categories.*',
        'route_params' => null,
    ]);

    expect($menu->isActive())->toBeTrue();
});
