<?php

use App\Models\User;
use Modules\TentangKami\Models\TentangKami;
use Spatie\Permission\Models\Permission;

function tentangKamiEditor(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'tentang-kami.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'tentang-kami.edit', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('halaman tentang kami menampilkan form', function () {
    $this->actingAs(tentangKamiEditor())
        ->get(route('admin.tentang-kami.index'))
        ->assertOk()
        ->assertSee('Tentang Kami')
        ->assertSee('Statistik');
});

test('user tanpa permission tidak bisa akses halaman tentang kami', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.tentang-kami.index'))
        ->assertForbidden();
});

test('tentang kami bisa disimpan beserta statistiknya', function () {
    $this->actingAs(tentangKamiEditor())
        ->put(route('admin.tentang-kami.update'), [
            'tentangkami_judul' => 'Tentang Crafthink',
            'tentangkami_deskripsi' => '<p>Kami agensi digital.</p>',
            'tentangkami_visi' => 'Jadi mitra digital terpercaya.',
            'tentangkami_misi' => 'Memberikan solusi web, aplikasi, dan sistem.',
            'stats' => [
                ['tentangkami_stats_label' => 'Proyek Selesai', 'tentangkami_stats_angka' => 150],
            ],
        ])
        ->assertRedirect(route('admin.tentang-kami.index'))
        ->assertSessionHas('success');

    $tentangKami = TentangKami::current();

    expect($tentangKami->tentangkami_judul)->toBe('Tentang Crafthink')
        ->and($tentangKami->tentangkami_deskripsi)->toContain('Kami agensi digital.')
        ->and($tentangKami->stats)->toHaveCount(1)
        ->and($tentangKami->stats->first()->tentangkami_stats_label)->toBe('Proyek Selesai')
        ->and($tentangKami->stats->first()->tentangkami_stats_angka)->toBe(150);
});

test('menghapus baris statistik dari form ikut menghapus datanya', function () {
    $tentangKami = TentangKami::current();
    $tentangKami->update([
        'tentangkami_judul' => 'Tentang Crafthink',
        'tentangkami_deskripsi' => 'Kami agensi digital.',
    ]);
    $tentangKami->stats()->create([
        'tentangkami_stats_label' => 'Klien Puas',
        'tentangkami_stats_angka' => 50,
    ]);

    $this->actingAs(tentangKamiEditor())
        ->put(route('admin.tentang-kami.update'), [
            'tentangkami_judul' => 'Tentang Crafthink',
            'tentangkami_deskripsi' => 'Kami agensi digital.',
            'stats' => [],
        ])
        ->assertRedirect(route('admin.tentang-kami.index'));

    expect($tentangKami->fresh()->stats)->toHaveCount(0);
});

test('tentang kami menolak judul kosong', function () {
    $this->actingAs(tentangKamiEditor())
        ->put(route('admin.tentang-kami.update'), [
            'tentangkami_deskripsi' => 'Kami agensi digital.',
        ])
        ->assertSessionHasErrors('tentangkami_judul');
});

test('script tag di deskripsi dan misi dibersihkan saat disimpan', function () {
    $this->actingAs(tentangKamiEditor())
        ->put(route('admin.tentang-kami.update'), [
            'tentangkami_judul' => 'Tentang Crafthink',
            'tentangkami_deskripsi' => '<p>Halo</p><script>alert(document.cookie)</script>',
            'tentangkami_misi' => '<ul><li>Poin misi</li></ul><script>alert(1)</script>',
        ])
        ->assertRedirect(route('admin.tentang-kami.index'));

    $tentangKami = TentangKami::current();

    expect($tentangKami->tentangkami_deskripsi)->not->toContain('<script>')
        ->and($tentangKami->tentangkami_misi)->not->toContain('<script>');
});
