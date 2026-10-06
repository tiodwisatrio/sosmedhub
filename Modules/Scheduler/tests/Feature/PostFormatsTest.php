<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\SocialAccount\Models\SocialAccount;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => Storage::fake('public'));

function fmtUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        ['scheduler.view', 'scheduler.create', 'scheduler.edit', 'scheduler.delete']
    ));

    return $user;
}

function fmtAccount(User $user): SocialAccount
{
    return SocialAccount::firstOrCreate(
        ['platform' => 'instagram', 'provider_account_id' => 'fmt-'.$user->id],
        ['user_id' => $user->id, 'username' => 'fmt_'.$user->id, 'status' => SocialAccount::STATUS_ACTIVE]
    );
}

function fmtSlot(): string
{
    return ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i');
}

function fmtPhoto(int $width = 1080, int $height = 1350, string $name = 'foto.jpg'): UploadedFile
{
    $image = new Imagick;
    $image->newPseudoImage($width, $height, 'gradient:#334455-#ddaa66');
    $image->setImageFormat('jpeg');
    $path = tempnam(sys_get_temp_dir(), 'img').'.jpg';
    $image->writeImage($path);

    return new UploadedFile($path, $name, 'image/jpeg', null, true);
}

function fmtVideo(float $seconds = 20, array $options = [], string $name = 'video.mp4'): UploadedFile
{
    $path = mp4File(fakeMp4(...array_merge(['seconds' => $seconds], $options)));

    return new UploadedFile($path, $name, 'video/mp4', null, true);
}

function fmtPost(User $user, array $payload, ?SocialAccount $account = null)
{
    $account ??= fmtAccount($user);

    return test()->actingAs($user)->post(route('admin.scheduled-posts.store'), array_replace([
        'caption' => 'Caption uji',
        'social_account_id' => $account->id,
        'scheduled_at' => fmtSlot(),
    ], $payload));
}

// ---------------------------------------------------------------------------------------------
// Menyimpan jadwal dengan beberapa format
// ---------------------------------------------------------------------------------------------

it('menyimpan Feed, Story, dan Reels sekaligus dengan media per format', function () {
    $user = fmtUser();

    fmtPost($user, [
        'formats' => ['feed', 'story', 'reel'],
        'share_to_feed' => 0,
        'media' => [fmtPhoto(), fmtPhoto(name: 'dua.jpg')],
        'media_story' => [fmtPhoto(1080, 1920), fmtVideo(30)],
        'media_reel' => [fmtVideo(45)],
    ])->assertSessionHasNoErrors();

    $post = ScheduledPost::with(['publications', 'media'])->firstOrFail();

    expect($post->status)->toBe(ScheduledPost::STATUS_SCHEDULED)
        ->and($post->formats())->toBe(['feed', 'story', 'reel'])
        ->and($post->publications->pluck('status')->unique()->all())->toBe(['pending'])
        ->and($post->publications->firstWhere('format', 'reel')->share_to_feed)->toBeFalse()
        ->and($post->mediaFor('feed'))->toHaveCount(2)
        ->and($post->mediaFor('story'))->toHaveCount(2)
        ->and($post->mediaFor('reel'))->toHaveCount(1);

    $story = $post->mediaFor('story');
    expect($story[0]->type)->toBe('image')
        ->and($story[0]->thumbnail_path)->not->toBeNull()
        ->and($story[1]->type)->toBe('video')
        ->and($story[1]->duration_ms)->toBe(30000)
        ->and($story[1]->width)->toBe(1080)
        ->and($story[1]->height)->toBe(1920)
        ->and($story[1]->mime)->toBe('video/mp4')
        ->and($story[1]->thumbnail_path)->toBeNull()
        ->and(Storage::disk('public')->exists($story[1]->media_path))->toBeTrue()
        ->and($story[1]->media_path)->toStartWith('scheduled-posts/videos/');

    // posisi dihitung per format
    expect($post->mediaFor('feed')->pluck('position')->all())->toBe([0, 1])
        ->and($post->mediaFor('story')->pluck('position')->all())->toBe([0, 1])
        ->and($post->mediaFor('reel')->pluck('position')->all())->toBe([0]);
});

