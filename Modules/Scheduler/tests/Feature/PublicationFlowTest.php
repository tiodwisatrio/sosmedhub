<?php

use App\Models\User;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Jobs\PublishPublicationJob;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;
use Modules\Scheduler\Services\PublicationRunner;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;

beforeEach(function () {
    Storage::fake('public');
    config([
        'social-account.instagram.status_poll_seconds' => 0,
        'social-account.instagram.status_poll_attempts' => 5,
    ]);
});

/**
 * Jadwal jatuh tempo dengan publikasi dan media per format.
 *
 * @param  array<string, list<string>>  $layout  format => daftar tipe media ('image' atau 'video')
 */
function flowPost(array $layout, bool $shareToFeed = true, string $caption = 'Caption uji'): ScheduledPost
{
    $user = User::factory()->create();
    $account = SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'instagram', 'provider_account_id' => '1789',
        'username' => 'flow', 'access_token' => 'rahasia-token', 'status' => SocialAccount::STATUS_ACTIVE,
    ]);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id, 'social_account_id' => $account->id, 'caption' => $caption,
        'scheduled_at' => now()->subMinute(), 'status' => ScheduledPost::STATUS_SCHEDULED,
    ]);
    $post->media()->delete();

    foreach ($layout as $format => $types) {
        $post->publications()->create(['format' => $format, 'share_to_feed' => $shareToFeed]);

        foreach ($types as $position => $type) {
            $path = "scheduled-posts/{$format}-{$position}".($type === 'video' ? '.mp4' : '.jpg');
            Storage::disk('public')->put($path, 'isi');
            $post->media()->create([
                'format' => $format, 'type' => $type, 'media_path' => $path,
                'mime' => $type === 'video' ? 'video/mp4' : 'image/jpeg', 'position' => $position,
            ]);
        }
    }

    return $post->fresh(['publications', 'media']);
}

/**
 * Instagram palsu. $calls mencatat pembuatan container dan penerbitan.
 *
 * @param  array<string, string|list<string>>  $statuses  container => status (atau urutan status)
 * @param  list<string>  $rejectTypes  media_type yang ditolak saat dibuat, misalnya ['REELS']
 */
function flowInstagram(array &$calls, array $statuses = [], array $rejectTypes = []): void
{
    $count = 0;

    Http::fake(function ($request) use (&$calls, &$count, &$statuses, $rejectTypes) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if ($request->method() === 'POST' && str_ends_with($path, '/1789/media')) {
            $data = $request->data();

            if (in_array($data['media_type'] ?? 'IMAGE', $rejectTypes, true)) {
                return Http::response(['error' => ['message' => 'Video ditolak Instagram']], 400);
            }

            $id = 'c'.(++$count);
            $calls[] = ['create', $data, $id];

            return Http::response(['id' => $id]);
        }

        if ($request->method() === 'GET' && preg_match('#/(c\d+)$#', $path, $m)) {
            $status = $statuses[$m[1]] ?? 'FINISHED';
            $code = is_array($status) ? (array_shift($statuses[$m[1]]) ?? 'FINISHED') : $status;
            $calls[] = ['status', $m[1], $code];

            return Http::response(['status_code' => $code, 'status' => "detail {$code}"]);
        }

        if ($request->method() === 'POST' && str_ends_with($path, '/1789/media_publish')) {
            $calls[] = ['publish', $request->data()];

            return Http::response(['id' => 'ig-'.$request->data()['creation_id']]);
        }

        return Http::response([], 404);
    });
}

/**
 * Induk carousel hanya boleh dibuat setelah Instagram melaporkan FINISHED untuk anak terakhir
 * yang sempat berstatus IN_PROGRESS.
 */
function flowCarouselCreatedAfterChildrenFinished(array $calls): bool
{
    $parent = null;
    $finishedAfterProgress = null;
    $sawProgress = false;

    foreach ($calls as $index => $call) {
        if ($call[0] === 'create' && ($call[1]['media_type'] ?? null) === 'CAROUSEL') {
            $parent = $index;
        }

        if ($call[0] === 'status' && $call[1] === 'c1') {
            $sawProgress = $sawProgress || $call[2] === 'IN_PROGRESS';

            if ($sawProgress && $call[2] === 'FINISHED' && $finishedAfterProgress === null) {
                $finishedAfterProgress = $index;
            }
        }
    }

    return $parent !== null && $finishedAfterProgress !== null && $finishedAfterProgress < $parent;
}

function flowCreates(array $calls): array
{
    return array_values(array_filter($calls, fn ($c) => $c[0] === 'create'));
}

function flowPublishes(array $calls): array
{
    return array_values(array_filter($calls, fn ($c) => $c[0] === 'publish'));
}

