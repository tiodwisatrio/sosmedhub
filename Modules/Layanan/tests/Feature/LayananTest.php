<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Layanan\Models\Layanan;
use Spatie\Permission\Models\Permission;

function layananManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'layanan.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'layanan.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'layanan.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'layanan.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman layanan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.layanans.index'))
        ->assertForbidden();
});

test('layanan baru bisa dibuat beserta gambar', function () {
    Storage::fake('public');

    $this->actingAs(layananManager())
        ->post(route('admin.layanans.store'), [
            'name' => 'Desain Web',
            'description' => 'Jasa desain website',
            'image' => UploadedFile::fake()->image('layanan.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.layanans.index'));

    $layanan = Layanan::firstWhere('name', 'Desain Web');

    expect($layanan)->not->toBeNull();
    Storage::disk('public')->assertExists($layanan->image);
});

test('layanan gagal dibuat tanpa nama', function () {
    $this->actingAs(layananManager())
        ->post(route('admin.layanans.store'), ['status' => 1])
        ->assertSessionHasErrors('name');
});

test('layanan bisa diupdate dan gambar lama dihapus saat ganti gambar baru', function () {
    Storage::fake('public');

    $layanan = Layanan::create([
        'name' => 'Desain Web',
        'image' => 'layanan/lama.jpg',
        'status' => 1,
    ]);
    Storage::disk('public')->put('layanan/lama.jpg', 'dummy');

    $this->actingAs(layananManager())
        ->put(route('admin.layanans.update', $layanan), [
            'name' => 'Desain Web Premium',
            'image' => UploadedFile::fake()->image('baru.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.layanans.index'));

    $layanan->refresh();

    expect($layanan->name)->toBe('Desain Web Premium');
    Storage::disk('public')->assertMissing('layanan/lama.jpg');
    Storage::disk('public')->assertExists($layanan->image);
});

test('layanan bisa dihapus', function () {
    $layanan = Layanan::create(['name' => 'Desain Web', 'status' => 1]);

    $this->actingAs(layananManager())
        ->delete(route('admin.layanans.destroy', $layanan))
        ->assertRedirect(route('admin.layanans.index'));

    expect(Layanan::where('id', $layanan->id)->exists())->toBeFalse();
});
