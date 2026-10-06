<?php

use App\Models\User;
use Modules\Menu\Models\Menu;
use Modules\Scheduler\Models\ScheduledPost;
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

function schedulerMenus(): array
{
    return [
        'jadwal' => new Menu([
            'route_name' => 'admin.scheduled-posts.index',
            'active_pattern' => 'admin.scheduled-posts.*,!admin.scheduled-posts.create',
        ]),
        'buat' => new Menu([
            'route_name' => 'admin.scheduled-posts.create',
            'active_pattern' => 'admin.scheduled-posts.create',
        ]),
        'riwayat' => new Menu([
            'route_name' => 'admin.post-history.index',
            'active_pattern' => 'admin.post-history.*',
        ]),
    ];
}

function schedulerUserWith(array $names): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        $names
    ));

    return $user;
}

test('pola dengan pengecualian membuat hanya satu menu penjadwalan yang menyala di halaman buat', function () {
    $user = schedulerUserWith(['scheduler.view', 'scheduler.create']);
    $menus = schedulerMenus();

    $this->actingAs($user)->get(route('admin.scheduled-posts.create'))->assertOk();
    expect($menus['buat']->isActive())->toBeTrue()
        ->and($menus['jadwal']->isActive())->toBeFalse()
        ->and($menus['riwayat']->isActive())->toBeFalse();

    $this->actingAs($user)->get(route('admin.scheduled-posts.index'))->assertOk();
    expect($menus['jadwal']->isActive())->toBeTrue()
        ->and($menus['buat']->isActive())->toBeFalse();

    $this->actingAs($user)->get(route('admin.post-history.index'))->assertOk();
    expect($menus['riwayat']->isActive())->toBeTrue()
        ->and($menus['jadwal']->isActive())->toBeFalse();
});

test('menu penjadwalan tetap menyala di halaman ubah', function () {
    $user = schedulerUserWith(['scheduler.view', 'scheduler.edit']);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'scheduled_at' => now()->addDay(),
    ]);
    $menus = schedulerMenus();

    $this->actingAs($user)->get(route('admin.scheduled-posts.edit', $post))->assertOk();

    expect($menus['jadwal']->isActive())->toBeTrue()
        ->and($menus['buat']->isActive())->toBeFalse();
});

test('active pattern mendukung beberapa pola dipisah koma dan spasi diabaikan', function () {
    $this->actingAs(userViewer())->get('/admin/users')->assertOk();

    expect((new Menu(['active_pattern' => 'admin.roles.* , admin.users.*']))->isActive())->toBeTrue()
        ->and((new Menu(['active_pattern' => 'admin.roles.*,admin.menus.*']))->isActive())->toBeFalse();
});

test('pola yang hanya berisi pengecualian tidak pernah aktif', function () {
    $this->actingAs(userViewer())->get('/admin/users')->assertOk();

    expect((new Menu(['active_pattern' => '!admin.roles.*']))->isActive())->toBeFalse();
});

test('sidebar menampilkan menu Manajemen Post hanya untuk user dengan izin membuat', function () {
    $menu = Menu::create([
        'label' => 'Manajemen Post', 'route_name' => 'admin.scheduled-posts.create',
        'active_pattern' => 'admin.scheduled-posts.create', 'permission' => 'scheduler.create',
        'urutan' => 6, 'is_active' => 1,
    ]);

    $viewer = schedulerUserWith(['scheduler.view']);
    $creator = schedulerUserWith(['scheduler.view', 'scheduler.create']);

    expect($menu->fresh()->canSee())->toBeFalse();

    $this->actingAs($viewer)->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->assertDontSee('Manajemen Post');

    $this->actingAs($creator)->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->assertSee('Manajemen Post')
        ->assertSee(route('admin.scheduled-posts.create'), false);
});
