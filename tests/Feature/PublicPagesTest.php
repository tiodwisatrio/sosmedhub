<?php

use Illuminate\Support\Facades\View;
use Modules\SiteSetting\Models\SiteSetting;

dataset('halaman publik', [
    'kebijakan privasi' => ['privacy', 'Kebijakan Privasi', 'instagram_business_content_publish'],
    'ketentuan layanan' => ['terms', 'Ketentuan Layanan', 'tidak berafiliasi'],
    'penghapusan data' => ['data-deletion', 'Penghapusan Data', 'Hapus Data Sosmedhub'],
]);

it('menampilkan halaman publik tanpa login', function (string $route, string $heading, string $phrase) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee($heading)
        ->assertSee($phrase);
})->with('halaman publik');

it('menautkan tiga halaman hukum dari footer', function () {
    $this->get(route('privacy'))
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('data-deletion'), false);
});

it('menampilkan email kontak dari pengaturan situs', function () {
    SiteSetting::query()->delete();
    $setting = SiteSetting::create(['app_name' => 'Sosmedhub', 'email' => 'halo@contoh.test']);

    // Provider membagikan pengaturan saat boot, jadi bagikan ulang setelah data dibuat.
    View::share('siteSetting', $setting);

    $this->get(route('data-deletion'))->assertSee('halo@contoh.test');
});

it('memasukkan halaman publik ke sitemap', function () {
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('data-deletion'), false);
});