it('tanpa pilihan format dianggap Feed saja agar form lama tetap berjalan', function () {
    $user = fmtUser();

    fmtPost($user, ['media' => [fmtPhoto()]])->assertSessionHasNoErrors();

    $post = ScheduledPost::firstOrFail();
    expect($post->formats())->toBe(['feed'])
        ->and($post->mediaFor('feed'))->toHaveCount(1);
});

it('Story saja tidak membutuhkan caption dan menyimpannya kosong', function () {
    $user = fmtUser();

    fmtPost($user, ['formats' => ['story'], 'caption' => '', 'media_story' => [fmtPhoto(1080, 1920)]])
        ->assertSessionHasNoErrors();

    expect(ScheduledPost::firstOrFail()->caption)->toBe('');
});

it('Feed mewajibkan caption, Reels tidak', function () {
    $user = fmtUser();

    fmtPost($user, ['formats' => ['feed'], 'caption' => '', 'media' => [fmtPhoto()]])
        ->assertSessionHasErrors('caption');

    fmtPost($user, ['formats' => ['reel'], 'caption' => '', 'media_reel' => [fmtVideo()]])
        ->assertSessionHasNoErrors();
});

it('menerima foto Story berbagai rasio tetapi menolak rasio itu di Feed', function () {
    $user = fmtUser();

    fmtPost($user, ['formats' => ['story'], 'media_story' => [fmtPhoto(1080, 1920)]])->assertSessionHasNoErrors();
    expect(ScheduledPost::count())->toBe(1);

    fmtPost($user, ['formats' => ['feed'], 'media' => [fmtPhoto(1080, 1920)]])
        ->assertSessionHasErrors('media.0');
    expect(ScheduledPost::count())->toBe(1);
});

// ---------------------------------------------------------------------------------------------
// Validasi
// ---------------------------------------------------------------------------------------------

it('menolak format yang dipilih tetapi tidak ada medianya', function (array $payload, string $field, string $message) {
    fmtPost(fmtUser(), $payload)->assertSessionHasErrors([$field => $message]);
    expect(ScheduledPost::count())->toBe(0);
})->with([
    'Feed tanpa foto' => [['formats' => ['feed']], 'media', 'Feed membutuhkan minimal 1 foto.'],
    'Story tanpa media' => [['formats' => ['story']], 'media_story', 'Story membutuhkan minimal 1 foto atau video.'],
    'Reels tanpa video' => [['formats' => ['reel']], 'media_reel', 'Reels membutuhkan minimal 1 video.'],
]);

it('memeriksa jenis file tiap format', function (string $field, string $format, string $kind, string $message) {
    $file = $kind === 'video' ? fmtVideo() : fmtPhoto();

    fmtPost(fmtUser(), ['formats' => [$format], $field => [$file]])
        ->assertSessionHasErrors(["{$field}.0" => $message]);
})->with([
    'Feed menolak video' => ['media', 'feed', 'video', 'Feed hanya menerima foto JPEG (.jpg atau .jpeg).'],
    'Reels menolak foto' => ['media_reel', 'reel', 'photo', 'Reels hanya menerima video MP4 atau MOV.'],
]);

