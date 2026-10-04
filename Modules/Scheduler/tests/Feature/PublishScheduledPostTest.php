<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;
use Modules\SocialAccount\Services\InstagramPublisher;

function duePost(array $attributes = []): ScheduledPost
{
    $user = User::factory()->create();
    $account = SocialAccount::firstOrCreate(['platform' => 'instagram', 'provider_account_id' => '1789'], [
        'user_id' => $user->id,
        'username' => 'tes',
        'access_token' => 'rahasia-token',
        'token_expires_at' => now()->addDays(30),
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);

    $post = ScheduledPost::create(array_merge([
        'user_id' => $user->id,
        'social_account_id' => $account->id,
        'caption' => 'Halo',
        'scheduled_at' => now()->subMinute(),
        'status' => ScheduledPost::STATUS_SCHEDULED,
    ], $attributes));

    $post->media()->create(['media_path' => 'scheduled-posts/a.jpg', 'position' => 0]);

    return $post;
}

beforeEach(fn () => config(['social-account.instagram.status_poll_seconds' => 0]));

it('mengirim hanya postingan jatuh tempo ke antrean', function () {
    Queue::fake();
    $due = duePost();
    duePost(['scheduled_at' => now()->addHour()]);

    $this->artisan('scheduler:dispatch-due')->assertSuccessful();

    Queue::assertPushed(PublishScheduledPostJob::class, 1);
    Queue::assertPushed(PublishScheduledPostJob::class, fn ($job) => $job->scheduledPostId === $due->id);
});

it('menerbitkan satu foto ke Instagram', function () {
    Http::fake([
        '*/1789/media' => Http::response(['id' => 'cont-1']),
        '*/cont-1*' => Http::response(['status_code' => 'FINISHED']),
        '*/1789/media_publish' => Http::response(['id' => 'ig-99']),
    ]);
    $post = duePost();

    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));

    $post->refresh();
    expect($post->status)->toBe(ScheduledPost::STATUS_PUBLISHED)
        ->and($post->ig_media_id)->toBe('ig-99')
        ->and($post->published_at)->not->toBeNull();
});

it('menandai gagal tanpa membocorkan token', function () {
    Http::fake(['*/1789/media' => Http::response(['error' => ['message' => 'Media tidak valid']], 400)]);
    $post = duePost();

    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));

    $post->refresh();
    expect($post->status)->toBe(ScheduledPost::STATUS_FAILED)
        ->and($post->error_message)->toContain('Media tidak valid')
        ->and($post->error_message)->not->toContain('rahasia-token');
});

it('tidak menerbitkan ulang postingan yang sudah diproses', function () {
    Http::fake();
    $post = duePost(['status' => ScheduledPost::STATUS_PUBLISHED]);

    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));

    Http::assertNothingSent();
});

it('memperpanjang token yang hampir kedaluwarsa', function () {
    Http::fake(['*/refresh_access_token*' => Http::response(['access_token' => 'baru', 'expires_in' => 5184000])]);
    $post = duePost();
    $post->socialAccount->update(['token_expires_at' => now()->addDays(3)]);

    $this->artisan('social-accounts:refresh-tokens')->assertSuccessful();

    $account = $post->socialAccount->refresh();
    expect($account->access_token)->toBe('baru')
        ->and($account->token_expires_at->isAfter(now()->addDays(50)))->toBeTrue();
});

it('mengirim email saat post gagal terbit', function () {
    Notification::fake();
    Http::fake(['*/1789/media' => Http::response(['error' => ['message' => 'Media tidak valid', 'code' => 100]], 400)]);
    $post = duePost();

    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));

    Notification::assertSentTo($post->user, PostFailedNotification::class);
    Notification::assertNotSentTo($post->user, AccountNeedsReconnectNotification::class);
});

