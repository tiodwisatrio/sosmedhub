<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Klien\Models\Klien;
use Spatie\Permission\Models\Permission;

function klienManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'klien.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'klien.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'klien.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'klien.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman klien', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.kliens.index'))
        ->assertForbidden();
});

test('klien baru bisa dibuat beserta logo', function () {
    Storage::fake('public');

    $this->actingAs(klienManager())
        ->post(route('admin.kliens.store'), [
            'nama_klien' => 'PT Sejahtera',
            'logo_klien' => UploadedFile::fake()->image('logo.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.kliens.index'));

    $klien = Klien::firstWhere('nama_klien', 'PT Sejahtera');

    expect($klien)->not->toBeNull();
    Storage::disk('public')->assertExists($klien->logo_klien);
});

test('klien gagal dibuat tanpa status', function () {
    $this->actingAs(klienManager())
        ->post(route('admin.kliens.store'), ['nama_klien' => 'PT Sejahtera'])
        ->assertSessionHasErrors('status');
});

test('klien bisa diupdate', function () {
    $klien = Klien::create(['nama_klien' => 'PT Lama', 'status' => 1]);

    $this->actingAs(klienManager())
        ->put(route('admin.kliens.update', $klien), [
            'nama_klien' => 'PT Baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.kliens.index'));

    expect($klien->fresh()->nama_klien)->toBe('PT Baru');
});

test('klien bisa dihapus', function () {
    $klien = Klien::create(['nama_klien' => 'PT Sejahtera', 'status' => 1]);

    $this->actingAs(klienManager())
        ->delete(route('admin.kliens.destroy', $klien))
        ->assertRedirect(route('admin.kliens.index'));

    expect(Klien::where('id', $klien->id)->exists())->toBeFalse();
});
