<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Models\Category;
use Modules\Team\Models\Team;
use Spatie\Permission\Models\Permission;

function teamManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'team.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'team.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'team.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'team.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

function teamCategory(): Category
{
    return Category::create(['type' => 'team', 'name' => 'Divisi Umum', 'slug' => 'divisi-umum']);
}

test('user tanpa permission tidak bisa akses halaman team', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.teams.index'))
        ->assertForbidden();
});

test('anggota team baru bisa dibuat beserta kategori dan foto', function () {
    Storage::fake('public');
    $category = teamCategory();

    $this->actingAs(teamManager())
        ->post(route('admin.teams.store'), [
            'name' => 'Budi Santoso',
            'description' => 'Backend Developer',
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('budi.jpg'),
            'status' => 1,
        ])
        ->assertRedirect(route('admin.teams.index'));

    $team = Team::firstWhere('name', 'Budi Santoso');

    expect($team)->not->toBeNull();
    expect($team->category_id)->toBe($category->id);
    Storage::disk('public')->assertExists($team->image);
});

test('anggota team gagal dibuat tanpa nama', function () {
    $this->actingAs(teamManager())
        ->post(route('admin.teams.store'), ['status' => 1])
        ->assertSessionHasErrors('name');
});

test('anggota team bisa diupdate', function () {
    $team = Team::create(['name' => 'Nama Lama', 'status' => 1]);

    $this->actingAs(teamManager())
        ->put(route('admin.teams.update', $team), [
            'name' => 'Nama Baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.teams.index'));

    expect($team->fresh()->name)->toBe('Nama Baru');
});

test('anggota team bisa dihapus', function () {
    $team = Team::create(['name' => 'Budi Santoso', 'status' => 1]);

    $this->actingAs(teamManager())
        ->delete(route('admin.teams.destroy', $team))
        ->assertRedirect(route('admin.teams.index'));

    expect(Team::where('id', $team->id)->exists())->toBeFalse();
});
