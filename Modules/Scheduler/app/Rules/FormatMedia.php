<?php

namespace Modules\Scheduler\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Services\Mp4Inspector;
use Modules\Scheduler\Services\VideoSpec;
use Throwable;

/**
 * Memeriksa satu file unggahan terhadap syarat formatnya.
 * Feed: foto JPEG. Story: foto JPEG atau video MP4/MOV. Reels: video MP4/MOV.
 */
class FormatMedia implements ValidationRule
{
    private const VIDEO_MIMES = ['video/mp4', 'video/quicktime', 'video/x-m4v'];

    /**
     * @param  bool  $enforceFeedRatio  Rasio Feed hanya dipaksa untuk Instagram; Facebook menerima rasio apa pun.
     */
    public function __construct(private readonly string $format, private readonly bool $enforceFeedRatio = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('File gagal diunggah. Coba lagi, dan periksa batas ukuran unggahan server.');

            return;
        }

        $mime = (string) $value->getMimeType();
        $isPhoto = $mime === 'image/jpeg';
        $isVideo = in_array($mime, self::VIDEO_MIMES, true);

        if ($this->format === ScheduledPost::FORMAT_FEED && ! $isPhoto) {
            $fail('Feed hanya menerima foto JPEG (.jpg atau .jpeg).');

            return;
        }

        if ($this->format === ScheduledPost::FORMAT_REEL && ! $isVideo) {
            $fail('Reels hanya menerima video MP4 atau MOV.');

            return;
        }

        if (! $isPhoto && ! $isVideo) {
            $fail('Story menerima foto JPEG atau video MP4/MOV.');

            return;
        }

        $isPhoto ? $this->validatePhoto($value, $attribute, $fail) : $this->validateVideo($value, $fail);
    }

    private function validatePhoto(UploadedFile $file, string $attribute, Closure $fail): void
    {
        $maxMb = (int) config('scheduler.video.photo_max_mb', 8);

        if ($file->getSize() > $maxMb * 1048576) {
            $fail("Ukuran foto maksimal {$maxMb} MB.");

            return;
        }

        $rule = new InstagramPhoto(enforceRatio: $this->format === ScheduledPost::FORMAT_FEED && $this->enforceFeedRatio);
        $rule->validate($attribute, $file, $fail);
    }

    private function validateVideo(UploadedFile $file, Closure $fail): void
    {
        // Batas ukuran diperiksa lebih dulu agar file besar tidak perlu dibaca.
        if ($file->getSize() > VideoSpec::maxBytes($this->format)) {
            $fail(VideoSpec::problem(
                ['duration' => (float) config('scheduler.video.min_seconds', 3), 'video_codec' => 'h264', 'audio_codec' => null, 'has_audio' => false, 'fps' => null],
                (int) $file->getSize(),
                $this->format
            ));

            return;
        }

        try {
            $info = app(Mp4Inspector::class)->inspect($file->getRealPath());
        } catch (Throwable $e) {
            $fail($e->getMessage() ?: 'Video tidak bisa dibaca. Gunakan MP4 atau MOV (H.264 + AAC).');

            return;
        }

        if ($problem = VideoSpec::problem($info, (int) $file->getSize(), $this->format)) {
            $fail($problem);
        }
    }
}