it('menolak video yang melanggar syarat Story dan Reels dengan pesan yang jelas', function (string $field, string $format, float $seconds, array $options, string $message) {
    fmtPost(fmtUser(), ['formats' => [$format], $field => [fmtVideo($seconds, $options)]])
        ->assertSessionHasErrors(["{$field}.0" => $message]);

    expect(ScheduledPost::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
})->with([
    'Story lebih dari 60 detik' => ['media_story', 'story', 61, [], 'Durasi video 1 menit 1 detik melebihi batas 1 menit untuk Story.'],
    'Reels lebih dari 3 menit' => ['media_reel', 'reel', 181, [], 'Durasi video 3 menit 1 detik melebihi batas 3 menit untuk Reels.'],
    'video terlalu pendek' => ['media_reel', 'reel', 2, [], 'Video terlalu pendek (2 detik). Minimal 3 detik.'],
    'codec video tidak didukung' => ['media_reel', 'reel', 20, ['videoCodec' => 'vp09'], 'Codec video "vp09" tidak didukung Instagram. Ekspor ulang sebagai MP4 dengan H.264 (atau HEVC) dan audio AAC.'],
    'codec audio tidak didukung' => ['media_reel', 'reel', 20, ['audioCodec' => 'ac-3'], 'Codec audio "ac-3" tidak didukung Instagram. Gunakan audio AAC.'],
    'frame rate terlalu rendah' => ['media_story', 'story', 20, ['fps' => 10], 'Frame rate 10 fps di luar batas 23-60 fps yang diterima Instagram.'],
]);

it('batas durasi Reels bisa diubah lewat konfigurasi dan Story tetap 60 detik', function () {
    config(['scheduler.video.reel_max_seconds' => 900]);

    fmtPost(fmtUser(), ['formats' => ['reel'], 'media_reel' => [fmtVideo(600)]])->assertSessionHasNoErrors();
    expect(ScheduledPost::count())->toBe(1);

    fmtPost(fmtUser(), ['formats' => ['story'], 'media_story' => [fmtVideo(61)]])->assertSessionHasErrors('media_story.0');
});

it('menerima video H.264 dengan audio AAC dan video HEVC tanpa audio', function (array $options) {
    fmtPost(fmtUser(), ['formats' => ['reel'], 'media_reel' => [fmtVideo(20, $options)]])->assertSessionHasNoErrors();
})->with([
    'H.264 + AAC' => [[]],
    'HEVC tanpa audio' => [['videoCodec' => 'hvc1', 'audioCodec' => null]],
    'moov di belakang' => [['faststart' => false]],
    'video rotasi iPhone' => [['width' => 1920, 'height' => 1080, 'rotate' => true]],
]);

it('menolak file video rusak dan file yang bukan video', function () {
    $broken = tempnam(sys_get_temp_dir(), 'vid').'.mp4';
    file_put_contents($broken, 'bukan video');

    fmtPost(fmtUser(), ['formats' => ['reel'], 'media_reel' => [new UploadedFile($broken, 'rusak.mp4', 'video/mp4', null, true)]])
        ->assertSessionHasErrors('media_reel.0');
    expect(ScheduledPost::count())->toBe(0);
});

it('membatasi jumlah media: Reels satu video, Story dan Feed maksimal sepuluh', function () {
    $user = fmtUser();

    fmtPost($user, ['formats' => ['reel'], 'media_reel' => [fmtVideo(), fmtVideo()]])
        ->assertSessionHasErrors('media_reel');

    fmtPost($user, ['formats' => ['story'], 'media_story' => array_map(fn () => fmtPhoto(540, 960), range(1, 11))])
        ->assertSessionHasErrors('media_story');

    expect(ScheduledPost::count())->toBe(0);
});

it('menolak nilai format yang tidak dikenal', function () {
    fmtPost(fmtUser(), ['formats' => ['feed', 'carousel'], 'media' => [fmtPhoto()]])
        ->assertSessionHasErrors('formats.1');
});

it('mengabaikan unggahan format yang tidak dipilih', function () {
    $user = fmtUser();

    fmtPost($user, [
        'formats' => ['feed'],
        'media' => [fmtPhoto()],
        'media_story' => [fmtPhoto()],
        'media_reel' => [fmtVideo()],
    ])->assertSessionHasErrors(['media_story.0', 'media_reel.0']);

    expect(ScheduledPost::count())->toBe(0);
});

// ---------------------------------------------------------------------------------------------
// Mengubah jadwal
// ---------------------------------------------------------------------------------------------

function fmtExisting(User $user, array $publications = [['feed', 'pending']], ?SocialAccount $account = null): ScheduledPost
{
    $account ??= fmtAccount($user);
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id, 'social_account_id' => $account->id, 'caption' => 'Lama',
        'status' => ScheduledPost::STATUS_SCHEDULED, 'scheduled_at' => now()->addDay(),
    ]);
    $post->media()->delete();

    foreach ($publications as [$format, $status]) {
        $post->publications()->create(['format' => $format, 'status' => $status, 'published_at' => $status === 'published' ? now() : null]);
        $path = $format === 'reel' ? "scheduled-posts/videos/{$format}.mp4" : "scheduled-posts/{$format}.jpg";
        Storage::disk('public')->put($path, 'isi');
        $post->media()->create([
            'format' => $format, 'type' => $format === 'reel' ? 'video' : 'image',
            'media_path' => $path, 'position' => 0,
        ]);
    }

    return $post->fresh(['publications', 'media']);
}

