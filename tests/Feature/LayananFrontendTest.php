<?php

use Modules\Layanan\Models\Layanan;

test('halaman layanan menampilkan layanan aktif', function () {
    Layanan::factory()->create(['name' => 'Website Company Profile', 'status' => 1]);
    Layanan::factory()->create(['name' => 'Layanan Nonaktif', 'status' => 0]);

    $this->get(route('layanan.index'))
        ->assertOk()
        ->assertSee('Website Company Profile')
        ->assertDontSee('Layanan Nonaktif');
});

test('halaman layanan tetap tampil saat belum ada data', function () {
    $this->get(route('layanan.index'))
        ->assertOk()
        ->assertSee('Belum ada layanan yang tersedia.');
});
