<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Jobs\PublishPublicationJob;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Services\FacebookPublicationRunner;
use Modules\Scheduler\Services\PublicationRunner;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => Storage::fake('public'));

/**
 * Jadwal Feed jatuh tempo untuk Page Facebook.
 *
 * @param  list<string>  $types  tipe media berurutan ('image' atau 'video')
 */
function fbPost(array $types = ['image'], string $caption = 'Caption Facebook'): ScheduledPost
{
    $user = User::factory()->create();
    $account = SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'facebook', 'provider_account_id' => '1010',
        'username' => 'Tio', 'display_name' => 'Tio', 'access_token' => 'token-page', 'status' => SocialAccount::STATUS_ACTIVE,
    ]);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id, 'social_account_id' => $account->id, 'caption' => $caption,
        'scheduled_at' => now()->subMinute(), 'status' => ScheduledPost::STATUS_SCHEDULED,
    ]);
    $post->media()->delete();
    $post->publications()->create(['format' => ScheduledPost::FORMAT_FEED]);

    foreach ($types as $position => $type) {
        $path = "scheduled-posts/fb-{$position}".($type === 'video' ? '.mp4' : '.jpg');
        Storage::disk('public')->put($path, 'isi');
        $post->media()->create([
            'format' => ScheduledPost::FORMAT_FEED, 'type' => $type, 'media_path' => $path,
            'mime' => $type === 'video' ? 'video/mp4' : 'image/jpeg', 'position' => $position,
        ]);
    }

    return $post->fresh(['publications', 'media']);
}

/**
 * Facebook palsu yang mencatat setiap panggilan ke /photos dan /feed.
 */
function fbFake(array &$calls, array $failPhotoAt = []): void
{
    $photos = 0;

    Http::fake(function ($request) use (&$calls, &$photos, $failPhotoAt) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if (str_ends_with($path, '/1010/photos')) {
            $photos++;

            if (in_array($photos, $failPhotoAt, true)) {
                return Http::response(['error' => ['message' => 'Foto ditolak', 'code' => 100]], 400);
            }

            $calls[] = ['photo', $request->body()];

            return Http::response(['id' => "p{$photos}", 'post_id' => "1010_post{$photos}"]);
        }

        if (str_ends_with($path, '/1010/feed')) {
            $calls[] = ['feed', $request->data()];

            return Http::response(['id' => '1010_album']);
        }

        return Http::response([], 404);
    });
}

function fbCalls(array $calls, string $type): array
{
    return array_values(array_filter($calls, fn ($c) => $c[0] === $type));
}

function fbRun(ScheduledPost $post): void
{
    (new PublishScheduledPostJob($post->id))->handle();
}