function fmtUpdate(User $user, ScheduledPost $post, array $payload)
{
    return test()->actingAs($user)->put(route('admin.scheduled-posts.update', $post), array_replace([
        'caption' => 'Baru',
        'social_account_id' => $post->social_account_id,
        'scheduled_at' => fmtSlot(),
    ], $payload));
}

it('menambah format Story pada jadwal yang sudah ada', function () {
    $user = fmtUser();
    $post = fmtExisting($user);

    fmtUpdate($user, $post, ['formats' => ['feed', 'story'], 'media_story' => [fmtPhoto(1080, 1920)]])
        ->assertSessionHasNoErrors();

    $fresh = $post->fresh(['publications', 'media']);
    expect($fresh->formats())->toBe(['feed', 'story'])
        ->and($fresh->mediaFor('story'))->toHaveCount(1)
        ->and($fresh->mediaFor('feed'))->toHaveCount(1)
        ->and($fresh->caption)->toBe('Baru');
});

it('menghapus format yang tidak lagi dipilih beserta file medianya', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'pending'], ['reel', 'pending']]);
    $reelPath = $post->mediaFor('reel')->first()->media_path;

    fmtUpdate($user, $post, ['formats' => ['feed']])->assertSessionHasNoErrors();

    $fresh = $post->fresh(['publications', 'media']);
    expect($fresh->formats())->toBe(['feed'])
        ->and($fresh->media)->toHaveCount(1)
        ->and(Storage::disk('public')->exists($reelPath))->toBeFalse();
});

it('format yang sudah terbit terkunci: media dan formatnya tidak bisa diubah', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'published'], ['reel', 'failed']]);
    $feedMedia = $post->mediaFor('feed')->first();
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL]);

    // mencoba mencabut Feed, menghapus mediannya, dan mengunggah foto baru ke Feed
    fmtUpdate($user, $post, [
        'formats' => ['reel'],
        'remove_media' => [$feedMedia->id],
        'media' => [fmtPhoto()],
    ])->assertSessionHasErrors('media.0');

    fmtUpdate($user, $post, ['formats' => ['reel'], 'remove_media' => [$feedMedia->id]])->assertSessionHasNoErrors();

    $fresh = $post->fresh(['publications', 'media']);
    expect($fresh->formats())->toBe(['feed', 'reel'])
        ->and($fresh->mediaFor('feed'))->toHaveCount(1)
        ->and(Storage::disk('public')->exists($feedMedia->media_path))->toBeTrue()
        ->and($fresh->publications->firstWhere('format', 'feed')->status)->toBe('published');
});

