<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function developer(): User
{
    $role = Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('halaman generator tampil untuk developer', function () {
    $this->actingAs(developer());

    $this->get(route('admin.generator.index'))
        ->assertOk()
        ->assertSee('Generator Modul');
});

test('generator menolak input tanpa nama modul', function () {
    $this->actingAs(developer());

    $this->from(route('admin.generator.index'))
        ->post(route('admin.generator.store'), [
            'fields' => [
                ['label' => 'Judul', 'name' => 'title', 'type' => 'string', 'nullable' => 0],
            ],
        ])
        ->assertSessionHasErrors('name');
});

test('generator menolak input tanpa kolom', function () {
    $this->actingAs(developer());

    $this->from(route('admin.generator.index'))
        ->post(route('admin.generator.store'), [
            'name' => 'Contoh',
        ])
        ->assertSessionHasErrors('fields');
});

test('generator menolak nama kolom yang tidak valid', function () {
    $this->actingAs(developer());

    $this->from(route('admin.generator.index'))
        ->post(route('admin.generator.store'), [
            'name' => 'Contoh',
            'fields' => [
                ['label' => 'Judul', 'name' => 'Judul Besar', 'type' => 'string', 'nullable' => 0],
            ],
        ])
        ->assertSessionHasErrors('fields.0.name');
});

test('pengguna tanpa permission tidak bisa akses generator', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.generator.index'))->assertForbidden();
});
