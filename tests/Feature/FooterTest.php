<?php

use Illuminate\Support\Facades\View;
use Modules\SiteSetting\Models\SiteSetting;

function updateSharedSiteSetting(array $data): SiteSetting
{
    $setting = SiteSetting::current();
    $setting->update($data);

    // $siteSetting sudah di-share ke semua view saat app boot (sebelum test
    // ini jalan), jadi perlu di-refresh manual supaya perubahan di atas
    // ikut terbaca saat request berikutnya di-assert.
    View::share('siteSetting', $setting->fresh());

    return $setting->fresh();
}

test('footer tampil di homepage dengan data site setting', function () {
    $setting = updateSharedSiteSetting([
        'deskripsi' => 'Deskripsi perusahaan untuk footer.',
        'alamat' => 'Jl. Contoh No. 1',
        'email' => 'kontak@crafthink.test',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('id="kontak"', false)
        ->assertSee('Deskripsi perusahaan untuk footer.')
        ->assertSee('Jl. Contoh No. 1')
        ->assertSee('kontak@crafthink.test')
        ->assertSee('&copy; '.date('Y').' '.$setting->app_name, false);
});

test('footer tampil di halaman layanan', function () {
    $this->get(route('layanan.index'))
        ->assertOk()
        ->assertSee('id="kontak"', false);
});

test('footer hanya menampilkan sosmed yang linknya diisi', function () {
    updateSharedSiteSetting([
        'instagram_nama' => '@crafthinkweb',
        'instagram_link' => 'https://instagram.com/crafthinkweb',
        'facebook_link' => null,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('@crafthinkweb')
        ->assertSee('https://instagram.com/crafthinkweb', false)
        ->assertDontSee('Facebook');
});