it('menyimpan jadwal gagal atau sebagian mengulang hanya format yang gagal', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'published'], ['reel', 'failed']]);
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL, 'error_message' => 'Reels: ditolak']);
    $post->publications()->where('format', 'reel')->update([
        'error_message' => 'ditolak',
        'state' => ['started_at' => now()->toIso8601String(), 'items' => [['media_id' => null, 'container_id' => 'lama', 'published_id' => null]], 'children' => []],
    ]);

    fmtUpdate($user, $post, ['formats' => ['feed', 'reel']])->assertSessionHasNoErrors();

    $fresh = $post->fresh(['publications']);
    $reel = $fresh->publications->firstWhere('format', 'reel');
    expect($fresh->status)->toBe(ScheduledPost::STATUS_SCHEDULED)
        ->and($fresh->error_message)->toBeNull()
        ->and($fresh->publications->firstWhere('format', 'feed')->status)->toBe('published')
        ->and($reel->status)->toBe('pending')
        ->and($reel->error_message)->toBeNull()
        ->and($reel->state['items'][0]['container_id'])->toBeNull();
});

it('mewajibkan media format yang dipilih tersisa setelah penghapusan', function () {
    $user = fmtUser();
    $post = fmtExisting($user);
    $media = $post->mediaFor('feed')->first();

    fmtUpdate($user, $post, ['formats' => ['feed'], 'remove_media' => [$media->id]])
        ->assertSessionHasErrors(['media' => 'Feed membutuhkan minimal 1 foto.']);

    fmtUpdate($user, $post, ['formats' => ['feed'], 'remove_media' => [$media->id], 'media' => [fmtPhoto()]])
        ->assertSessionHasNoErrors();

    $fresh = $post->fresh('media');
    expect($fresh->media)->toHaveCount(1)->and($fresh->media->first()->id)->not->toBe($media->id);
});

it('halaman ubah menampilkan format yang terkunci dan media tiap format', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'published'], ['reel', 'failed']]);
    $post->publications()->where('format', 'reel')->update(['error_message' => 'Video ditolak', 'share_to_feed' => false]);
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL]);

    $html = $this->actingAs($user)->get(route('admin.scheduled-posts.edit', $post))
        ->assertOk()
        ->assertSee('Sebagian format sudah terbit, sebagian gagal.')
        ->assertSee('Video ditolak')
        ->getContent();

    $cfg = composerConfig($html);
    expect($cfg['formats'])->toBe(['feed', 'reel'])
        ->and($cfg['locked'])->toBe(['feed'])
        ->and($cfg['shareToFeed'])->toBeFalse()
        ->and($cfg['existing']['feed'])->toHaveCount(1)
        ->and($cfg['existing']['reel'][0]['type'])->toBe('video')
        ->and($cfg['existing']['story'])->toBe([]);
});

// ---------------------------------------------------------------------------------------------
// Duplikat dan hapus
// ---------------------------------------------------------------------------------------------

it('duplikasi menyalin semua format dan media termasuk video', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'published'], ['reel', 'failed']]);
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL]);
    $post->publications()->where('format', 'reel')->update(['share_to_feed' => false]);

    $this->actingAs($user)->post(route('admin.scheduled-posts.duplicate', $post))->assertRedirect();

    $copy = ScheduledPost::where('id', '!=', $post->id)->with(['publications', 'media'])->firstOrFail();
    $reelCopy = $copy->mediaFor('reel')->first();

    expect($copy->status)->toBe(ScheduledPost::STATUS_DRAFT)
        ->and($copy->formats())->toBe(['feed', 'reel'])
        ->and($copy->publications->pluck('status')->unique()->all())->toBe(['pending'])
        ->and($copy->publications->firstWhere('format', 'reel')->share_to_feed)->toBeFalse()
        ->and($reelCopy->type)->toBe('video')
        ->and($reelCopy->media_path)->toStartWith('scheduled-posts/videos/')
        ->and($reelCopy->media_path)->not->toBe($post->mediaFor('reel')->first()->media_path)
        ->and(Storage::disk('public')->exists($reelCopy->media_path))->toBeTrue()
        ->and($copy->mediaFor('feed'))->toHaveCount(1);
});

it('menghapus jadwal membersihkan semua file termasuk video dan baris publikasinya', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'pending'], ['reel', 'pending']]);
    $files = $post->media->pluck('media_path')->all();

    $this->actingAs($user)->delete(route('admin.scheduled-posts.destroy', $post))->assertRedirect();

    expect(ScheduledPost::count())->toBe(0)
        ->and(ScheduledPostPublication::count())->toBe(0)
        ->and(ScheduledPostMedia::count())->toBe(0);
    foreach ($files as $file) {
        expect(Storage::disk('public')->exists($file))->toBeFalse();
    }
});

