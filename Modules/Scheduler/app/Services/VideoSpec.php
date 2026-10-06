<?php

namespace Modules\Scheduler\Services;

use Modules\Scheduler\Models\ScheduledPost;

/**
 * Syarat video Instagram per format, diperiksa terhadap hasil Mp4Inspector.
 * Story: 3-60 detik, maks 100MB. Reels: 3 detik sampai batas konfigurasi (bawaan 3 menit), maks 300MB.
 */
class VideoSpec
{
    private const FPS_MIN = 23;

    private const FPS_MAX = 60;

    // Pembulatan frame rate rata-rata pada video bitrate variabel.
    private const FPS_TOLERANCE = 0.5;

    public static function maxSeconds(string $format): int
    {
        return (int) config($format === ScheduledPost::FORMAT_REEL
            ? 'scheduler.video.reel_max_seconds'
            : 'scheduler.video.story_max_seconds');
    }

    public static function maxBytes(string $format): int
    {
        return (int) config($format === ScheduledPost::FORMAT_REEL
            ? 'scheduler.video.reel_max_mb'
            : 'scheduler.video.story_max_mb') * 1024 * 1024;
    }

    /**
     * @param  array{duration: float, video_codec: ?string, audio_codec: ?string, has_audio: bool, fps: ?float}  $info
     * @return string|null Pesan galat bila tidak memenuhi syarat
     */
    public static function problem(array $info, int $bytes, string $format): ?string
    {
        $label = ScheduledPost::formatLabel($format);
        $min = (int) config('scheduler.video.min_seconds', 3);
        $maxSeconds = self::maxSeconds($format);
        $maxBytes = self::maxBytes($format);

        if ($info['duration'] < $min) {
            return sprintf('Video terlalu pendek (%s). Minimal %d detik.', self::duration($info['duration']), $min);
        }

        if ($info['duration'] > $maxSeconds) {
            return sprintf('Durasi video %s melebihi batas %s untuk %s.', self::duration($info['duration']), self::duration($maxSeconds), $label);
        }

        if ($bytes > $maxBytes) {
            return sprintf('Ukuran video %s MB melebihi batas %d MB untuk %s.', number_format($bytes / 1048576, 1, ',', '.'), $maxBytes / 1048576, $label);
        }

        if (! in_array($info['video_codec'], ['h264', 'hevc'], true)) {
            return sprintf('Codec video "%s" tidak didukung Instagram. Ekspor ulang sebagai MP4 dengan H.264 (atau HEVC) dan audio AAC.', $info['video_codec'] ?? 'tidak dikenal');
        }

        if ($info['has_audio'] && $info['audio_codec'] !== 'aac') {
            return sprintf('Codec audio "%s" tidak didukung Instagram. Gunakan audio AAC.', $info['audio_codec'] ?? 'tidak dikenal');
        }

        if ($info['fps'] !== null && ($info['fps'] < self::FPS_MIN - self::FPS_TOLERANCE || $info['fps'] > self::FPS_MAX + self::FPS_TOLERANCE)) {
            return sprintf('Frame rate %s fps di luar batas %d-%d fps yang diterima Instagram.', rtrim(rtrim(number_format($info['fps'], 2, ',', ''), '0'), ','), self::FPS_MIN, self::FPS_MAX);
        }

        return null;
    }

    /**
     * "45 detik", "3 menit", atau "2 menit 30 detik".
     */
    public static function duration(float $seconds): string
    {
        $whole = (int) round($seconds);

        if ($whole < 60) {
            return $whole.' detik';
        }

        $minutes = intdiv($whole, 60);
        $rest = $whole % 60;

        return $rest === 0 ? "{$minutes} menit" : "{$minutes} menit {$rest} detik";
    }
}
