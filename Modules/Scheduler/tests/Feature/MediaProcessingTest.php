<?php

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Jobs\PublishScheduledPostJob;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Services\InstagramPublisher;
use Spatie\Permission\Models\Permission;

function mediaUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        ['scheduler.view', 'scheduler.create', 'scheduler.edit']
    ));

    return $user;
}

function mediaAccount(User $user): SocialAccount
{
    return SocialAccount::create([
        'user_id' => $user->id,
        'platform' => SocialAccount::PLATFORM_INSTAGRAM,
        'provider_account_id' => 'ig-media-'.$user->id,
        'username' => 'media_'.$user->id,
        'status' => SocialAccount::STATUS_ACTIVE,
    ]);
}

/**
 * JPEG sungguhan dengan noise (supaya ukurannya realistis), komentar, dan blok EXIF.
 * setImageOrientation() milik ImageMagick tidak menulis tag EXIF ke file, jadi blok EXIF
 * berisi tag Orientation disisipkan langsung, seperti foto dari kamera HP.
 */
function realJpeg(int $width, int $height, int $orientation = 1): string
{
    $image = new Imagick;
    $image->newPseudoImage($width, $height, 'plasma:fractal');
    $image->setImageFormat('jpeg');
    $image->setImageCompressionQuality(95);
    $image->setImageProperty('comment', 'lokasi rahasia');
    $jpeg = $image->getImageBlob();
    $image->clear();

    // TIFF big-endian, satu entri IFD: tag 0x0112 (Orientation), tipe SHORT, 1 nilai.
    $tiff = "MM\x00\x2A\x00\x00\x00\x08"."\x00\x01"."\x01\x12\x00\x03\x00\x00\x00\x01".pack('n', $orientation)."\x00\x00"."\x00\x00\x00\x00";
    $payload = "Exif\x00\x00".$tiff;
    $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

    $path = tempnam(sys_get_temp_dir(), 'jpg').'.jpg';
    file_put_contents($path, substr($jpeg, 0, 2).$app1.substr($jpeg, 2));

    return $path;
}

function mediaSlot(): string
{
    return ScheduledPost::nextSlot(now()->addHour())->setTimezone(ScheduledPost::WIB)->format('Y-m-d\TH:i');
}

function storePhoto(User $user, string $path, string $name = 'foto.jpg')
{
    return test()->actingAs($user)->post(route('admin.scheduled-posts.store'), [
        'caption' => 'Uji foto.',
        'social_account_id' => mediaAccount($user)->id,
        'media' => [new UploadedFile($path, $name, 'image/jpeg', null, true)],
        'scheduled_at' => mediaSlot(),
    ]);
}

function openStored(string $path): Imagick
{
    $image = new Imagick;
    $image->readImage(Storage::disk('public')->path($path));

    return $image;
}

beforeEach(fn () => Storage::fake('public'));

it('menyimpan versi terbit selebar 1440px dan thumbnail 400px, bukan file asli', function () {
    $source = realJpeg(3000, 2400);
    $originalSize = filesize($source);

    storePhoto(mediaUser(), $source)->assertSessionHasNoErrors();

    $media = ScheduledPostMedia::firstOrFail();
    $publish = openStored($media->media_path);
    $thumb = openStored($media->thumbnail_path);

    expect($publish->getImageWidth())->toBe(1440)
        ->and($publish->getImageHeight())->toBe(1152)
        ->and($thumb->getImageWidth())->toBe(400)
        ->and($media->width)->toBe(1440)
        ->and($media->height)->toBe(1152)
        ->and($media->size)->toBe(Storage::disk('public')->size($media->media_path))
        ->and($media->size)->toBeLessThan($originalSize)
        ->and(Storage::disk('public')->size($media->thumbnail_path))->toBeLessThan($media->size)
        // hanya dua file: versi terbit dan thumbnail
        ->and(Storage::disk('public')->allFiles())->toHaveCount(2);
});

