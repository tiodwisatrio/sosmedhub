<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Keunggulan\Models\Keunggulan;
use Spatie\Permission\Models\Permission;

function keunggulanManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'keunggulan.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'keunggulan.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'keunggulan.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'keunggulan.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman keunggulan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.keunggulans.index'))
        ->assertForbidden();
});

test('keunggulan baru bisa dibuat beserta gambar', function () {
    Storage::fake('public');

    $this->actingAs(keunggulanManager())
        ->post(route('admin.keunggulans.store'), [
            'name' => 'Harga Terjangkau',
            'description' => 'Harga bersaing',
            'image' => UploadedFile::fake()->image('keunggulan.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.keunggulans.index'));

    $keunggulan = Keunggulan::firstWhere('name', 'Harga Terjangkau');

    expect($keunggulan)->not->toBeNull();
    Storage::disk('public')->assertExists($keunggulan->image);
});

test('keunggulan gagal dibuat tanpa nama', function () {
    $this->actingAs(keunggulanManager())
        ->post(route('admin.keunggulans.store'), ['status' => 1])
        ->assertSessionHasErrors('name');
});

test('keunggulan bisa diupdate', function () {
    $keunggulan = Keunggulan::create(['name' => 'Lama', 'status' => 1]);

    $this->actingAs(keunggulanManager())
        ->put(route('admin.keunggulans.update', $keunggulan), [
            'name' => 'Baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.keunggulans.index'));

    expect($keunggulan->fresh()->name)->toBe('Baru');
});

test('keunggulan bisa dihapus', function () {
    $keunggulan = Keunggulan::create(['name' => 'Harga Terjangkau', 'status' => 1]);

    $this->actingAs(keunggulanManager())
        ->delete(route('admin.keunggulans.destroy', $keunggulan))
        ->assertRedirect(route('admin.keunggulans.index'));

    expect(Keunggulan::where('id', $keunggulan->id)->exists())->toBeFalse();
});
