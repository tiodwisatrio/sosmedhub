<?php

use App\Models\User;
use Modules\Scheduler\Models\ScheduledPost;
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
            // state Alpine yang dibutuhkan komponen
            ->assertSee('previewList.length', false)
            ->assertSee('photoCountLabel', false)
            ->assertSee('get photoCountLabel()', false)
            ->assertDontSee('mediaPreviews.length', false);
    }
});