function flowRun(ScheduledPost $post): void
{
    (new PublishScheduledPostJob($post->id))->handle();
}

it('menerbitkan Story foto: satu container per foto, tanpa caption', function () {
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([ScheduledPost::FORMAT_STORY => ['image', 'image']]);

    flowRun($post);

    $creates = flowCreates($calls);
    expect($creates)->toHaveCount(2)
        ->and($creates[0][1]['media_type'])->toBe('STORIES')
        ->and($creates[0][1])->toHaveKey('image_url')
        ->and($creates[0][1])->not->toHaveKey('caption')
        ->and($creates[1][1])->not->toHaveKey('caption')
        ->and(flowPublishes($calls))->toHaveCount(2);

    $publication = $post->publications()->first()->fresh();
    expect($publication->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHED)
        ->and($publication->ig_media_id)->toBe('ig-c1')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('menerbitkan Story video memakai video_url', function () {
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([ScheduledPost::FORMAT_STORY => ['video']]);

    flowRun($post);

    $create = flowCreates($calls)[0][1];
    expect($create['media_type'])->toBe('STORIES')
        ->and($create)->toHaveKey('video_url')
        ->and($create)->not->toHaveKey('image_url')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('menerbitkan Reels dengan caption dan pilihan tampil di Feed', function (bool $share, string $expected) {
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']], shareToFeed: $share, caption: 'Caption reels');

    flowRun($post);

    $create = flowCreates($calls)[0][1];
    expect($create['media_type'])->toBe('REELS')
        ->and($create['caption'])->toBe('Caption reels')
        ->and($create['share_to_feed'])->toBe($expected)
        ->and($create)->toHaveKey('video_url')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
})->with([
    'tampil di Feed' => [true, 'true'],
    'hanya tab Reels' => [false, 'false'],
]);

it('membuat induk carousel Feed hanya setelah semua anaknya selesai diproses', function () {
    $calls = [];
    flowInstagram($calls, statuses: ['c1' => ['IN_PROGRESS', 'FINISHED']]);
    $post = flowPost([ScheduledPost::FORMAT_FEED => ['image', 'image', 'image']]);

    flowRun($post);

    $creates = flowCreates($calls);
    expect($creates)->toHaveCount(4)
        ->and($creates[0][1]['is_carousel_item'])->toBe('true')
        ->and($creates[2][1]['is_carousel_item'])->toBe('true')
        ->and($creates[3][1]['media_type'])->toBe('CAROUSEL')
        ->and(flowCarouselCreatedAfterChildrenFinished($calls))->toBeTrue()
        ->and($creates[3][1]['children'])->toBe('c1,c2,c3')
        ->and($creates[3][1]['caption'])->toBe('Caption uji')
        ->and(flowPublishes($calls))->toHaveCount(1)
        ->and(flowPublishes($calls)[0][1]['creation_id'])->toBe('c4')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('menerbitkan semua format yang dipilih dan mengirim satu email berhasil', function () {
    Notification::fake();
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([
        ScheduledPost::FORMAT_FEED => ['image'],
        ScheduledPost::FORMAT_STORY => ['image'],
        ScheduledPost::FORMAT_REEL => ['video'],
    ]);

    flowRun($post);

    $fresh = $post->fresh();
    expect($fresh->status)->toBe(ScheduledPost::STATUS_PUBLISHED)
        ->and($fresh->publications()->where('status', 'published')->count())->toBe(3)
        ->and($fresh->ig_media_id)->toBe('ig-c1')
        ->and($fresh->published_at)->not->toBeNull()
        ->and($fresh->error_message)->toBeNull();

    Notification::assertSentToTimes($post->user, PostPublishedNotification::class, 1);
    Notification::assertNotSentTo($post->user, PostFailedNotification::class);
});

it('menandai jadwal terbit sebagian bila satu format gagal, tanpa menggagalkan format lain', function () {
    Notification::fake();
    $calls = [];
    flowInstagram($calls, rejectTypes: ['REELS']);
    $post = flowPost([
        ScheduledPost::FORMAT_FEED => ['image'],
        ScheduledPost::FORMAT_REEL => ['video'],
    ]);

    flowRun($post);

    $fresh = $post->fresh();
    $feed = $fresh->publications()->where('format', 'feed')->first();
    $reel = $fresh->publications()->where('format', 'reel')->first();

    expect($fresh->status)->toBe(ScheduledPost::STATUS_PARTIAL)
        ->and($feed->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHED)
        ->and($reel->status)->toBe(ScheduledPostPublication::STATUS_FAILED)
        ->and($reel->error_message)->toContain('Video ditolak Instagram')
        ->and($fresh->error_message)->toContain('Reels: ')
        ->and($fresh->error_message)->not->toContain('rahasia-token')
        ->and($fresh->ig_media_id)->toBe($feed->ig_media_id)
        ->and($fresh->published_at)->not->toBeNull();

    Notification::assertSentToTimes($post->user, PostFailedNotification::class, 1);
    Notification::assertNotSentTo($post->user, PostPublishedNotification::class);
});

it('menyatakan gagal total bila semua format gagal', function () {
    Notification::fake();
    $calls = [];
    flowInstagram($calls, rejectTypes: ['REELS', 'STORIES']);
    $post = flowPost([
        ScheduledPost::FORMAT_STORY => ['image'],
        ScheduledPost::FORMAT_REEL => ['video'],
    ]);

    flowRun($post);

    $fresh = $post->fresh();
    expect($fresh->status)->toBe(ScheduledPost::STATUS_FAILED)
        ->and($fresh->published_at)->toBeNull()
        ->and($fresh->error_message)->toContain('Story: ')->toContain('Reels: ');

    Notification::assertSentToTimes($post->user, PostFailedNotification::class, 1);
});

it('mengirim satu job per format berurutan Feed, Story, Reels dan melewati yang sudah terbit', function () {
    Queue::fake();
    $post = flowPost([
        ScheduledPost::FORMAT_REEL => ['video'],
        ScheduledPost::FORMAT_FEED => ['image'],
        ScheduledPost::FORMAT_STORY => ['image'],
    ]);
    $post->publications()->where('format', 'story')->update(['status' => 'published']);

    flowRun($post);

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHING);
    Queue::assertPushedTimes(PublishPublicationJob::class, 2);

    $order = Queue::pushed(PublishPublicationJob::class)
        ->map(fn ($job) => ScheduledPostPublication::find($job->publicationId)->format)
        ->all();
    expect($order)->toBe(['feed', 'reel']);
});

it('video yang masih diproses membuat runner menunggu, lalu melanjutkan tanpa container ganda', function () {
    $calls = [];
    flowInstagram($calls, statuses: ['c1' => ['IN_PROGRESS', 'FINISHED']]);
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']]);
    $publication = $post->publications()->first();
    $post->update(['status' => ScheduledPost::STATUS_PUBLISHING]);
    $runner = app(PublicationRunner::class);

    $first = $runner->advance($publication);

    expect($first->isWaiting())->toBeTrue()
        ->and($publication->fresh()->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHING)
        ->and($publication->fresh()->state['items'][0]['container_id'])->toBe('c1')
        ->and(flowPublishes($calls))->toBe([]);

    $second = $runner->advance($publication->fresh());

    expect($second->status)->toBe('done')
        ->and(flowCreates($calls))->toHaveCount(1)
        ->and(flowPublishes($calls))->toHaveCount(1)
        ->and($publication->fresh()->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHED);
});

it('job melepas diri sesuai jeda konfigurasi saat Instagram masih memproses video', function () {
    config(['scheduler.video.poll_delay_seconds' => 45]);
    $calls = [];
    flowInstagram($calls, statuses: ['c1' => 'IN_PROGRESS']);
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']]);
    $post->update(['status' => ScheduledPost::STATUS_PUBLISHING]);

    $queueJob = Mockery::mock(Job::class);
    $queueJob->shouldReceive('release')->once()->with(45);

    $job = new PublishPublicationJob($post->publications()->first()->id);
    $job->setJob($queueJob);
    $job->handle(app(PublicationRunner::class));

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHING);
});

it('menggagalkan video yang tidak selesai diproses dalam batas waktu', function () {
    config(['scheduler.video.poll_max_minutes' => 10]);
    $calls = [];
    flowInstagram($calls, statuses: ['c1' => 'IN_PROGRESS']);
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']]);
    $publication = $post->publications()->first();
    $publication->update(['state' => [
        'started_at' => now()->subMinutes(30)->toIso8601String(),
        'items' => [['media_id' => null, 'container_id' => 'c1', 'published_id' => null]],
        'children' => [],
    ]]);

    $result = app(PublicationRunner::class)->advance($publication);

    expect($result->status)->toBe('failed')
        ->and($publication->fresh()->error_message)->toContain('belum selesai memproses');
});

it('menggagalkan container yang berstatus ERROR dengan rincian dari Instagram', function () {
    $calls = [];
    flowInstagram($calls, statuses: ['c1' => 'ERROR']);
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']]);
    $publication = $post->publications()->first();

    app(PublicationRunner::class)->advance($publication);

    expect($publication->fresh()->status)->toBe(ScheduledPostPublication::STATUS_FAILED)
        ->and($publication->fresh()->error_message)->toContain('gagal memproses media')->toContain('detail ERROR');
});

it('melanjutkan Story yang sebagian sudah terbit tanpa menerbitkan ulang item itu', function () {
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([ScheduledPost::FORMAT_STORY => ['image', 'image']]);
    $publication = $post->publications()->first();
    $media = $post->media->sortBy('position')->values();
    $publication->update(['status' => 'failed', 'state' => [
        'started_at' => now()->subMinute()->toIso8601String(),
        'items' => [
            ['media_id' => $media[0]->id, 'container_id' => 'lama-1', 'published_id' => 'ig-lama-1'],
            ['media_id' => $media[1]->id, 'container_id' => 'kedaluwarsa', 'published_id' => null],
        ],
        'children' => [],
    ]]);

    // Jadwal ulang: container lama dilepas, item yang sudah terbit dipertahankan.
    $publication->fresh()->resetForRetry();
    $reset = $publication->fresh();
    expect($reset->state['items'][0]['published_id'])->toBe('ig-lama-1')
        ->and($reset->state['items'][1]['container_id'])->toBeNull()
        ->and($reset->status)->toBe('pending');

    app(PublicationRunner::class)->advance($reset);

    expect(flowCreates($calls))->toHaveCount(1)
        ->and(flowPublishes($calls))->toHaveCount(1)
        ->and($publication->fresh()->state['items'][0]['published_id'])->toBe('ig-lama-1')
        ->and($publication->fresh()->state['items'][1]['published_id'])->toBe('ig-c1')
        ->and($publication->fresh()->ig_media_id)->toBe('ig-lama-1');
});

it('token ditolak pada satu format menandai akun terputus dan tetap menyelesaikan jadwal', function () {
    Notification::fake();
    Http::fake(['*/1789/media' => Http::response(['error' => ['message' => 'Invalid OAuth access token', 'code' => 190]], 400)]);
    $post = flowPost([ScheduledPost::FORMAT_STORY => ['image']]);

    flowRun($post);

    expect($post->socialAccount->fresh()->status)->toBe(SocialAccount::STATUS_EXPIRED)
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED);
    Notification::assertSentTo($post->user, AccountNeedsReconnectNotification::class);
    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

it('job format yang sudah selesai tidak mengirim email lagi', function () {
    Notification::fake();
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([ScheduledPost::FORMAT_FEED => ['image']]);

    flowRun($post);
    $publicationId = $post->publications()->first()->id;

    (new PublishPublicationJob($publicationId))->handle(app(PublicationRunner::class));
    (new PublishScheduledPostJob($post->id))->handle();

    Notification::assertSentToTimes($post->user, PostPublishedNotification::class, 1);
    expect(flowCreates($calls))->toHaveCount(1);
});

it('menolak menerbitkan Reels yang videonya sudah dihapus dari server', function () {
    Http::fake();
    $post = flowPost([ScheduledPost::FORMAT_REEL => ['video']]);
    $post->media()->update(['media_path' => null]);

    flowRun($post);

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED)
        ->and($post->fresh()->error_message)->toContain('Reels')->toContain('Unggah ulang medianya');
    Http::assertNothingSent();
});

it('menerbitkan Story dan carousel sesuai urutan yang disimpan', function () {
    $calls = [];
    flowInstagram($calls);
    $post = flowPost([
        ScheduledPost::FORMAT_STORY => ['image', 'image', 'image'],
        ScheduledPost::FORMAT_FEED => ['image', 'image'],
    ]);

    // Urutan dibalik: media dengan nama terakhir menjadi yang pertama.
    foreach ([ScheduledPost::FORMAT_STORY => 3, ScheduledPost::FORMAT_FEED => 2] as $format => $count) {
        $post->media()->where('format', $format)->get()->each(
            fn ($media) => $media->update(['position' => $count - 1 - $media->position])
        );
    }

    flowRun($post);

    $creates = flowCreates($calls);
    $order = collect($creates)
        ->filter(fn ($c) => ($c[1]['media_type'] ?? null) === 'STORIES')
        ->map(fn ($c) => $c[1]['image_url'])
        ->values()
        ->all();

    expect($order[0])->toContain('story-2.jpg')
        ->and($order[1])->toContain('story-1.jpg')
        ->and($order[2])->toContain('story-0.jpg');

    $children = collect($creates)->filter(fn ($c) => ($c[1]['is_carousel_item'] ?? null) === 'true')->map(fn ($c) => $c[1]['image_url'])->values();
    expect($children[0])->toContain('feed-1.jpg')->and($children[1])->toContain('feed-0.jpg');
});
