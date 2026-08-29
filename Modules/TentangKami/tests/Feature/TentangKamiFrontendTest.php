<?php

use Modules\TentangKami\Models\TentangKami;

test('halaman tentang kami publik menampilkan judul, deskripsi, dan statistik', function () {
    $tentangKami = TentangKami::current();
    $tentangKami->update([
        'tentangkami_judul' => 'Tentang Crafthink',
        'tentangkami_deskripsi' => '<p>Kami agensi digital terpercaya.</p>',
        'tentangkami_visi' => 'Jadi mitra digital terpercaya.',
        'tentangkami_misi' => '<ul><li>Memberikan solusi web dan aplikasi.</li></ul>',
    ]);
    $tentangKami->stats()->create([
        'tentangkami_stats_label' => 'Proyek Selesai',
        'tentangkami_stats_angka' => 150,
    ]);

    $this->get(route('tentang-kami.index'))
        ->assertOk()
        ->assertSee('Tentang Crafthink')
        ->assertSee('Kami agensi digital terpercaya.')
        ->assertSee('Jadi mitra digital terpercaya.')
        ->assertSee('Memberikan solusi web dan aplikasi.')
        ->assertSee('Proyek Selesai')
        ->assertSee('150');
});

test('halaman tentang kami tetap tampil walau belum ada statistik', function () {
    $this->get(route('tentang-kami.index'))
        ->assertOk();
});

test('navbar dan footer mengarah ke halaman tentang kami yang sesungguhnya, bukan anchor', function () {
    $content = $this->get('/')->assertOk()->getContent();

    expect($content)->toContain(route('tentang-kami.index'))
        ->and($content)->not->toContain('href="tentang-kami"')
        ->and($content)->not->toContain('#tentang-kami"');
});