it('memutar foto sesuai orientasi EXIF dan menghapus metadata', function () {
    // Tersimpan lanskap 1000x800, tetapi EXIF menyatakan diputar 90 derajat: tampil potret 800x1000.
    storePhoto(mediaUser(), realJpeg(1000, 800, Imagick::ORIENTATION_RIGHTTOP))->assertSessionHasNoErrors();

    $publish = openStored(ScheduledPostMedia::firstOrFail()->media_path);

    expect($publish->getImageWidth())->toBe(800)
        ->and($publish->getImageHeight())->toBe(1000)
        ->and($publish->getImageProperties('exif:*'))->toBe([])
        ->and($publish->getImageProperty('comment'))->toBeFalse()
        ->and($publish->getImageProfiles('*', false))->toBe([]);
});

it('tidak memperbesar foto yang sudah kecil', function () {
    storePhoto(mediaUser(), realJpeg(1080, 1080))->assertSessionHasNoErrors();

    expect(ScheduledPostMedia::firstOrFail()->width)->toBe(1080);
});

it('menolak rasio yang tidak diterima Instagram', function () {
    storePhoto(mediaUser(), realJpeg(900, 1600))
        ->assertSessionHasErrors(['media.0' => 'Rasio foto 900×1600 tidak diterima Instagram. Gunakan rasio antara 4:5 (potret) dan 1,91:1 (lanskap), misalnya 1:1 atau 4:5.']);

    expect(ScheduledPost::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('menerima rasio di batas 4:5 dan 1,91:1', function (int $width, int $height) {
    storePhoto(mediaUser(), realJpeg($width, $height))->assertSessionHasNoErrors();
})->with([
    'potret 4:5' => [800, 1000],
    'lanskap 1,91:1' => [1910, 1000],
]);

it('memakai orientasi EXIF saat memeriksa rasio', function () {
    // Tersimpan 1600x900 (lanskap 16:9, diterima), tetapi tampil 900x1600 (potret 9:16, ditolak).
    storePhoto(mediaUser(), realJpeg(1600, 900, Imagick::ORIENTATION_RIGHTTOP))
        ->assertSessionHasErrors('media.0');
});

it('menolak foto di atas batas resolusi', function () {
    config(['scheduler.media.max_megapixels' => 1]);

    storePhoto(mediaUser(), realJpeg(1200, 1000))
        ->assertSessionHasErrors(['media.0' => 'Resolusi foto terlalu besar (1,2 MP). Maksimal 1 MP; kecilkan dulu sebelum diunggah.']);
});

it('menolak file rusak yang berakhiran .jpg', function () {
    $path = tempnam(sys_get_temp_dir(), 'bad').'.jpg';
    file_put_contents($path, 'bukan gambar sama sekali');

    storePhoto(mediaUser(), $path, 'rusak.jpg')->assertSessionHasErrors('media.0');
    expect(ScheduledPost::count())->toBe(0);
});

it('menghapus versi terbit setelah masa simpan dan mempertahankan thumbnail', function () {
    $disk = Storage::disk('public');
    $user = mediaUser();

    $make = function (string $status, int $daysAgo, bool $withThumb = true) use ($user, $disk) {
        $post = ScheduledPost::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
            'published_at' => $status === ScheduledPost::STATUS_PUBLISHED ? now()->subDays($daysAgo) : null,
        ]);
        $disk->put("scheduled-posts/{$post->id}.jpg", 'terbit');
        if ($withThumb) {
            $disk->put("scheduled-posts/thumbs/{$post->id}.jpg", 'thumb');
        }

        return $post->media()->create([
            'media_path' => "scheduled-posts/{$post->id}.jpg",
            'thumbnail_path' => $withThumb ? "scheduled-posts/thumbs/{$post->id}.jpg" : null,
            'position' => 0,
        ]);
    };

    $old = $make(ScheduledPost::STATUS_PUBLISHED, 31);
    $recent = $make(ScheduledPost::STATUS_PUBLISHED, 10);
    $failed = $make(ScheduledPost::STATUS_FAILED, 0);
    $legacy = $make(ScheduledPost::STATUS_PUBLISHED, 60, withThumb: false);

    $this->artisan('scheduler:prune-media')->assertSuccessful();

    expect($old->fresh()->media_path)->toBeNull()
        ->and($old->fresh()->media_pruned_at)->not->toBeNull()
        ->and($disk->exists("scheduled-posts/{$old->scheduled_post_id}.jpg"))->toBeFalse()
        ->and($disk->exists($old->thumbnail_path))->toBeTrue()
        ->and($recent->fresh()->media_path)->not->toBeNull()
        ->and($failed->fresh()->media_path)->not->toBeNull()
        // foto lama tanpa thumbnail tidak dihapus agar tetap ada gambar
        ->and($legacy->fresh()->media_path)->not->toBeNull();
});

it('menduplikasi postingan yang fotonya sudah dihapus dengan peringatan', function () {
    $user = mediaUser();
    $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_PUBLISHED]);
    $post->media()->delete();
    Storage::disk('public')->put('scheduled-posts/thumbs/a.jpg', 'thumb');
    $post->media()->create([
        'media_path' => null,
        'thumbnail_path' => 'scheduled-posts/thumbs/a.jpg',
        'media_pruned_at' => now(),
        'position' => 0,
    ]);

    $this->actingAs($user)->post(route('admin.scheduled-posts.duplicate', $post))
        ->assertSessionHas('error', fn ($message) => str_contains($message, '1 foto tidak ikut tersalin'));

    $copy = ScheduledPost::where('id', '!=', $post->id)->firstOrFail();
    expect($copy->media()->count())->toBe(0);
});