it('mengirim email saat post berhasil terbit dan bisa dimatikan lewat config', function () {
    Notification::fake();
    Http::fake([
        '*/1789/media' => Http::response(['id' => 'cont-1']),
        '*/cont-1*' => Http::response(['status_code' => 'FINISHED']),
        '*/1789/media_publish' => Http::response(['id' => 'ig-99']),
    ]);

    $post = duePost();
    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));
    Notification::assertSentTo($post->user, PostPublishedNotification::class);

    config(['scheduler.notify_published' => false]);
    Notification::fake();
    $quiet = duePost();
    (new PublishScheduledPostJob($quiet->id))->handle(app(InstagramPublisher::class));
    Notification::assertNothingSent();
});

it('menandai akun terputus dan memberi tahu pemilik saat token ditolak Instagram', function () {
    Notification::fake();
    Http::fake(['*/1789/media' => Http::response(['error' => ['message' => 'Invalid OAuth access token', 'code' => 190]], 400)]);
    $post = duePost();

    (new PublishScheduledPostJob($post->id))->handle(app(InstagramPublisher::class));

    expect($post->socialAccount->refresh()->status)->toBe(SocialAccount::STATUS_EXPIRED)
        ->and($post->refresh()->status)->toBe(ScheduledPost::STATUS_FAILED);
    Notification::assertSentTo($post->user, AccountNeedsReconnectNotification::class, fn ($n) => $n->expired);
    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

it('memperingatkan pemilik bila refresh token gagal dan hampir kedaluwarsa', function () {
    Notification::fake();
    Http::fake(['*/refresh_access_token*' => Http::response(['error' => ['message' => 'gagal']], 400)]);
    $post = duePost();
    $post->socialAccount->update(['token_expires_at' => now()->addDays(2)]);

    $this->artisan('social-accounts:refresh-tokens')->assertSuccessful();

    Notification::assertSentTo($post->user, AccountNeedsReconnectNotification::class, fn ($n) => ! $n->expired);
    expect($post->socialAccount->refresh()->status)->toBe(SocialAccount::STATUS_ACTIVE);
});

it('tidak mengirim peringatan bila token masih lama berlakunya', function () {
    Notification::fake();
    Http::fake(['*/refresh_access_token*' => Http::response(['error' => ['message' => 'gagal']], 400)]);
    $post = duePost();
    $post->socialAccount->update(['token_expires_at' => now()->addDays(8)]);

    $this->artisan('social-accounts:refresh-tokens')->assertSuccessful();

    Notification::assertNothingSent();
});

it('menandai akun kedaluwarsa dan memberi tahu bila refresh gagal setelah token habis', function () {
    Notification::fake();
    Http::fake(['*/refresh_access_token*' => Http::response(['error' => ['message' => 'gagal']], 400)]);
    $post = duePost();
    $post->socialAccount->update(['token_expires_at' => now()->subHour()]);

    $this->artisan('social-accounts:refresh-tokens')->assertSuccessful();

    expect($post->socialAccount->refresh()->status)->toBe(SocialAccount::STATUS_EXPIRED);
    Notification::assertSentTo($post->user, AccountNeedsReconnectNotification::class, fn ($n) => $n->expired);
});

it('merender isi email notifikasi dengan benar', function () {
    $post = duePost(['status' => ScheduledPost::STATUS_FAILED, 'error_message' => 'Media tidak valid']);
    $post->load('socialAccount', 'user');
    $account = $post->socialAccount;
    $account->token_expires_at = now()->addDays(2);

    $failed = (new PostFailedNotification($post))->toMail($post->user);
    expect($failed->subject)->toBe('Postingan Instagram gagal terbit')
        ->and($failed->actionUrl)->toBe(route('admin.scheduled-posts.edit', $post))
        ->and(implode(' ', $failed->introLines))->toContain('Media tidak valid');

    $published = (new PostPublishedNotification($post))->toMail($post->user);
    expect($published->subject)->toBe('Postingan Instagram berhasil terbit')
        ->and(implode(' ', $published->introLines))->toContain('@tes');

    $warning = (new AccountNeedsReconnectNotification($account, expired: false))->toMail($post->user);
    $expired = (new AccountNeedsReconnectNotification($account, expired: true))->toMail($post->user);
    expect($warning->subject)->toContain('akan berakhir')
        ->and($expired->subject)->toContain('terputus')
        ->and($expired->actionUrl)->toBe(route('admin.social-accounts.index'));
});
