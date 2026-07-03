<?php

use Illuminate\Support\Facades\View;
use Modules\SiteSetting\Models\SiteSetting;

test('halaman kontak menampilkan info dari site setting', function () {
    $setting = SiteSetting::current();
    $setting->update([
        'alamat' => 'Jl. Contoh No. 1',
        'no_telp' => '021123456',
        'no_whatsapp' => '081234567890',
        'email' => 'kontak@crafthink.test',
        'iframe_map' => 'https://www.google.com/maps/embed?test',
    ]);
    View::share('siteSetting', $setting->fresh());

    $this->get(route('kontak'))
        ->assertOk()
        ->assertSee('Jl. Contoh No. 1')
        ->assertSee('021123456')
        ->assertSee('081234567890')
        ->assertSee('kontak@crafthink.test')
        ->assertSee('google.com/maps/embed', false);
});

test('halaman kontak tidak mengeksekusi html mentah walau iframe_map berisi payload berbahaya', function () {
    $setting = SiteSetting::current();
    $setting->update([
        'iframe_map' => '<img src="x" onerror="alert(document.cookie)">',
    ]);
    View::share('siteSetting', $setting->fresh());

    $this->get(route('kontak'))
        ->assertOk()
        ->assertDontSee('<img src="x" onerror="alert', false)
        ->assertSee('&lt;img src=&quot;x&quot;', false);
});

test('halaman kontak tetap tampil walau field opsional kosong', function () {
    $this->get(route('kontak'))
        ->assertOk()
        ->assertSee('Peta belum tersedia.');
});

test('navbar dan footer mengarah ke halaman kontak yang sesungguhnya, bukan anchor', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain(route('kontak'))
        ->and($content)->not->toContain('href="#kontak"');
});
