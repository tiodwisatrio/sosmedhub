<?php

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function socialAccountUser(array $permissions = ['view']): User
{
    $pairs = [
        'view' => 'social-account.view',
        'create' => 'social-account.create',
        'edit' => 'social-account.edit',
        'delete' => 'social-account.delete',
    ];

    $user = User::factory()->create();
    $user->givePermissionTo(
        array_map(
            fn ($key) => Permission::firstOrCreate(['name' => $pairs[$key], 'guard_name' => 'web']),
            $permissions
        )
    );

    return $user;
}

function makeSocialAccount(User $user, array $attributes = []): SocialAccount
{
    return SocialAccount::create(array_replace([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'username' => 'brand_'.$user->id,
        'status' => SocialAccount::STATUS_ACTIVE,
    ], $attributes));
}

test('client hanya melihat akun sosial miliknya', function () {
    $user = socialAccountUser();
    $otherUser = User::factory()->create();

    makeSocialAccount($user, ['username' => 'akun_sendiri']);
    makeSocialAccount($otherUser, ['username' => 'akun_orang']);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertOk()
        ->assertSee('akun_sendiri')
        ->assertDontSee('akun_orang');
});

test('client bisa menambahkan akun sosial untuk dirinya sendiri', function () {
    $user = socialAccountUser(['view', 'create']);

    $this->actingAs($user)
        ->post(route('admin.social-accounts.store'), [
            'platform' => SocialAccount::PLATFORM_INSTAGRAM,
            'username' => 'brand_baru',
            'display_name' => 'Brand Baru',
            'status' => SocialAccount::STATUS_ACTIVE,
        ])
        ->assertRedirect(route('admin.social-accounts.index'));

    $account = SocialAccount::firstWhere('username', 'brand_baru');

    expect($account)->not->toBeNull()
        ->and($account->user_id)->toBe($user->id)
        ->and($account->isActive())->toBeTrue();
});

test('disconnect akun sosial mengubah status tanpa menghapus data', function () {
    $user = socialAccountUser(['view', 'delete']);
    $account = makeSocialAccount($user, ['access_token' => 'token-rahasia']);

    $this->actingAs($user)
        ->delete(route('admin.social-accounts.destroy', $account))
        ->assertRedirect(route('admin.social-accounts.index'));

    $fresh = $account->fresh();

    expect($fresh)->not->toBeNull()
        ->and($fresh->status)->toBe(SocialAccount::STATUS_DISCONNECTED)
        ->and($fresh->access_token)->toBeNull();
});

test('redirect OAuth Instagram mengarahkan ke halaman login Instagram', function () {
    $user = socialAccountUser(['create']);

    config([
        'social-account.instagram.client_id' => 'ig-client-id',
        'social-account.instagram.client_secret' => 'ig-secret',
        'social-account.instagram.redirect_uri' => 'http://localhost/callback',
    ]);

    $response = $this->actingAs($user)->get(route('admin.social-accounts.instagram.redirect'));

    $response->assertRedirect();
    expect(parse_url($response->headers->get('Location'), PHP_URL_HOST))->toBe('www.instagram.com');
});

test('redirect OAuth memberikan pesan bila belum dikonfigurasi', function () {
    $user = socialAccountUser(['create']);

    config(['social-account.instagram.client_id' => null]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.instagram.redirect'))
        ->assertRedirect(route('admin.social-accounts.create'))
        ->assertSessionHas('error');
});

test('callback OAuth menyimpan akun Instagram berhasil', function () {
    $user = socialAccountUser(['create']);
    $state = Crypt::encryptString(json_encode(['user_id' => $user->id]));
    session()->put('instagram_oauth_state', $state);

    config([
        'social-account.instagram.client_secret' => 'ig-secret',
        'social-account.instagram.graph_version' => 'v22.0',
    ]);

    Http::fake([
        'api.instagram.com/oauth/access_token' => Http::response([
            'data' => [[
                'access_token' => 'short-lived-token',
                'user_id' => 123456789,
                'permissions' => 'instagram_business_basic,instagram_business_content_publish',
            ]],
        ]),
        'graph.instagram.com/*' => function ($request) {
            if (str_contains($request->url(), 'access_token?')) {
                return Http::response(['access_token' => 'long-lived-token', 'expires_in' => 5184000]);
            }

            return Http::response([
                'user_id' => '123456789',
                'username' => 'brand_oke',
                'account_type' => 'BUSINESS',
                'profile_picture_url' => 'https://example.com/avatar.jpg',
            ]);
        },
    ]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.instagram.callback', [
            'code' => 'auth-code-abc',
            'state' => $state,
        ]))
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('success');

    $account = SocialAccount::where('provider_account_id', '123456789')->first();

    expect($account)->not->toBeNull()
        ->and($account->user_id)->toBe($user->id)
        ->and($account->username)->toBe('brand_oke')
        ->and($account->avatar_url)->toBe('https://example.com/avatar.jpg')
        ->and($account->isActive())->toBeTrue()
        ->and($account->access_token)->toBe('long-lived-token');

    $rawToken = DB::table('social_accounts')->where('id', $account->id)->value('access_token');
    expect($rawToken)->not->toBe('long-lived-token');
});

