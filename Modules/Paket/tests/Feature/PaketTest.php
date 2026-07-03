<?php

use App\Models\User;
use Modules\Paket\Models\Paket;
use Spatie\Permission\Models\Permission;

function paketManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'paket.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'paket.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'paket.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'paket.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman paket', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.pakets.index'))
        ->assertForbidden();
});

test('paket baru bisa dibuat', function () {
    $this->actingAs(paketManager())
        ->post(route('admin.pakets.store'), [
            'nama_paket' => 'Paket Silver',
            'deskripsi_paket' => 'Paket hemat',
            'harga_paket' => 'Rp500.000',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.pakets.index'));

    expect(Paket::where('nama_paket', 'Paket Silver')->exists())->toBeTrue();
});

test('paket gagal dibuat tanpa nama', function () {
    $this->actingAs(paketManager())
        ->post(route('admin.pakets.store'), ['status' => 1])
        ->assertSessionHasErrors('nama_paket');
});

test('paket bisa diupdate', function () {
    $paket = Paket::create(['nama_paket' => 'Paket Lama', 'status' => 1]);

    $this->actingAs(paketManager())
        ->put(route('admin.pakets.update', $paket), [
            'nama_paket' => 'Paket Baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.pakets.index'));

    expect($paket->fresh()->nama_paket)->toBe('Paket Baru');
});

test('paket bisa dihapus', function () {
    $paket = Paket::create(['nama_paket' => 'Paket Silver', 'status' => 1]);

    $this->actingAs(paketManager())
        ->delete(route('admin.pakets.destroy', $paket))
        ->assertRedirect(route('admin.pakets.index'));

    expect(Paket::where('id', $paket->id)->exists())->toBeFalse();
});
