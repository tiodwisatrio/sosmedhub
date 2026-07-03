<?php

use Modules\Layanan\Models\Layanan;

test('navbar punya menu mobile dengan hamburger toggle', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('id="nav-hamburger-btn"', false)
        ->assertSee('id="nav-mobile-panel"', false)
        ->assertSee('md:hidden', false);
});

test('menu mobile tampil full-screen dengan teks besar', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain('fixed inset-0 z-40 bg-black')
        ->and($content)->toContain('text-4xl sm:text-5xl');
});

test('menu mobile punya animasi tirai clip-path', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain('clip-path: inset(0 0 100% 0)')
        ->and($content)->toContain('nav-menu-open')
        ->and($content)->toContain('#nav-mobile-panel.nav-menu-open');
});

test('navbar selalu di atas elemen lain yang juga z-50 seperti indikator scroll-down', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain('id="site-navbar" class="fixed top-0 inset-x-0 z-[60]')
        ->and($content)->toContain('z-50 flex flex-col items-center gap-3 pb-10');
});

test('navbar tidak bergantung pada Alpine.js yang tidak dimuat di halaman ini', function () {
    $content = $this->get('/')->assertOk()->getContent();

    // Cek pemakaian direktif Alpine sungguhan (atribut HTML), bukan sekadar
    // substring — Livewire menyuntik CSS selector `[x-cloak]` di setiap
    // halaman begitu ada komponen Livewire lain yang pernah dirender dalam
    // proses test yang sama, walau halaman ini sendiri tidak memakai Alpine.
    expect($content)->not->toMatch('/\sx-data=/')
        ->and($content)->not->toMatch('/\sx-show=/')
        ->and($content)->not->toMatch('/\sx-cloak(\s|>|=)/');
});

test('section layanan di homepage tidak pakai lebar/tinggi fixed piksel', function () {
    Layanan::factory()->create(['status' => 1]);

    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->not->toContain('style="width: 320px; height: 320px;"')
        ->and($content)->toContain('sm:w-[320px]');
});

test('halaman layanan tidak pakai lebar/tinggi fixed piksel', function () {
    Layanan::factory()->create(['status' => 1]);

    $content = $this->get(route('layanan.index'))->assertOk()->getContent();

    expect($content)->not->toContain('style="width: 320px; height: 320px;"')
        ->and($content)->toContain('sm:w-[320px]');
});
