<?php

use Modules\Scheduler\Services\Mp4Inspector;

it('membaca durasi, ukuran, codec, frame rate, dan posisi moov', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(seconds: 100, fps: 29.97)));

    expect($info['duration'])->toBe(100.0)
        ->and($info['width'])->toBe(1080)
        ->and($info['height'])->toBe(1920)
        ->and($info['video_codec'])->toBe('h264')
        ->and($info['audio_codec'])->toBe('aac')
        ->and($info['has_audio'])->toBeTrue()
        ->and($info['fps'])->toBe(29.97)
        ->and($info['faststart'])->toBeTrue()
        ->and($info['brand'])->toBe('isom');
});

it('mengenali HEVC, video tanpa audio, dan brand QuickTime', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(videoCodec: 'hvc1', audioCodec: null, brand: 'qt  ')));

    expect($info['video_codec'])->toBe('hevc')
        ->and($info['has_audio'])->toBeFalse()
        ->and($info['audio_codec'])->toBeNull()
        ->and($info['brand'])->toBe('qt');
});

it('melaporkan codec yang tidak didukung apa adanya', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(videoCodec: 'vp09', audioCodec: 'ac-3')));

    expect($info['video_codec'])->toBe('vp09')->and($info['audio_codec'])->toBe('ac-3');
});

it('menukar lebar dan tinggi untuk video yang diputar 90 derajat seperti rekaman iPhone', function () {
    // Disimpan landscape 1920x1080 dengan matriks rotasi: tampil potret 1080x1920.
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(width: 1920, height: 1080, rotate: true)));

    expect($info['width'])->toBe(1080)->and($info['height'])->toBe(1920);

    $plain = (new Mp4Inspector)->inspect(mp4File(fakeMp4(width: 1920, height: 1080, rotate: false)));
    expect($plain['width'])->toBe(1920)->and($plain['height'])->toBe(1080);
});

it('mendeteksi moov yang berada di belakang mdat', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(faststart: false)));

    expect($info['faststart'])->toBeFalse()->and($info['duration'])->toBe(10.0);
});

it('membaca box mdat berukuran 64 bit', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeMp4(largeMdat: true, faststart: false)));

    expect($info['faststart'])->toBeFalse()->and($info['duration'])->toBe(10.0);
});

it('menolak file yang bukan video, kosong, atau terpotong', function (string $content) {
    expect(fn () => (new Mp4Inspector)->inspect(mp4File($content)))->toThrow(RuntimeException::class);
})->with([
    'teks biasa' => ['ini bukan video sama sekali, hanya teks'],
    'kosong' => [''],
    'jpeg' => ["\xFF\xD8\xFF\xE0".str_repeat("\0", 100)],
    'terpotong' => [substr(fakeMp4(), 0, 40)],
    'tanpa moov' => [mp4Box('ftyp', 'isom'.pack('N', 0)).mp4Box('mdat', str_repeat("\1", 16))],
]);

it('menolak video tanpa durasi', function () {
    expect(fn () => (new Mp4Inspector)->inspect(mp4File(fakeMp4(seconds: 0))))
        ->toThrow(RuntimeException::class, 'Durasi video');
});

it('menolak file yang tidak ada', function () {
    expect(fn () => (new Mp4Inspector)->inspect('/tmp/tidak-ada-'.uniqid().'.mp4'))
        ->toThrow(RuntimeException::class);
});

it('membaca durasi dan frame rate MP4 fragmented dari tiap sumber yang mungkin', function (string $source, array $options) {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeFragmentedMp4(...array_merge(['durationIn' => $source], $options))));

    expect($info['duration'])->toBe(8.0)
        ->and($info['width'])->toBe(1080)
        ->and($info['height'])->toBe(1920)
        ->and($info['video_codec'])->toBe('h264')
        ->and($info['fps'])->toBe(30.0)
        ->and($info['faststart'])->toBeTrue();
})->with([
    'durasi di trun' => ['trun', []],
    'durasi bawaan di tfhd' => ['tfhd', []],
    'durasi bawaan di trex' => ['trex', []],
    'banyak fragmen kecil' => ['trun', ['fragments' => 24]],
    'satu fragmen' => ['trun', ['fragments' => 1]],
]);

it('membaca video stok 60 fps berformat fragmented', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeFragmentedMp4(seconds: 10, fps: 60, fragments: 2)));

    expect($info['duration'])->toBe(10.0)->and($info['fps'])->toBe(60.0)->and($info['has_audio'])->toBeFalse();
});

it('memakai mehd bila ada dan tetap membaca fps dari fragmen', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeFragmentedMp4(withMehd: true)));

    expect($info['duration'])->toBe(8.0)->and($info['fps'])->toBe(30.0);
});

it('mengabaikan fragmen jalur audio saat menghitung durasi video', function () {
    $info = (new Mp4Inspector)->inspect(mp4File(fakeFragmentedMp4(withAudioFragments: true)));

    expect($info['duration'])->toBe(8.0)->and($info['fps'])->toBe(30.0);
});

it('tetap menolak MP4 fragmented yang tidak memuat informasi durasi sama sekali', function () {
    expect(fn () => (new Mp4Inspector)->inspect(mp4File(fakeFragmentedMp4(durationIn: 'none'))))
        ->toThrow(RuntimeException::class, 'Durasi video');
});
