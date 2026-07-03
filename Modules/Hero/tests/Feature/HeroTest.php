<?php

use App\Models\User;
use Modules\Hero\Models\Hero;
use Spatie\Permission\Models\Permission;

function heroManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'hero.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'hero.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'hero.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'hero.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman hero', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.heroes.index'))
        ->assertForbidden();
});

test('hero baru bisa dibuat', function () {
    $this->actingAs(heroManager())
        ->post(route('admin.heroes.store'), [
            'judul_hero' => 'Selamat Datang',
            'deskripsi_hero' => 'Solusi digital terbaik untuk bisnis Anda.',
            'button_hero' => 'Hubungi Kami',
        ])
        ->assertRedirect(route('admin.heroes.index'));

    expect(Hero::where('judul_hero', 'Selamat Datang')->exists())->toBeTrue();
});

test('hero gagal dibuat tanpa deskripsi', function () {
    $this->actingAs(heroManager())
        ->post(route('admin.heroes.store'), [
            'judul_hero' => 'Selamat Datang',
            'button_hero' => 'Hubungi Kami',
        ])
        ->assertSessionHasErrors('deskripsi_hero');
});

test('hero bisa diupdate', function () {
    $hero = Hero::create([
        'judul_hero' => 'Judul Lama',
        'deskripsi_hero' => 'Deskripsi lama',
        'button_hero' => 'Klik',
    ]);

    $this->actingAs(heroManager())
        ->put(route('admin.heroes.update', $hero), [
            'judul_hero' => 'Judul Baru',
            'deskripsi_hero' => 'Deskripsi baru',
            'button_hero' => 'Klik',
        ])
        ->assertRedirect(route('admin.heroes.index'));

    expect($hero->fresh()->judul_hero)->toBe('Judul Baru');
});

test('hero bisa dihapus', function () {
    $hero = Hero::create([
        'judul_hero' => 'Selamat Datang',
        'deskripsi_hero' => 'Deskripsi',
        'button_hero' => 'Klik',
    ]);

    $this->actingAs(heroManager())
        ->delete(route('admin.heroes.destroy', $hero))
        ->assertRedirect(route('admin.heroes.index'));

    expect(Hero::where('id', $hero->id)->exists())->toBeFalse();
});
