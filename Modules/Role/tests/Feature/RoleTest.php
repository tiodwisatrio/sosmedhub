<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function roleManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'role.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'role.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'role.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'role.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('developer role is hidden from the role list', function () {
    Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('editor')
        ->assertDontSee('developer');
});

test('user tanpa permission tidak bisa akses halaman role', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

test('role baru bisa dibuat beserta permission-nya', function () {
    Permission::firstOrCreate(['name' => 'post.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->post(route('admin.roles.store'), [
            'name' => 'editor',
            'permissions' => ['post.view', 'post.create'],
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::where('name', 'editor')->first();

    expect($role)->not->toBeNull();
    expect($role->permissions->pluck('name')->all())->toEqualCanonicalizing(['post.view', 'post.create']);
});

test('nama role tidak boleh duplikat', function () {
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->post(route('admin.roles.store'), ['name' => 'editor'])
        ->assertSessionHasErrors('name');
});

test('role developer tidak bisa dibuka halaman edit-nya', function () {
    $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->get(route('admin.roles.edit', $developer))
        ->assertForbidden();
});

test('role developer tidak bisa diupdate', function () {
    $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->put(route('admin.roles.update', $developer), ['name' => 'developer-baru'])
        ->assertForbidden();

    expect($developer->fresh()->name)->toBe('developer');
});

test('role developer tidak bisa dihapus', function () {
    $developer = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->delete(route('admin.roles.destroy', $developer))
        ->assertForbidden();

    expect(Role::where('name', 'developer')->exists())->toBeTrue();
});

test('role biasa bisa diupdate dan dihapus', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs(roleManager())
        ->put(route('admin.roles.update', $role), ['name' => 'editor-senior'])
        ->assertRedirect(route('admin.roles.index'));

    expect($role->fresh()->name)->toBe('editor-senior');

    $this->actingAs(roleManager())
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::where('id', $role->id)->exists())->toBeFalse();
});

test('user dengan role developer bisa akses manajemen role walau tanpa permission eksplisit', function () {
    $developerRole = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($developerRole);

    $this->actingAs($user)
        ->get(route('admin.roles.index'))
        ->assertOk();
});
