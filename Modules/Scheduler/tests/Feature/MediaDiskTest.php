<?php

use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Services\MediaDisk;
use Modules\Scheduler\Services\MediaProcessor;

it('memakai disk public bila SCHEDULER_MEDIA_DISK tidak diatur', function () {
    expect(MediaDisk::name())->toBe('public');
});

it('menulis foto olahan dan video ke disk yang diatur, bukan ke disk public', function () {
    Storage::fake('public');
    Storage::fake('media_alt');
    config(['scheduler.media.disk' => 'media_alt']);

    $source = tempnam(sys_get_temp_dir(), 'foto').'.jpg';
    $canvas = imagecreatetruecolor(1080, 1350);
    imagejpeg($canvas, $source);

    try {
        $photo = app(MediaProcessor::class)->process($source);
    } finally {
        @unlink($source);
    }

    expect(Storage::disk('media_alt')->exists($photo['media_path']))->toBeTrue()
        ->and(Storage::disk('media_alt')->exists($photo['thumbnail_path']))->toBeTrue()
        ->and(Storage::disk('public')->allFiles())->toBe([])
        ->and(MediaDisk::url($photo['media_path']))->toContain($photo['media_path']);
});
