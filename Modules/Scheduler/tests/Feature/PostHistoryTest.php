<?php

use App\Models\User;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function historyUser(array $permissions = ['scheduler.view']): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(
        array_map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']), $permissions)
    );

    return $user;
}

function historyAccount(User $user, string $username): SocialAccount
{
    return SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'provider_account_id' => 'ig-'.$username,
        'username' => $username,
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);
}

function historyPost(User $user, array $attributes = []): ScheduledPost
{
    return ScheduledPost::factory()->create(array_merge([
        'user_id' => $user->id,
        'status' => ScheduledPost::STATUS_PUBLISHED,
        'scheduled_at' => now()->subDay(),
    ], $attributes));
}

test('halaman riwayat menolak user tanpa izin melihat penjadwalan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.post-history.index'))
        ->assertForbidden();
});

test('riwayat hanya menampilkan postingan milik user dan yang sudah lewat antrean', function () {
    $user = historyUser();
    $other = historyUser();

    historyPost($user, ['caption' => 'Sudah terbit milik saya']);
    historyPost($user, ['caption' => 'Gagal milik saya', 'status' => ScheduledPost::STATUS_FAILED]);
    historyPost($user, [
        'caption' => 'Masih menunggu giliran',
        'status' => ScheduledPost::STATUS_SCHEDULED,
        'scheduled_at' => now()->addDay(),
    ]);
    historyPost($other, ['caption' => 'Milik orang lain']);

    $this->actingAs($user)
        ->get(route('admin.post-history.index'))
        ->assertOk()
        ->assertSee('Sudah terbit milik saya')
        ->assertSee('Gagal milik saya')
        ->assertDontSee('Masih menunggu giliran')
        ->assertDontSee('Milik orang lain');
});

test('filter status menyaring daftar dan jumlah tab tetap lengkap', function () {
    $user = historyUser();

    historyPost($user, ['caption' => 'Post terbit A']);
    historyPost($user, ['caption' => 'Post terbit B']);
    historyPost($user, ['caption' => 'Post gagal C', 'status' => ScheduledPost::STATUS_FAILED]);
    historyPost($user, ['caption' => 'Post batal D', 'status' => ScheduledPost::STATUS_CANCELLED]);

    $response = $this->actingAs($user)
        ->get(route('admin.post-history.index', ['status' => 'failed']))
        ->assertOk()
        ->assertSee('Post gagal C')
        ->assertDontSee('Post terbit A')
        ->assertDontSee('Post batal D');

    expect($response->viewData('counts')->all())->toMatchArray([
        '' => 4,
        ScheduledPost::STATUS_PUBLISHED => 2,
        ScheduledPost::STATUS_FAILED => 1,
        ScheduledPost::STATUS_CANCELLED => 1,
        ScheduledPost::STATUS_DRAFT => 0,
    ]);
});

test('status yang tidak dikenal diabaikan', function () {
    $user = historyUser();
    historyPost($user, ['caption' => 'Tetap tampil']);

    $this->actingAs($user)
        ->get(route('admin.post-history.index', ['status' => 'bukan-status']))
        ->assertOk()
        ->assertSee('Tetap tampil');
});

test('filter akun dan pencarian caption bekerja bersama', function () {
    $user = historyUser();
    $brandA = historyAccount($user, 'brand_a');
    $brandB = historyAccount($user, 'brand_b');

    historyPost($user, ['caption' => 'Promo kopi', 'social_account_id' => $brandA->id]);
    historyPost($user, ['caption' => 'Promo teh', 'social_account_id' => $brandB->id]);
    historyPost($user, ['caption' => 'Menu baru', 'social_account_id' => $brandA->id]);

    $this->actingAs($user)
        ->get(route('admin.post-history.index', ['account' => $brandA->id]))
        ->assertSee('Promo kopi')
        ->assertSee('Menu baru')
        ->assertDontSee('Promo teh');

    $this->actingAs($user)
        ->get(route('admin.post-history.index', ['account' => $brandA->id, 'q' => 'promo']))
        ->assertSee('Promo kopi')
        ->assertDontSee('Menu baru')
        ->assertDontSee('Promo teh');
});

test('pencarian memperlakukan tanda persen sebagai teks biasa', function () {
    $user = historyUser();
    historyPost($user, ['caption' => 'Diskon 50% hari ini']);
    historyPost($user, ['caption' => 'Tanpa angka sama sekali']);

    $this->actingAs($user)
        ->get(route('admin.post-history.index', ['q' => '%']))
        ->assertSee('Diskon 50% hari ini')
        ->assertDontSee('Tanpa angka sama sekali');
});

test('baris riwayat menampilkan username akun dan tombol sesuai status', function () {
    $user = historyUser(['scheduler.view', 'scheduler.edit', 'scheduler.create']);
    $account = historyAccount($user, 'brand_a');

    historyPost($user, [
        'caption' => 'Gagal terbit',
        'status' => ScheduledPost::STATUS_FAILED,
        'social_account_id' => $account->id,
        'error_message' => 'Media tidak valid',
    ]);

    $this->actingAs($user)
        ->get(route('admin.post-history.index'))
        ->assertSee('@brand_a')
        ->assertSee('Media tidak valid')
        ->assertSee('Jadwalkan Ulang')
        ->assertSee('Duplikat');
});

test('halaman penjadwalan menampilkan ringkasan gagal dan tautan ke riwayat', function () {
    $user = historyUser();
    historyPost($user, ['status' => ScheduledPost::STATUS_FAILED]);
    historyPost($user, ['status' => ScheduledPost::STATUS_FAILED]);

    $this->actingAs($user)
        ->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->assertSee('postingan gagal terbit')
        ->assertSee(route('admin.post-history.index', ['status' => 'failed']), false);
});

test('developer melihat riwayat semua user', function () {
    Permission::firstOrCreate(['name' => 'scheduler.view', 'guard_name' => 'web']);
    $developer = historyUser();
    $developer->assignRole(Role::firstOrCreate(['name' => 'developer', 'guard_name' => 'web']));
    $client = historyUser();
    historyPost($client, ['caption' => 'Milik client']);

    $this->actingAs($developer)
        ->get(route('admin.post-history.index'))
        ->assertSee('Milik client');
});
