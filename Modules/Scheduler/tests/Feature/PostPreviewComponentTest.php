<?php

use App\Models\User;
use Illuminate\Support\Facades\View;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SiteSetting\Models\SiteSetting;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;

function previewUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(
        array_map(
            fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
            ['scheduler.view', 'scheduler.create', 'scheduler.edit']
        )
    );

    return $user;
}

function previewAccount(User $user): SocialAccount
{
    return SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'provider_account_id' => 'ig-preview',
        'username' => 'toko_preview',
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);
}

test('halaman buat dan ubah memakai komponen pratinjau yang sama', function () {
    $user = previewUser();
    $account = previewAccount($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'status' => ScheduledPost::STATUS_SCHEDULED,
        'scheduled_at' => now()->addDay(),
    ]);

    foreach ([route('admin.scheduled-posts.create'), route('admin.scheduled-posts.edit', $post)] as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            // bingkai ponsel, kartu ringkasan, dan username akun dari komponen
            ->assertSee('aspect-[9/19.5]', false)
            ->assertSee('Ringkasan jadwal')
            ->assertSee('toko_preview')
            // tiga bingkai pratinjau dan tombol geser antar format
            ->assertSee("x-show=\"active === 'feed'\"", false)
            ->assertSee("x-show=\"active === 'story'\"", false)
            ->assertSee("x-show=\"active === 'reel'\"", false)
            ->assertSee('Pratinjau per format')
            ->assertSee('Pratinjau format sebelumnya')
            ->assertSee('Pratinjau format berikutnya')
            // state Alpine bersama berasal dari satu komponen
            ->assertSee('Alpine.data(\'postComposer\'', false)
            ->assertSee('get photoCountLabel()', false)
            // video pratinjau bisa diputar: klik untuk putar/jeda, tombol suara, dan dijeda saat berganti
            ->assertSee('Putar atau jeda video')
            ->assertSee('Matikan suara')
            ->assertSee('data-preview-video', false)
            ->assertSee('stopPlayback()', false)
            ->assertSee('trackProgress(event)', false)
            ->assertDontSee('mediaPreviews', false);
    }
});

test('input file tersembunyi berada di dalam label relatif agar tidak menggulung seluruh halaman', function () {
    $user = previewUser();
    previewAccount($user);

    $html = $this->actingAs($user)->get(route('admin.scheduled-posts.create'))->assertOk()->getContent();

    // Tiap uploader: label pembungkus input sr-only harus "relative". Tanpa itu, input yang letaknya
    // di luar layar (misalnya uploader Reels) memaksa browser menggulung kerangka halaman saat dialog
    // file dibuka, sehingga muncul ruang kosong di bawah dan header ikut tergeser.
    preg_match_all('/<label[^>]*>(?:(?!<\/label>).)*?data-media-input="[a-z]+".*?<\/label>/s', $html, $labels);
    expect($labels[0])->toHaveCount(3);

    foreach ($labels[0] as $label) {
        expect($label)->toMatch('/^<label[^>]*class="[^"]*\brelative\b/');
    }

    // Kerangka halaman admin tidak boleh bisa digulung secara programatik oleh elemen mana pun.
    expect($html)->toMatch('/class="flex h-screen overflow-hidden overflow-clip"/');
});

test('uploader menyediakan seret-lepas, tombol panah, dan token urutan untuk tiap format', function () {
    $user = previewUser();
    previewAccount($user);

    $this->actingAs($user)->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        // kotak media bisa diseret dan menerima lepasan
        ->assertSee('data-media-tile', false)
        ->assertSee('@dragstart=', false)
        ->assertSee('@dragover=', false)
        ->assertSee('@drop.prevent=', false)
        ->assertSee('@dragend=', false)
        // alternatif untuk layar sentuh dan keyboard
        ->assertSee('Geser ke kiri')
        ->assertSee('Geser ke kanan')
        ->assertSee('Seret media untuk mengatur urutan, atau pakai tombol panah.')
        ->assertSee('Foto pertama menjadi sampul carousel.')
        ->assertSee('Urutan ini juga urutan tayang Story.')
        // urutan akhir dikirim ke server per format
        ->assertSee('name="order[feed][]"', false)
        ->assertSee('name="order[story][]"', false)
        ->assertSee('name="order[reel][]"', false)
        ->assertSee('moveTo(format, fromKey, toKey)', false);
});

