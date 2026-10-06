<?php

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;

function facebookUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(
        Permission::firstOrCreate(['name' => 'social-account.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'social-account.create', 'guard_name' => 'web']),
    );

    return $user;
}

function facebookPage(string $id, string $name, array $overrides = []): array
{
    return array_replace([
        'id' => $id,
        'name' => $name,
        'access_token' => 'page-token-'.$id,
        'tasks' => ['ANALYZE', 'CREATE_CONTENT', 'MANAGE'],
        'picture' => ['data' => ['url' => 'https://example.test/'.$id.'.jpg']],
    ], $overrides);
}

function fakeFacebook(array $pages, ?array $permissions = null): void
{
    $permissions ??= ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts', 'business_management', 'public_profile'];

    Http::fake([
        'graph.facebook.com/*/oauth/access_token' => Http::response(['access_token' => 'user-token']),
        'graph.facebook.com/*/me/permissions' => Http::response([
            'data' => array_map(fn ($permission) => ['permission' => $permission, 'status' => 'granted'], $permissions),
        ]),
        'graph.facebook.com/*/me/accounts*' => Http::response(['data' => $pages]),
    ]);
}

function facebookCallback(User $user, array $query = [])
{
    $state = Crypt::encryptString(json_encode(['user_id' => $user->id]));

    return test()->withSession(['facebook_oauth_state' => $state])
        ->actingAs($user)
        ->get(route('admin.social-accounts.facebook.callback', array_replace(['state' => $state, 'code' => 'abc'], $query)));
}

beforeEach(function () {
    config([
        'social-account.facebook.client_id' => 'fb-client-id',
        'social-account.facebook.client_secret' => 'fb-secret',
        'social-account.facebook.redirect_uri' => 'http://localhost/facebook/callback',
    ]);
});

test('redirect OAuth Facebook mengarahkan ke dialog Facebook dengan scope Halaman', function () {
    $response = $this->actingAs(facebookUser())->get(route('admin.social-accounts.facebook.redirect'));

    $location = $response->headers->get('Location');
    parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

    $response->assertRedirect();
    expect(parse_url($location, PHP_URL_HOST))->toBe('www.facebook.com')
        ->and($location)->toContain('/dialog/oauth')
        ->and($query['client_id'])->toBe('fb-client-id')
        ->and($query['scope'])->toBe('pages_show_list,pages_read_engagement,pages_manage_posts,business_management');
});

test('redirect OAuth Facebook memberi pesan bila belum dikonfigurasi', function () {
    config(['social-account.facebook.client_id' => null]);

    $this->actingAs(facebookUser())
        ->get(route('admin.social-accounts.facebook.redirect'))
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('error');
});

test('client tidak bisa menentukan pemilik akun lewat owner_id', function () {
    $user = facebookUser();
    $other = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.social-accounts.facebook.redirect', ['owner_id' => $other->id]));

    parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    expect(json_decode(Crypt::decryptString($query['state']), true)['user_id'])->toBe($user->id);
});

test('callback dengan satu Halaman langsung menghubungkannya dengan token terenkripsi', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi', ['username' => 'tokokopi'])]);

    facebookCallback($user)
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('success');

    $account = SocialAccount::firstWhere('provider_account_id', '111');

    expect($account->platform)->toBe(SocialAccount::PLATFORM_FACEBOOK)
        ->and($account->user_id)->toBe($user->id)
        ->and($account->display_name)->toBe('Toko Kopi')
        ->and($account->username)->toBe('tokokopi')
        ->and($account->access_token)->toBe('page-token-111')
        ->and($account->token_expires_at)->toBeNull()
        ->and($account->isActive())->toBeTrue()
        ->and($account->getRawOriginal('access_token'))->not->toContain('page-token-111');
});

test('Halaman tanpa username memakai namanya', function () {
    fakeFacebook([facebookPage('111', 'Toko Kopi')]);

    facebookCallback(facebookUser());

    expect(SocialAccount::firstWhere('provider_account_id', '111')->username)->toBe('Toko Kopi');
});

