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

describe('halaman publik dua bahasa', function () {
    $scopes = [
        'instagram_business_basic', 'instagram_business_content_publish',
        'pages_show_list', 'pages_read_engagement', 'pages_manage_posts', 'business_management',
    ];

    $pages = [
        'home' => ['/', 'Plan today.', 'Susun hari ini.'],
        'privacy' => ['/kebijakan-privasi', 'Privacy Policy', 'Kebijakan Privasi'],
        'terms' => ['/ketentuan-layanan', 'Terms of Service', 'Ketentuan Layanan'],
        'data-deletion' => ['/hapus-data', 'Data Deletion', 'Penghapusan Data'],
    ];

    foreach ($pages as $name => [$path, $english, $indonesian]) {
        it("menampilkan {$name} dalam Indonesia secara bawaan dan English bila dipilih", function () use ($path, $english, $indonesian) {
            $this->get($path)->assertOk()
                ->assertSee('<html lang="id"', false)
                ->assertSee($indonesian)
                ->assertDontSee($english);

            $this->withCookie('locale', 'en')->get($path)->assertOk()
                ->assertSee('<html lang="en"', false)
                ->assertSee($english)
                ->assertDontSee($indonesian);
        });
    }

    it('menyebut keenam izin Meta di kebijakan privasi kedua bahasa', function () use ($scopes) {
        $id = $this->get('/kebijakan-privasi')->getContent();
        $en = $this->withCookie('locale', 'en')->get('/kebijakan-privasi')->getContent();

        foreach ($scopes as $scope) {
            expect($id)->toContain($scope);
            expect($en)->toContain($scope);
        }
    });

    it('menyebut keenam izin Meta di beranda kedua bahasa', function () use ($scopes) {
        $id = $this->get('/')->getContent();
        $en = $this->withCookie('locale', 'en')->get('/')->getContent();

        foreach ($scopes as $scope) {
            expect($id)->toContain($scope);
            expect($en)->toContain($scope);
        }
    });

    it('menyambungkan tiga halaman hukum dari footer dan menyediakan saklar bahasa di navbar', function () {
        foreach ([null, 'en'] as $locale) {
            $request = $locale ? $this->withCookie('locale', $locale) : $this;

            $request->get('/')
                ->assertOk()
                ->assertSee(route('privacy'), false)
                ->assertSee(route('terms'), false)
                ->assertSee(route('data-deletion'), false)
                ->assertSee(route('locale.update'), false)
                ->assertSee('data-flag="en"', false);
        }
    });

    it('menyebut Facebook dan cara mencabut akses di Facebook pada halaman hapus data kedua bahasa', function () {
        $this->get('/hapus-data')->assertSee('Facebook')->assertSee('Integrasi bisnis');
        $this->withCookie('locale', 'en')->get('/hapus-data')->assertSee('Facebook')->assertSee('Business Integrations');
    });

    it('memakai batas durasi dari konfigurasi di Ketentuan Layanan kedua bahasa', function () {
        $this->get('/ketentuan-layanan')->assertSee('3 menit')->assertSee('1 menit 30 detik');
        $this->withCookie('locale', 'en')->get('/ketentuan-layanan')->assertSee('3 min')->assertSee('1 min 30 sec');
    });
});