test('pratinjau menyediakan tampilan Facebook dan data akun yang berubah mengikuti pilihan', function () {
    $user = previewUser();
    previewAccount($user);
    SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_FACEBOOK,
        'provider_account_id' => 'fb-preview',
        'username' => 'Halaman Preview',
        'display_name' => 'Halaman Preview',
        'avatar_url' => 'https://example.test/avatar.jpg',
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        // layar Facebook dan layar Instagram saling bergantian menurut platform akun
        ->assertSee('x-show="isFacebook"', false)
        ->assertSee('x-show="! isFacebook"', false)
        ->assertSee('Komentar')
        ->assertSee('Bagikan')
        // nama dan foto akun dikirim ke Alpine untuk pratinjau
        ->assertSee('Halaman Preview')
        ->assertSee('example.test', false)
        ->assertSee('avatar.jpg', false);
});

/**
 * Mengubah Pengaturan Situs lalu membagikan ulang ke view: variabel $siteSetting dibagikan sekali saat boot,
 * jadi perubahan di tengah tes tidak terlihat tanpa ini.
 */
function previewSiteSetting(array $values): void
{
    $setting = SiteSetting::current();
    $setting->update($values);

    View::share('siteSetting', $setting->fresh());
}

test('pratinjau tanpa akun tertaut memakai nama dan gambar dari Pengaturan Situs', function () {
    previewSiteSetting([
        'app_name' => 'Merek Uji',
        'instagram_nama' => 'Nama Instagram Contoh',
        'icon' => 'site-settings/ikon-uji.jpg',
        'logo_atas' => 'site-settings/logo-uji.jpg',
    ]);

    $this->actingAs(previewUser())
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('<span x-text="accountName">Merek Uji</span>', false)
        ->assertDontSee('<span x-text="accountName">Nama Instagram Contoh</span>', false)
        ->assertSee('ikon-uji.jpg', false)
        ->assertDontSee('logo-uji.jpg', false);
});

test('pratinjau tanpa ikon memakai logo atas, dan tanpa gambar situs tidak memuat gambar', function () {
    previewSiteSetting(['app_name' => 'Merek Uji', 'icon' => null, 'logo_atas' => 'site-settings/logo-uji.jpg']);

    $this->actingAs(previewUser())
        ->get(route('admin.scheduled-posts.create'))
        ->assertSee('logo-uji.jpg', false);

    previewSiteSetting(['icon' => null, 'logo_atas' => null]);

    $this->actingAs(previewUser())
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertDontSee('site-settings', false);
});

test('pratinjau dengan akun tertaut memakai username akun, bukan nama situs', function () {
    previewSiteSetting(['app_name' => 'Merek Uji']);
    $user = previewUser();
    previewAccount($user);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('<span x-text="accountName">toko_preview</span>', false)
        ->assertDontSee('<span x-text="accountName">Merek Uji</span>', false);
});

test('form jadwal tanpa akun tertaut menawarkan tombol Hubungkan Akun ke halaman Akun Sosial', function () {
    $user = previewUser();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'social-account.view', 'guard_name' => 'web']));

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('data-connect-account', false)
        ->assertSee('href="'.route('admin.social-accounts.index').'"', false)
        ->assertSee('Hubungkan Akun');

    $this->actingAs($user)->withCookie('locale', 'en')
        ->get(route('admin.scheduled-posts.create'))
        ->assertSee('Connect Account');
});

test('tombol Hubungkan Akun tidak tampil bila akun sudah ada atau pengguna tidak boleh membuka Akun Sosial', function () {
    // Tanpa izin social-account.view: pesan tetap ada, tombol tidak.
    $this->actingAs(previewUser())
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('Belum ada akun sosial aktif')
        ->assertDontSee('data-connect-account', false);

    // Sudah punya akun: tidak ada pesan kosong maupun tombolnya.
    $user = previewUser();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'social-account.view', 'guard_name' => 'web']));
    previewAccount($user);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertDontSee('Belum ada akun sosial aktif')
        ->assertDontSee('data-connect-account', false);
});