// ---------------------------------------------------------------------------------------------
// Tampilan: keterangan format
// ---------------------------------------------------------------------------------------------

it('riwayat menampilkan lencana format dengan status tiap format dan galat per format', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'published'], ['story', 'pending'], ['reel', 'failed']]);
    $post->publications()->where('format', 'reel')->update(['error_message' => 'Video ditolak Instagram']);
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL, 'scheduled_at' => now()->subDay()]);

    $this->actingAs($user)->get(route('admin.post-history.index'))
        ->assertOk()
        ->assertSee('data-format-badges', false)
        ->assertSeeInOrder(['Feed', 'Story', 'Reels'])
        ->assertSee('Feed: Terbit')
        ->assertSee('Reels: Gagal')
        ->assertSee('Reels:')
        ->assertSee('Video ditolak Instagram')
        ->assertSee('Sebagian terbit')
        ->assertSee('Jadwalkan Ulang');

    $this->actingAs($user)->get(route('admin.post-history.index', ['status' => 'partial']))
        ->assertOk()
        ->assertSee('Video ditolak Instagram');
});

it('kalender menampilkan lencana format dan modal memuat format serta jenis media', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['feed', 'pending'], ['reel', 'pending']]);
    $post->update(['scheduled_at' => now(ScheduledPost::WIB)->addDays(2)->setTime(10, 0)->utc()]);

    $response = $this->actingAs($user)->get(route('admin.scheduled-posts.index'))->assertOk();
    $html = $response->getContent();

    expect(substr_count($html, 'data-format-badges'))->toBeGreaterThanOrEqual(1)
        ->and($html)->toContain('Feed')->toContain('Reels');

    $modal = collect($response->viewData('postsForModal'))->firstWhere('id', $post->id);
    expect(collect($modal['formats'])->pluck('key')->all())->toBe(['feed', 'reel'])
        ->and(collect($modal['media'])->pluck('type')->all())->toBe(['image', 'video'])
        ->and(collect($modal['media'])->pluck('format')->all())->toBe(['Feed', 'Reels'])
        ->and($modal['uses_caption'])->toBeTrue();
});

it('postingan khusus Story ditandai tanpa caption dan video-saja memakai ubin ikon', function () {
    $user = fmtUser();
    $post = fmtExisting($user, [['reel', 'pending']]);
    $post->update(['caption' => '', 'scheduled_at' => now()->subDay(), 'status' => ScheduledPost::STATUS_PUBLISHED]);
    $post->publications()->update(['status' => 'published', 'published_at' => now()]);

    $this->actingAs($user)->get(route('admin.post-history.index'))
        ->assertOk()
        ->assertSee('Tanpa caption (Story)')
        ->assertSee('Reels');

    expect($post->fresh('media')->has_video_only)->toBeTrue();
});

it('ringkasan di halaman penjadwalan menyebut yang gagal dan yang terbit sebagian', function () {
    $user = fmtUser();
    ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_FAILED]);
    ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_PARTIAL]);
    ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_PARTIAL]);

    $this->actingAs($user)->get(route('admin.scheduled-posts.index'))
        ->assertOk()
        ->assertSee('postingan gagal terbit')
        ->assertSee('terbit sebagian')
        ->assertSee('href="'.route('admin.post-history.index', ['status' => 'failed']).'"', false);
});

// ---------------------------------------------------------------------------------------------
// Urutan media (seret-lepas)
// ---------------------------------------------------------------------------------------------

/** Lebar tiap foto berbeda supaya urutan di database bisa dikenali dari lebar yang tersimpan. */
function fmtWidths(ScheduledPost $post, string $format): array
{
    return $post->fresh('media')->mediaFor($format)->pluck('width')->all();
}