it('menolak menerbitkan postingan yang fotonya sudah tidak tersedia', function () {
    $user = mediaUser();
    $post = ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'social_account_id' => SocialAccount::create([
            'user_id' => $user->id, 'platform' => 'instagram', 'provider_account_id' => 'x1',
            'username' => 'x1', 'access_token' => 'token', 'status' => SocialAccount::STATUS_ACTIVE,
        ])->id,
        'status' => ScheduledPost::STATUS_SCHEDULED,
        'scheduled_at' => now()->subMinute(),
    ]);
    $post->media()->delete();
    $post->media()->create(['media_path' => null, 'thumbnail_path' => 'scheduled-posts/thumbs/a.jpg', 'position' => 0]);
    Http::fake();

    (new PublishScheduledPostJob($post->id))
        ->handle(app(InstagramPublisher::class));

    expect($post->fresh()->status)->toBe(ScheduledPost::STATUS_FAILED)
        ->and($post->fresh()->error_message)->toContain('Unggah ulang fotonya');
    Http::assertNothingSent();
});

it('mengolah foto lama yang belum punya thumbnail', function () {
    $disk = Storage::disk('public');
    $user = mediaUser();
    $post = ScheduledPost::factory()->create(['user_id' => $user->id]);
    $post->media()->delete();
    $disk->put('scheduled-posts/lama.jpg', file_get_contents(realJpeg(2000, 2000)));
    $media = $post->media()->create(['media_path' => 'scheduled-posts/lama.jpg', 'position' => 0]);

    $this->artisan('scheduler:process-existing-media')->assertSuccessful();

    $media->refresh();
    expect($media->thumbnail_path)->not->toBeNull()
        ->and($media->width)->toBe(1440)
        ->and($disk->exists('scheduled-posts/lama.jpg'))->toBeFalse()
        ->and($disk->exists($media->media_path))->toBeTrue()
        ->and($disk->exists($media->thumbnail_path))->toBeTrue();

    // dijalankan ulang tidak mengolah dua kali
    $this->artisan('scheduler:process-existing-media')->expectsOutputToContain('0 foto diolah')->assertSuccessful();
});

it('riwayat memakai thumbnail', function () {
    $user = mediaUser();
    $post = ScheduledPost::factory()->create(['user_id' => $user->id, 'status' => ScheduledPost::STATUS_PUBLISHED, 'scheduled_at' => now()->subDay()]);
    $post->media()->delete();
    Storage::disk('public')->put('scheduled-posts/thumbs/t.jpg', 'thumb');
    $post->media()->create(['media_path' => null, 'thumbnail_path' => 'scheduled-posts/thumbs/t.jpg', 'position' => 0]);

    $this->actingAs($user)->get(route('admin.post-history.index'))
        ->assertOk()
        ->assertSee('scheduled-posts/thumbs/t.jpg');
});

it('menjadwalkan pembersihan foto setiap hari', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'scheduler:prune-media'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 1 * * *')
        ->and($event->expiresAt)->toBe(60);
});