test('callback dengan beberapa Halaman menampilkan pilihan dan belum menyimpan apa pun', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi'), facebookPage('222', 'Toko Teh')]);

    facebookCallback($user)->assertRedirect(route('admin.social-accounts.facebook.pages'));

    expect(SocialAccount::count())->toBe(0);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.facebook.pages'))
        ->assertOk()
        ->assertSee('Toko Kopi')
        ->assertSee('Toko Teh')
        ->assertDontSee('page-token-111');
});

test('hanya Halaman yang dipilih yang dihubungkan', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi'), facebookPage('222', 'Toko Teh')]);

    facebookCallback($user);

    $this->actingAs($user)
        ->post(route('admin.social-accounts.facebook.connect'), ['pages' => ['222']])
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('success');

    expect(SocialAccount::pluck('provider_account_id')->all())->toBe(['222'])
        ->and(SocialAccount::first()->user_id)->toBe($user->id);
});

test('Halaman yang bukan milik pengguna tidak bisa dipilih', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi'), facebookPage('222', 'Toko Teh')]);

    facebookCallback($user);

    $this->actingAs($user)
        ->post(route('admin.social-accounts.facebook.connect'), ['pages' => ['999']])
        ->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
});

test('memilih Halaman wajib minimal satu', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi'), facebookPage('222', 'Toko Teh')]);

    facebookCallback($user);

    $this->actingAs($user)
        ->post(route('admin.social-accounts.facebook.connect'), ['pages' => []])
        ->assertSessionHasErrors('pages');
});

test('pilihan Halaman tanpa sesi koneksi ditolak', function () {
    $user = facebookUser();

    $this->actingAs($user)
        ->get(route('admin.social-accounts.facebook.pages'))
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->post(route('admin.social-accounts.facebook.connect'), ['pages' => ['111']])
        ->assertSessionHas('error');
});

test('Halaman tanpa hak membuat konten tidak ditawarkan', function () {
    fakeFacebook([facebookPage('111', 'Hanya Analis', ['tasks' => ['ANALYZE']])]);

    facebookCallback(facebookUser())->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
});

test('izin yang ditolak pengguna membatalkan koneksi', function () {
    fakeFacebook([facebookPage('111', 'Toko Kopi')], ['pages_show_list', 'pages_read_engagement']);

    facebookCallback(facebookUser())
        ->assertRedirect(route('admin.social-accounts.index'))
        ->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
});

test('state yang tidak cocok ditolak', function () {
    $user = facebookUser();
    fakeFacebook([facebookPage('111', 'Toko Kopi')]);

    $this->withSession(['facebook_oauth_state' => 'lain'])
        ->actingAs($user)
        ->get(route('admin.social-accounts.facebook.callback', ['state' => 'palsu', 'code' => 'abc']))
        ->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
    Http::assertNothingSent();
});

test('pengguna yang menolak dialog Facebook mendapat pesan', function () {
    facebookCallback(facebookUser(), ['error' => 'access_denied', 'code' => null])
        ->assertSessionHas('error');

    expect(SocialAccount::count())->toBe(0);
});

test('kegagalan API Facebook tidak membocorkan token ke pesan error', function () {
    Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid code']], 400)]);

    $response = facebookCallback(facebookUser());

    $response->assertSessionHas('error');
    expect(session('error'))->not->toContain('fb-secret');
});

test('akun Facebook tampil di kartu Facebook dan bukan di kartu Instagram', function () {
    $user = facebookUser();

    SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_FACEBOOK,
        'provider_account_id' => '111',
        'username' => 'Toko Kopi',
        'display_name' => 'Toko Kopi',
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)
        ->get(route('admin.social-accounts.index'))
        ->assertOk()
        ->assertSee('Toko Kopi')
        ->assertSee('ID 111')
        ->assertDontSee('@Toko Kopi')
        ->assertSee(route('admin.social-accounts.facebook.redirect'), false);
});

test('akun Facebook tidak ditawarkan di form penjadwalan sebelum publikasinya ada', function () {
    $user = facebookUser();
    $user->givePermissionTo(
        Permission::firstOrCreate(['name' => 'scheduler.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'scheduler.create', 'guard_name' => 'web']),
    );

    SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_FACEBOOK,
        'provider_account_id' => '111',
        'username' => 'Halaman Rahasia',
        'display_name' => 'Halaman Rahasia',
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertDontSee('Halaman Rahasia');
});
