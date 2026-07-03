<?php

use App\Models\User;
use Modules\Category\Models\Category;
use Spatie\Permission\Models\Permission;

function categoryManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'category.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'category.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'category.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'category.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman kategori', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('index hanya menampilkan kategori sesuai type', function () {
    Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita']);
    Category::create(['type' => 'team', 'name' => 'Divisi', 'slug' => 'divisi']);

    $this->actingAs(categoryManager())
        ->get(route('admin.categories.index', ['type' => 'post']))
        ->assertOk()
        ->assertSee('Berita')
        ->assertDontSee('Divisi');
});

test('kategori baru bisa dibuat', function () {
    $this->actingAs(categoryManager())
        ->post(route('admin.categories.store'), [
            'type' => 'post',
            'name' => 'Tutorial',
            'slug' => 'tutorial',
            'description' => 'Kategori tutorial',
        ])
        ->assertRedirect(route('admin.categories.index', ['type' => 'post']));

    expect(Category::where('slug', 'tutorial')->exists())->toBeTrue();
});

test('slug kategori tidak boleh duplikat', function () {
    Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita']);

    $this->actingAs(categoryManager())
        ->post(route('admin.categories.store'), [
            'type' => 'post',
            'name' => 'Berita Lagi',
            'slug' => 'berita',
        ])
        ->assertSessionHasErrors('slug');
});

test('kategori bisa diupdate', function () {
    $category = Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita']);

    $this->actingAs(categoryManager())
        ->put(route('admin.categories.update', $category), [
            'type' => 'post',
            'name' => 'Berita Terkini',
            'slug' => 'berita',
        ])
        ->assertRedirect(route('admin.categories.index', ['type' => 'post']));

    expect($category->fresh()->name)->toBe('Berita Terkini');
});

test('kategori bisa dihapus', function () {
    $category = Category::create(['type' => 'post', 'name' => 'Berita', 'slug' => 'berita']);

    $this->actingAs(categoryManager())
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index', ['type' => 'post']));

    expect(Category::where('id', $category->id)->exists())->toBeFalse();
});