it('menyimpan urutan unggahan baru sesuai urutan dari form', function () {
    $user = fmtUser();

    fmtPost($user, [
        'formats' => ['feed'],
        'media' => [fmtPhoto(1080, 1350), fmtPhoto(1000, 1250), fmtPhoto(900, 1125)],
        'order' => ['feed' => ['n:2', 'n:0', 'n:1']],
    ])->assertSessionHasNoErrors();

    expect(fmtWidths(ScheduledPost::firstOrFail(), 'feed'))->toBe([900, 1080, 1000]);
});

it('tanpa urutan, media tersimpan sesuai urutan unggah seperti sebelumnya', function () {
    fmtPost(fmtUser(), ['formats' => ['feed'], 'media' => [fmtPhoto(1080, 1350), fmtPhoto(1000, 1250)]])
        ->assertSessionHasNoErrors();

    expect(fmtWidths(ScheduledPost::firstOrFail(), 'feed'))->toBe([1080, 1000]);
});

it('mengurutkan media Story dan Feed secara terpisah', function () {
    fmtPost(fmtUser(), [
        'formats' => ['feed', 'story'],
        'media' => [fmtPhoto(1080, 1350), fmtPhoto(1000, 1250)],
        'media_story' => [fmtPhoto(800, 1400), fmtPhoto(700, 1400)],
        'order' => ['feed' => ['n:1', 'n:0'], 'story' => ['n:0', 'n:1']],
    ])->assertSessionHasNoErrors();

    $post = ScheduledPost::firstOrFail();
    expect(fmtWidths($post, 'feed'))->toBe([1000, 1080])
        ->and(fmtWidths($post, 'story'))->toBe([800, 700]);
});

/** Jadwal dengan beberapa media Feed yang sudah tersimpan. */
function fmtWithFeedMedia(User $user, array $widths): ScheduledPost
{
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id, 'social_account_id' => fmtAccount($user)->id, 'caption' => 'Lama',
        'status' => ScheduledPost::STATUS_SCHEDULED, 'scheduled_at' => now()->addDay(),
    ]);
    $post->media()->delete();
    $post->publications()->create(['format' => 'feed']);

    foreach ($widths as $position => $width) {
        Storage::disk('public')->put("scheduled-posts/w{$width}.jpg", 'x');
        $post->media()->create([
            'format' => 'feed', 'type' => 'image', 'media_path' => "scheduled-posts/w{$width}.jpg",
            'width' => $width, 'position' => $position,
        ]);
    }

    return $post->fresh('media');
}

it('mengurutkan ulang media yang sudah tersimpan', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600, 700]);
    [$a, $b, $c] = $post->media->pluck('id')->all();

    fmtUpdate($user, $post, ['formats' => ['feed'], 'order' => ['feed' => ["e:{$c}", "e:{$a}", "e:{$b}"]]])
        ->assertSessionHasNoErrors();

    expect(fmtWidths($post, 'feed'))->toBe([700, 500, 600]);
});

it('menyisipkan unggahan baru di tengah media yang sudah ada', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600]);
    [$a, $b] = $post->media->pluck('id')->all();

    fmtUpdate($user, $post, [
        'formats' => ['feed'],
        'media' => [fmtPhoto(900, 1125), fmtPhoto(800, 1000)],
        'order' => ['feed' => ["e:{$a}", 'n:1', "e:{$b}", 'n:0']],
    ])->assertSessionHasNoErrors();

    expect(fmtWidths($post, 'feed'))->toBe([500, 800, 600, 900]);
});

it('urutan tetap konsisten setelah media dihapus', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600, 700]);
    [$a, $b, $c] = $post->media->pluck('id')->all();

    fmtUpdate($user, $post, ['formats' => ['feed'], 'remove_media' => [$b], 'order' => ['feed' => ["e:{$c}", "e:{$a}"]]])
        ->assertSessionHasNoErrors();

    $fresh = $post->fresh('media');
    expect(fmtWidths($post, 'feed'))->toBe([700, 500])
        ->and($fresh->media->pluck('position')->all())->toBe([0, 1]);
});

