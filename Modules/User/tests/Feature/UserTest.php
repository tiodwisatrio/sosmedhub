<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function userManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'user.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'user.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'user.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user dengan role developer disembunyikan dari daftar pengguna', function () {
    $developerRole = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $developer = User::factory()->create(['name' => 'Developer Rahasia']);
    $developer->assignRole($developerRole);

    $biasa = User::factory()->create(['name' => 'User Biasa']);

    $this->actingAs(userManager())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('User Biasa')
        ->assertDontSee('Developer Rahasia');
});

test('user tanpa permission tidak bisa akses halaman pengguna', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('pengguna baru bisa dibuat dengan password ter-hash dan role tersimpan', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs(userManager())
        ->post(route('admin.users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'phone' => '081234567890',
            'status' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'editor',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::firstWhere('email', 'budi@example.test');

    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($user->hasRole('editor'))->toBeTrue();
    expect($user->approval_status)->toBe(User::APPROVAL_APPROVED);
});

test('pengguna pending bisa di-approve dari daftar pengguna', function () {
    $target = User::factory()->pendingApproval()->create(['name' => 'Client Pending']);
    Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

    $manager = userManager();

    $this->actingAs($manager)
        ->patch(route('admin.users.approve', $target))
        ->assertRedirect(route('admin.users.index'));

    $fresh = $target->fresh();

    expect($fresh->approval_status)->toBe(User::APPROVAL_APPROVED)
        ->and($fresh->approved_by)->toBe($manager->id)
        ->and($fresh->status)->toBeTrue();
});

test('email pengguna tidak boleh duplikat', function () {
    User::factory()->create(['email' => 'duplikat@example.test']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs(userManager())
        ->post(route('admin.users.store'), [
            'name' => 'Nama Lain',
            'email' => 'duplikat@example.test',
            'status' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'editor',
        ])
        ->assertSessionHasErrors('email');
});

test('halaman edit user dengan role developer tidak bisa dibuka', function () {
    $developerRole = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $developer = User::factory()->create();
    $developer->assignRole($developerRole);

    $this->actingAs(userManager())
        ->get(route('admin.users.edit', $developer))
        ->assertForbidden();
});

test('user dengan role developer tidak bisa diupdate', function () {
    $developerRole = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $developer = User::factory()->create(['name' => 'Developer Asli']);
    $developer->assignRole($developerRole);

    $this->actingAs(userManager())
        ->put(route('admin.users.update', $developer), [
            'name' => 'Nama Diubah Paksa',
            'email' => $developer->email,
            'status' => 1,
        ])
        ->assertForbidden();

    expect($developer->fresh()->name)->toBe('Developer Asli');
});

test('user biasa bisa diupdate, password tidak berubah kalau dikosongkan', function () {
    $user = User::factory()->create(['status' => 1]);
    $originalPasswordHash = $user->password;

    $this->actingAs(userManager())
        ->put(route('admin.users.update', $user), [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'status' => 0,
        ])
        ->assertRedirect(route('admin.users.index'));

    $fresh = $user->fresh();

    expect($fresh->name)->toBe('Nama Baru');
    expect($fresh->status)->toBeFalse();
    expect($fresh->password)->toBe($originalPasswordHash);
});

test('user tidak bisa menghapus akunnya sendiri', function () {
    $manager = userManager();

    $this->actingAs($manager)
        ->delete(route('admin.users.destroy', $manager))
        ->assertForbidden();

    expect(User::where('id', $manager->id)->exists())->toBeTrue();
});

test('user dengan role developer tidak bisa dihapus', function () {
    $developerRole = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $developer = User::factory()->create();
    $developer->assignRole($developerRole);

    $this->actingAs(userManager())
        ->delete(route('admin.users.destroy', $developer))
        ->assertForbidden();

    expect(User::where('id', $developer->id)->exists())->toBeTrue();
});

test('user biasa bisa dihapus oleh pengguna lain', function () {
    $target = User::factory()->create();

    $this->actingAs(userManager())
        ->delete(route('admin.users.destroy', $target))
        ->assertRedirect(route('admin.users.index'));

    expect(User::where('id', $target->id)->exists())->toBeFalse();
});