it('menerbitkan satu foto langsung ke Page lewat /photos dengan caption', function () {
    $calls = [];
    fbFake($calls);
    $post = fbPost(['image'], 'Halo Page');

    fbRun($post);

    $photos = fbCalls($calls, 'photo');
    expect($photos)->toHaveCount(1)
        ->and($photos[0][1])->toContain('Halo Page')
        ->and(fbCalls($calls, 'feed'))->toBeEmpty();

    $publication = $post->publications()->first()->fresh();
    expect($publication->status)->toBe(ScheduledPostPublication::STATUS_PUBLISHED)
        ->and($publication->ig_media_id)->toBe('1010_post1')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('mengirim token Page sebagai bearer dan foto sebagai unggahan langsung', function () {
    $calls = [];
    fbFake($calls);

    fbRun(fbPost(['image']));

    Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com/v26.0/1010/photos')
        && $request->hasHeader('Authorization', 'Bearer token-page')
        && str_contains($request->body(), 'name="source"'));
});

it('menggabungkan beberapa foto: unggah tanpa terbit, lalu satu postingan /feed', function () {
    $calls = [];
    fbFake($calls);
    $post = fbPost(['image', 'image', 'image'], 'Album');

    fbRun($post);

    $photos = fbCalls($calls, 'photo');
    $feed = fbCalls($calls, 'feed');

    expect($photos)->toHaveCount(3)
        ->and($photos[0][1])->toContain('false')
        ->and($photos[0][1])->not->toContain('Album')
        ->and($feed)->toHaveCount(1)
        ->and($feed[0][1]['message'])->toBe('Album')
        ->and($feed[0][1]['attached_media[0]'])->toBe('{"media_fbid":"p1"}')
        ->and($feed[0][1]['attached_media[2]'])->toBe('{"media_fbid":"p3"}');

    expect($post->publications()->first()->fresh()->ig_media_id)->toBe('1010_album')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('tidak mengunggah ulang foto yang sudah terunggah saat dijalankan lagi setelah gagal', function () {
    $calls = [];
    fbFake($calls, failPhotoAt: [3]);
    $post = fbPost(['image', 'image', 'image']);

    fbRun($post);

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED)
        ->and($post->publications()->first()->fresh()->state['children'][1]['container_id'])->toBe('p2')
        ->and(fbCalls($calls, 'feed'))->toBeEmpty();

    $publication = $post->publications()->first();
    $publication->update(['status' => ScheduledPostPublication::STATUS_PENDING]);
    // Lewat query: model $post masih menyimpan status lama sehingga update() tidak menulis apa pun.
    ScheduledPost::query()->whereKey($post->id)->update(['status' => ScheduledPost::STATUS_SCHEDULED]);
    // Fake yang sama tetap dipakai (kegagalan hanya pada foto ke-3); hitung panggilan sejak sekarang.
    $before = count($calls);

    fbRun($post);

    $calls = array_slice($calls, $before);

    // Hanya foto ketiga yang diunggah ulang, lalu postingan terbit.
    expect(fbCalls($calls, 'photo'))->toHaveCount(1)
        ->and(fbCalls($calls, 'feed'))->toHaveCount(1)
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_PUBLISHED);
});

it('menandai gagal tanpa menerbitkan bila Facebook menolak foto', function () {
    $calls = [];
    fbFake($calls, failPhotoAt: [1]);
    $post = fbPost(['image']);

    fbRun($post);

    $publication = $post->publications()->first()->fresh();
    expect($publication->status)->toBe(ScheduledPostPublication::STATUS_FAILED)
        ->and($publication->error_message)->toContain('Foto ditolak')
        ->and($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED);
});

it('menolak video karena Facebook baru mendukung foto', function () {
    $calls = [];
    fbFake($calls);
    $post = fbPost(['video']);

    fbRun($post);

    expect($post->publications()->first()->fresh()->error_message)->toContain('foto')
        ->and($calls)->toBeEmpty();
});

it('menandai akun kedaluwarsa dan mengirim email hubungkan ulang saat token ditolak (kode 190)', function () {
    Notification::fake();
    Http::fake(['*' => Http::response(['error' => ['message' => 'Session has expired', 'code' => 190]], 401)]);
    $post = fbPost(['image']);

    fbRun($post);

    expect($post->socialAccount->fresh()->status)->toBe(SocialAccount::STATUS_EXPIRED)
        ->and($post->publications()->first()->fresh()->error_message)->toContain('Token Facebook ditolak');
    Notification::assertSentTo($post->user, AccountNeedsReconnectNotification::class);
});

it('tidak menerbitkan dua kali bila job dijalankan ulang setelah terbit', function () {
    $calls = [];
    fbFake($calls);
    $post = fbPost(['image']);

    fbRun($post);
    (new PublishPublicationJob($post->publications()->first()->id))->handle(app(PublicationRunner::class), app(FacebookPublicationRunner::class));

    expect(fbCalls($calls, 'photo'))->toHaveCount(1);
});

function fbFormUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        ['scheduler.view', 'scheduler.create', 'scheduler.edit', 'scheduler.delete']
    ));

    return $user;
}

function fbFormAccount(User $user): SocialAccount
{
    return SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'facebook', 'provider_account_id' => 'fb-'.$user->id,
        'username' => 'Page Uji', 'display_name' => 'Page Uji', 'access_token' => 'x', 'status' => SocialAccount::STATUS_ACTIVE,
    ]);
}

it('menampilkan akun Facebook di form penjadwalan', function () {
    $user = fbFormUser();
    fbFormAccount($user);

    $this->actingAs($user)->get(route('admin.scheduled-posts.create'))
        ->assertOk()
        ->assertSee('Page Uji');
});

it('menerima jadwal Feed ke akun Facebook', function () {
    $user = fbFormUser();
    $account = fbFormAccount($user);

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), [
        'social_account_id' => $account->id,
        'caption' => 'Halo Facebook',
        'scheduled_at' => ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i'),
        'formats' => ['feed'],
        'media' => [fmtPhoto()],
    ])->assertSessionHasNoErrors();

    expect(ScheduledPost::query()->where('social_account_id', $account->id)->count())->toBe(1);
})->skip(! function_exists('fmtPhoto'), 'membutuhkan helper PostFormatsTest');

it('menolak Story dan Reels untuk akun Facebook', function () {
    $user = fbFormUser();
    $account = fbFormAccount($user);

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), [
        'social_account_id' => $account->id,
        'caption' => 'Halo',
        'scheduled_at' => ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i'),
        'formats' => ['feed', 'story'],
    ])->assertSessionHasErrors('formats');

    expect(ScheduledPost::query()->count())->toBe(0);
});

it('menerima foto rasio 9:16 untuk Facebook tetapi menolaknya untuk Instagram', function () {
    $user = fbFormUser();
    $facebook = fbFormAccount($user);
    $instagram = SocialAccount::create([
        'user_id' => $user->id, 'platform' => 'instagram', 'provider_account_id' => 'ig-'.$user->id,
        'username' => 'ig_uji', 'access_token' => 'x', 'status' => SocialAccount::STATUS_ACTIVE,
    ]);
    $payload = fn (SocialAccount $account) => [
        'social_account_id' => $account->id,
        'caption' => 'Rasio tinggi',
        'scheduled_at' => ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i'),
        'formats' => ['feed'],
        'media' => [fmtPhoto(675, 1200)],
    ];

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), $payload($instagram))
        ->assertSessionHasErrors('media.0');

    $this->actingAs($user)->post(route('admin.scheduled-posts.store'), $payload($facebook))
        ->assertSessionHasNoErrors();

    expect(ScheduledPost::query()->where('social_account_id', $facebook->id)->count())->toBe(1);
});