test('callback OAuth menolak state yang tidak cocok', function () {
    $user = socialAccountUser(['create']);
    session()->put('instagram_oauth_state', Crypt::encryptString(json_encode(['user_id' => $user->id])));

    $this->actingAs($user)
        ->get(route('admin.social-accounts.instagram.callback', [
            'code' => 'auth-code-abc',
            'state' => 'state-tidak-sama',
        ]))
        ->assertRedirect(route('admin.social-accounts.create'))
        ->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
});

test('halaman akun sosial menampilkan kartu Instagram, Facebook, dan Threads', function () {
    $html = $this->actingAs(socialAccountUser(['view', 'create']))
        ->get(route('admin.social-accounts.index'))
        ->assertOk()
        ->assertSee('aria-label="Instagram"', false)
        ->assertSee('aria-label="Facebook"', false)
        ->assertSee('aria-label="Threads"', false)
        ->getContent();

    // Facebook dan Threads: tombol Hubungkan nonaktif, bukan tautan
    expect(substr_count($html, 'aria-disabled="true"'))->toBe(2)
        ->and(substr_count($html, 'Segera hadir'))->toBe(2);
});

test('kartu Instagram tanpa akun menampilkan tombol Hubungkan ke OAuth', function () {
    $this->actingAs(socialAccountUser(['view', 'create']))
        ->get(route('admin.social-accounts.index'))
        ->assertSee('Belum ada akun terhubung')
        ->assertSee('href="'.route('admin.social-accounts.instagram.redirect').'"', false)
        ->assertDontSee('+ Tambah Akun');
});

test('kartu Instagram menampilkan avatar, nama, username, Edit, Putuskan, dan Tambah Akun', function () {
    $user = socialAccountUser(['view', 'create', 'edit', 'delete']);
    $account = makeSocialAccount($user, [
        'username' => 'kopikita',
        'display_name' => 'Kopi Kita Official',
        'avatar_url' => 'https://cdn.example.com/avatar.jpg',
    ]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('1 akun terhubung')
        ->assertSee('Kopi Kita Official')
        ->assertSee('@kopikita')
        ->assertSee('src="https://cdn.example.com/avatar.jpg"', false)
        // avatar CDN Instagram bisa kedaluwarsa: ada cadangan inisial
        ->assertSee('onerror=', false)
        ->assertSee('KO')
        ->assertSee('href="'.route('admin.social-accounts.edit', $account).'"', false)
        ->assertSee('action="'.route('admin.social-accounts.destroy', $account).'"', false)
        ->assertSee('+ Tambah Akun')
        ->assertDontSee('Hubungkan akun Instagram untuk mulai');
});

test('beberapa akun Instagram tampil berurutan menurut username', function () {
    $user = socialAccountUser(['view']);
    makeSocialAccount($user, ['username' => 'zeta_brand', 'provider_account_id' => 'z']);
    makeSocialAccount($user, ['username' => 'alfa_brand', 'provider_account_id' => 'a']);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('2 akun terhubung')
        ->assertSeeInOrder(['@alfa_brand', '@zeta_brand']);
});

test('akun yang koneksinya berakhir menampilkan peringatan dan tombol Hubungkan Ulang', function () {
    $user = socialAccountUser(['view', 'create']);
    makeSocialAccount($user, ['username' => 'lama', 'status' => SocialAccount::STATUS_EXPIRED]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('Koneksi berakhir')
        ->assertSee('Hubungkan Ulang')
        ->assertSee('href="'.route('admin.social-accounts.instagram.redirect').'"', false);
});

test('akun yang sudah diputus tidak tampil dan kartu kembali meminta Hubungkan', function () {
    $user = socialAccountUser(['view', 'create']);
    makeSocialAccount($user, ['username' => 'sudah_putus', 'status' => SocialAccount::STATUS_DISCONNECTED]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertDontSee('sudah_putus')
        ->assertSee('Belum ada akun terhubung');
});

test('tombol kartu mengikuti izin: tanpa izin tidak ada Hubungkan, Edit, atau Putuskan', function () {
    $user = socialAccountUser(['view']);
    $account = makeSocialAccount($user, ['username' => 'hanya_lihat']);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('@hanya_lihat')
        ->assertSee('Anda tidak punya izin untuk menghubungkan akun.')
        ->assertDontSee('+ Tambah Akun')
        ->assertDontSee(route('admin.social-accounts.edit', $account), false)
        ->assertDontSee(route('admin.social-accounts.destroy', $account), false);
});

test('developer melihat akun semua user beserta pemilik', function () {
    $developer = socialAccountUser(['view']);
    $developer->assignRole(Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']));
    $client = User::factory()->create(['name' => 'Budi Client']);
    makeSocialAccount($client, ['username' => 'milik_budi']);

    $this->actingAs($developer)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('@milik_budi')
        ->assertSee('Pemilik: Budi Client');
});

test('client tidak melihat label pemilik', function () {
    $user = socialAccountUser(['view']);
    makeSocialAccount($user, ['username' => 'punya_sendiri']);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertSee('@punya_sendiri')
        ->assertDontSee('Pemilik:');
});
