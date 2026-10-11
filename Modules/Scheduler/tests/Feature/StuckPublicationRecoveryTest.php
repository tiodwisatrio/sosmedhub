<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Modules\Scheduler\Jobs\PublishPublicationJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;

/**
 * Jadwal berstatus publishing dengan satu publikasi per status yang diminta.
 *
 * @param  array<string, string>  $publications  format => status
 */
function stuckPost(array $publications, string $postStatus = ScheduledPost::STATUS_PUBLISHING): ScheduledPost
{
    $post = ScheduledPost::factory()->create(['status' => $postStatus]);
    $post->publications()->delete();

    foreach ($publications as $format => $status) {
        $post->publications()->create(['format' => $format, 'status' => $status]);
    }

    return $post->fresh('publications');
}

function stuckAge(ScheduledPost $post, int $minutes): void
{
    DB::table('scheduled_post_publications')
        ->where('scheduled_post_id', $post->id)
        ->update(['updated_at' => now()->subMinutes($minutes)]);
}

it('menandai gagal format yang macet lalu menetapkan jadwal gagal dan mengirim email', function () {
    Notification::fake();
    $post = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHING]);
    stuckAge($post, 45);

    $this->artisan('scheduler:recover-stuck')->assertSuccessful();

    $publication = $post->publications()->first()->fresh();

    expect($publication->status)->toBe(ScheduledPostPublication::STATUS_FAILED)
        ->and($publication->error_message)->toContain('tidak selesai dalam 30 menit')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED);

    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

it('tidak menyentuh format yang masih baru atau jadwal yang tidak sedang publishing', function () {
    Notification::fake();
    $recent = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHING]);
    stuckAge($recent, 5);

    $scheduled = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PENDING], ScheduledPost::STATUS_SCHEDULED);
    stuckAge($scheduled, 120);

    $this->artisan('scheduler:recover-stuck')->assertSuccessful();

    expect($recent->publications()->first()->fresh()->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHING)
        ->and($recent->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHING)
        ->and($scheduled->publications()->first()->fresh()->status)->toBe(ScheduledPostPublication::STATUS_PENDING)
        ->and($scheduled->fresh()->status)->toBe(ScheduledPost::STATUS_SCHEDULED);

    Notification::assertNothingSent();
});

it('menjadikan jadwal partial bila sebagian format sudah terbit dan satu macet', function () {
    Notification::fake();
    $post = stuckPost([
        ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHED,
        ScheduledPost::FORMAT_STORY => ScheduledPostPublication::STATUS_PUBLISHING,
    ]);
    stuckAge($post, 60);

    $this->artisan('scheduler:recover-stuck')->assertSuccessful();

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_PARTIAL)
        ->and($post->publications()->where('format', ScheduledPost::FORMAT_FEED)->first()->fresh()->status)
        ->toBe(ScheduledPostPublication::STATUS_PUBLISHED);

    Notification::assertSentTo($post->user, PostFailedNotification::class);
    Notification::assertNotSentTo($post->user, PostPublishedNotification::class);
});

it('menghormati batas menit dari opsi', function () {
    Notification::fake();
    $post = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHING]);
    stuckAge($post, 10);

    $this->artisan('scheduler:recover-stuck', ['--minutes' => 5])->assertSuccessful();

    expect($post->publications()->first()->fresh()->isFailed())->toBeTrue();
});

it('hook failed() pada job menandai format gagal supaya jadwal tidak menggantung', function () {
    Notification::fake();
    $post = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHING]);
    $publication = $post->publications()->first();

    (new PublishPublicationJob($publication->id))->failed(new RuntimeException('kena batas waktu'));

    $publication->refresh();

    expect($publication->status)->toBe(ScheduledPostPublication::STATUS_FAILED)
        ->and($publication->error_message)->toContain('RuntimeException')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED);

    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

it('hook failed() tidak mengubah format yang sudah terbit', function () {
    Notification::fake();
    $post = stuckPost([ScheduledPost::FORMAT_FEED => ScheduledPostPublication::STATUS_PUBLISHED]);
    $publication = $post->publications()->first();

    (new PublishPublicationJob($publication->id))->failed(null);

    expect($publication->fresh()->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHED);
    Notification::assertNothingSent();
});
