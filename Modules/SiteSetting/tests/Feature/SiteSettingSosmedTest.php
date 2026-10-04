<?php

use App\Models\User;
use Modules\SiteSetting\Models\SiteSetting;
use Spatie\Permission\Models\Permission;

function siteSettingEditor(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'site-setting.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'site-setting.edit', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('halaman pengaturan situs menampilkan form sosmed dan marketplace', function () {
    $this->actingAs(siteSettingEditor())
        ->get(route('admin.site-settings.index'))
        ->assertOk()
        ->assertSee('Media Sosial')
        ->assertSee('Marketplace')
        ->assertSee('Peta Lokasi');
});

test('pengaturan situs bisa menyimpan deskripsi, sosmed, marketplace, dan iframe map', function () {
    $this->actingAs(siteSettingEditor())
        ->put(route('admin.site-settings.update'), [
            'app_name' => 'Sosmedhub',
            'deskripsi' => 'Penjadwal konten Instagram berbahasa Indonesia.',
            'iframe_map' => 'https://www.google.com/maps/embed?test',
            'instagram_nama' => '@sosmedhub',
            'instagram_link' => 'https://instagram.com/sosmedhub',
            'shopee_nama' => 'Sosmedhub',
            'shopee_link' => 'https://shopee.co.id/sosmedhub',
        ])
        ->assertRedirect(route('admin.site-settings.index'))
        ->assertSessionHas('success');

    $setting = SiteSetting::current();

    expect($setting->deskripsi)->toBe('Penjadwal konten Instagram berbahasa Indonesia.')
        ->and($setting->iframe_map)->toContain('google.com/maps/embed')
        ->and($setting->instagram_nama)->toBe('@sosmedhub')
        ->and($setting->instagram_link)->toBe('https://instagram.com/sosmedhub')
        ->and($setting->shopee_nama)->toBe('Sosmedhub')
        ->and($setting->shopee_link)->toBe('https://shopee.co.id/sosmedhub');
});

test('pengaturan situs menolak iframe map yang bukan url embed google maps', function () {
    $this->actingAs(siteSettingEditor())
        ->put(route('admin.site-settings.update'), [
            'app_name' => 'Sosmedhub',
            'iframe_map' => '<img src="x" onerror="alert(document.cookie)">',
        ])
        ->assertSessionHasErrors('iframe_map');

    $this->actingAs(siteSettingEditor())
        ->put(route('admin.site-settings.update'), [
            'app_name' => 'Sosmedhub',
            'iframe_map' => 'https://evil.example.com/maps/embed',
        ])
        ->assertSessionHasErrors('iframe_map');
});
