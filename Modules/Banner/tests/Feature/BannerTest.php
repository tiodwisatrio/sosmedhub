<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Banner\Models\Banner;
use Spatie\Permission\Models\Permission;

function bannerManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'banner.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'banner.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'banner.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'banner.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman banner', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.banners.index'))
        ->assertForbidden();
});

test('banner baru bisa dibuat beserta gambar', function () {
    Storage::fake('public');

    $this->actingAs(bannerManager())
        ->post(route('admin.banners.store'), [
            'nama_banner' => 'Promo Akhir Tahun',
            'deskripsi_banner' => 'Diskon 50%',
            'gambar_banner' => UploadedFile::fake()->image('banner.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.banners.index'));

    $banner = Banner::firstWhere('nama_banner', 'Promo Akhir Tahun');

    expect($banner)->not->toBeNull();
    Storage::disk('public')->assertExists($banner->gambar_banner);
});

test('banner gagal dibuat tanpa nama', function () {
    $this->actingAs(bannerManager())
        ->post(route('admin.banners.store'), ['status' => 1])
        ->assertSessionHasErrors('nama_banner');
});

test('banner bisa diupdate', function () {
    $banner = Banner::create(['nama_banner' => 'Promo Lama', 'status' => 1]);

    $this->actingAs(bannerManager())
        ->put(route('admin.banners.update', $banner), [
            'nama_banner' => 'Promo Baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.banners.index'));

    expect($banner->fresh()->nama_banner)->toBe('Promo Baru');
});

test('banner bisa dihapus beserta gambarnya', function () {
    Storage::fake('public');
    Storage::disk('public')->put('banners/lama.jpg', 'dummy');

    $banner = Banner::create([
        'nama_banner' => 'Promo Lama',
        'gambar_banner' => 'banners/lama.jpg',
        'status' => 1,
    ]);

    $this->actingAs(bannerManager())
        ->delete(route('admin.banners.destroy', $banner))
        ->assertRedirect(route('admin.banners.index'));

    expect(Banner::where('id', $banner->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('banners/lama.jpg');
});
