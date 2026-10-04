<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SocialAccount\Models\SocialAccount;
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
