<?php

use App\Models\User;
use Modules\Menu\Models\Menu;
use Spatie\Permission\Models\Permission;

function menuManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'menu.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'menu.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'menu.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'menu.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman menu', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.menus.index'))
        ->assertForbidden();
});

test('menu baru bisa dibuat', function () {
    $this->actingAs(menuManager())
        ->post(route('admin.menus.store'), [
            'label' => 'Produk',
            'route_name' => 'admin.products.index',
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.menus.index'));

    expect(Menu::where('label', 'Produk')->exists())->toBeTrue();
});

test('menu gagal dibuat tanpa label', function () {
    $this->actingAs(menuManager())
        ->post(route('admin.menus.store'), ['is_active' => 1])
        ->assertSessionHasErrors('label');
});

test('menu bisa dijadikan child dari parent lain', function () {
    $parent = Menu::create(['label' => 'Master', 'is_active' => 1]);

    $this->actingAs(menuManager())
        ->post(route('admin.menus.store'), [
            'label' => 'Anak Menu',
            'parent_id' => $parent->id,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.menus.index'));

    $child = Menu::firstWhere('label', 'Anak Menu');

    expect($child->parent_id)->toBe($parent->id);
});

test('menu dan anaknya ikut terhapus saat parent dihapus', function () {
    $parent = Menu::create(['label' => 'Master', 'is_active' => 1]);
    $child = Menu::create(['label' => 'Anak', 'parent_id' => $parent->id, 'is_active' => 1]);

    $this->actingAs(menuManager())
        ->delete(route('admin.menus.destroy', $parent))
        ->assertRedirect(route('admin.menus.index'));

    expect(Menu::where('id', $parent->id)->exists())->toBeFalse();
    expect(Menu::where('id', $child->id)->exists())->toBeFalse();
});

test('reorder menu memperbarui urutan dan parent', function () {
    $menuA = Menu::create(['label' => 'A', 'urutan' => 0, 'is_active' => 1]);
    $menuB = Menu::create(['label' => 'B', 'urutan' => 1, 'is_active' => 1]);

    $this->actingAs(menuManager())
        ->post(route('admin.menus.reorder'), [
            'items' => [
                ['id' => $menuA->id, 'urutan' => 1],
                ['id' => $menuB->id, 'urutan' => 0],
            ],
        ])
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect($menuA->fresh()->urutan)->toBe(1);
    expect($menuB->fresh()->urutan)->toBe(0);
});

test('menu tanpa permission selalu terlihat, menu dengan permission mengikuti akses user', function () {
    $menuBebas = Menu::create(['label' => 'Bebas', 'is_active' => 1]);
    $menuDibatasi = Menu::create(['label' => 'Dibatasi', 'permission' => 'post.view', 'is_active' => 1]);

    $userTanpaAkses = User::factory()->create();
    $this->actingAs($userTanpaAkses);

    expect($menuBebas->canSee())->toBeTrue();
    expect($menuDibatasi->canSee())->toBeFalse();

    $userTanpaAkses->givePermissionTo(Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']));

    expect($menuDibatasi->canSee())->toBeTrue();
});
