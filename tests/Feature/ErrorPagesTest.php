<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('halaman 404 kustom tampil untuk route yang tidak ada', function () {
    $this->get('/halaman-yang-tidak-pernah-ada-'.uniqid())
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan.')
        ->assertSee('Kembali ke Beranda');
});

test('halaman 404 kustom tampil untuk post dengan slug yang tidak ada', function () {
    $this->get('/posts/slug-tidak-ada-'.uniqid())
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan.');
});

test('halaman 403 kustom tampil saat user tidak punya permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden()
        ->assertSee('Akses ditolak.')
        ->assertSee('Kembali ke Dashboard');
});

test('halaman 403 kustom mengarahkan ke beranda untuk guest', function () {
    $this->view('errors.403')
        ->assertSee('Akses ditolak.')
        ->assertSee('Kembali ke Beranda');
});

test('halaman 419 kustom bisa dirender', function () {
    $this->view('errors.419')
        ->assertSee('Sesi Anda telah berakhir.')
        ->assertSee('Muat Ulang Halaman');
});

test('halaman 429 kustom tampil saat rate limit forgot-password tercapai', function () {
    foreach (range(1, 6) as $_) {
        $this->get(route('password.request'));
    }

    $this->get(route('password.request'))
        ->assertStatus(429)
        ->assertSee('Terlalu banyak percobaan.');
});

test('halaman 500 kustom tampil saat terjadi error tak tertangani dan debug mode mati', function () {
    config(['app.debug' => false]);

    Route::get('/test-500-error', function () {
        throw new RuntimeException('Simulasi error server.');
    });

    $this->get('/test-500-error')
        ->assertServerError()
        ->assertSee('Terjadi kesalahan pada server.')
        ->assertSee('Kembali ke Beranda');
});