it('mengabaikan token urutan yang tidak valid atau milik jadwal lain dan menaruh sisanya di belakang', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600, 700]);
    $other = fmtWithFeedMedia($user, [111]);
    [$a, $b, $c] = $post->media->pluck('id')->all();
    $foreign = $other->media->first()->id;

    fmtUpdate($user, $post, [
        'formats' => ['feed'],
        // media milik jadwal lain, nomor unggahan yang tidak ada, dan duplikat diabaikan; $a tidak disebut
        'order' => ['feed' => ["e:{$c}", "e:{$foreign}", 'n:7', "e:{$c}", "e:{$b}"]],
    ])->assertSessionHasNoErrors();

    expect(fmtWidths($post, 'feed'))->toBe([700, 600, 500])
        ->and($other->fresh('media')->media->first()->position)->toBe(0);
});

it('menolak token urutan dengan bentuk yang salah', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600]);

    fmtUpdate($user, $post, ['formats' => ['feed'], 'order' => ['feed' => ['bukan-token']]])
        ->assertSessionHasErrors('order.feed.0');
});

it('urutan format yang sudah terbit diabaikan', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600]);
    $post->publications()->update(['status' => 'published', 'published_at' => now()]);
    $post->update(['status' => ScheduledPost::STATUS_PARTIAL]);
    $post->publications()->create(['format' => 'story', 'status' => 'failed']);
    Storage::disk('public')->put('scheduled-posts/s.jpg', 'x');
    $post->media()->create(['format' => 'story', 'type' => 'image', 'media_path' => 'scheduled-posts/s.jpg', 'width' => 300, 'position' => 0]);
    [$a, $b] = $post->fresh('media')->mediaFor('feed')->pluck('id')->all();

    fmtUpdate($user, $post, ['formats' => ['feed', 'story'], 'order' => ['feed' => ["e:{$b}", "e:{$a}"]]])
        ->assertSessionHasNoErrors();

    expect(fmtWidths($post, 'feed'))->toBe([500, 600]);
});

it('halaman ubah mengirim media sesuai urutan tersimpan', function () {
    $user = fmtUser();
    $post = fmtWithFeedMedia($user, [500, 600, 700]);
    $ids = $post->media->pluck('id')->all();
    $post->media()->where('id', $ids[2])->update(['position' => 0]);
    $post->media()->where('id', $ids[0])->update(['position' => 1]);
    $post->media()->where('id', $ids[1])->update(['position' => 2]);

    $cfg = composerConfig($this->actingAs($user)->get(route('admin.scheduled-posts.edit', $post))->getContent());

    expect(collect($cfg['existing']['feed'])->pluck('id')->all())->toBe([$ids[2], $ids[0], $ids[1]]);
});

it('menerima Reels dari MP4 fragmented (video stok, perekam layar) dan menyimpan durasinya', function () {
    $path = mp4File(fakeFragmentedMp4(seconds: 10, fps: 60, fragments: 2));

    fmtPost(fmtUser(), ['formats' => ['reel'], 'media_reel' => [new UploadedFile($path, 'stok.mp4', 'video/mp4', null, true)]])
        ->assertSessionHasNoErrors();

    $media = ScheduledPostMedia::firstOrFail();
    expect($media->duration_ms)->toBe(10000)->and($media->width)->toBe(1080)->and($media->height)->toBe(1920);
});

it('menolak Reels fragmented yang melebihi batas durasi dengan pesan durasi, bukan "tidak bisa dibaca"', function () {
    $path = mp4File(fakeFragmentedMp4(seconds: 200, fps: 30, fragments: 4));

    fmtPost(fmtUser(), ['formats' => ['reel'], 'media_reel' => [new UploadedFile($path, 'panjang.mp4', 'video/mp4', null, true)]])
        ->assertSessionHasErrors(['media_reel.0' => 'Durasi video 3 menit 20 detik melebihi batas 3 menit untuk Reels.']);
});
