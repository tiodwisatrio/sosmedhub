<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;

function orphanFile(string $path, int $hoursOld): void
{
    Storage::disk('public')->put($path, 'isi');
    touch(Storage::disk('public')->path($path), now()->subHours($hoursOld)->getTimestamp());
}

it('menghapus file yatim yang lama dan mempertahankan yang dirujuk atau masih baru', function () {
    $post = ScheduledPost::factory()->create();
    $post->media()->delete();

    orphanFile('scheduled-posts/dirujuk.jpg', 100);
    orphanFile('scheduled-posts/thumbs/dirujuk.jpg', 100);
    orphanFile('scheduled-posts/videos/dirujuk.mp4', 100);
    $post->media()->create([
        'media_path' => 'scheduled-posts/dirujuk.jpg',
        'thumbnail_path' => 'scheduled-posts/thumbs/dirujuk.jpg',
        'position' => 0,
    ]);
    $post->media()->create(['media_path' => 'scheduled-posts/videos/dirujuk.mp4', 'type' => 'video', 'position' => 1]);

    orphanFile('scheduled-posts/yatim-lama.jpg', 48);
    orphanFile('scheduled-posts/thumbs/yatim-lama.jpg', 48);
    orphanFile('scheduled-posts/videos/yatim-lama.mp4', 30);
    orphanFile('scheduled-posts/yatim-baru.jpg', 2);

    $this->artisan('scheduler:prune-orphans')->expectsOutputToContain('Menghapus 3 file yatim')->assertSuccessful();

    $disk = Storage::disk('public');
    expect($disk->exists('scheduled-posts/dirujuk.jpg'))->toBeTrue()
        ->and($disk->exists('scheduled-posts/thumbs/dirujuk.jpg'))->toBeTrue()
        ->and($disk->exists('scheduled-posts/videos/dirujuk.mp4'))->toBeTrue()
        ->and($disk->exists('scheduled-posts/yatim-baru.jpg'))->toBeTrue()
        ->and($disk->exists('scheduled-posts/yatim-lama.jpg'))->toBeFalse()
        ->and($disk->exists('scheduled-posts/thumbs/yatim-lama.jpg'))->toBeFalse()
        ->and($disk->exists('scheduled-posts/videos/yatim-lama.mp4'))->toBeFalse();
});

it('dry-run hanya menampilkan tanpa menghapus', function () {
    orphanFile('scheduled-posts/yatim.jpg', 72);

    $this->artisan('scheduler:prune-orphans', ['--dry-run' => true])
        ->expectsOutputToContain('akan dihapus: scheduled-posts/yatim.jpg')
        ->expectsOutputToContain('Akan menghapus 1 file yatim')
        ->assertSuccessful();

    expect(Storage::disk('public')->exists('scheduled-posts/yatim.jpg'))->toBeTrue();
});

it('batas umur bisa diatur dan folder lain tidak disentuh', function () {
    orphanFile('scheduled-posts/sedang.jpg', 5);
    orphanFile('site-settings/logo.jpg', 500);

    $this->artisan('scheduler:prune-orphans', ['--hours' => 3])->assertSuccessful();

    expect(Storage::disk('public')->exists('scheduled-posts/sedang.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('site-settings/logo.jpg'))->toBeTrue();
});

it('menjadwalkan pembersihan file yatim setiap hari', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'scheduler:prune-orphans'));

    expect($event)->not->toBeNull()->and($event->expression)->toBe('0 2 * * *');
});
