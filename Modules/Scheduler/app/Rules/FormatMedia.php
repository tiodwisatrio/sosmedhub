<?php

namespace Modules\Scheduler\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Services\Mp4Inspector;
use Modules\Scheduler\Services\VideoSpec;
use Modules\Scheduler\Services\VideoTrimmer;
use Throwable;

/**
 * Memeriksa satu file unggahan terhadap syarat formatnya.
 * Feed: foto JPEG (Instagram juga menerima video MP4/MOV dalam carousel). Story: foto JPEG atau video MP4/MOV.
 * Reels: video MP4/MOV.
 */
class FormatMedia implements ValidationRule
{
    private const VIDEO_MIMES = ['video/mp4', 'video/quicktime', 'video/x-m4v'];

    /**
     * @param  bool  $enforceFeedRatio  Rasio Feed hanya dipaksa untuk Instagram; Facebook menerima rasio apa pun.
     */
    /**
     * @param  array<int|string, mixed>  $trims  rentang potong Story per urutan file unggahan: [index => ['start' => detik, 'end' => detik]]
     * @param  string|null  $platform  Platform akun tujuan; memengaruhi batas durasi Reels (Facebook 90 detik).
     */
    public function __construct(
        private readonly string $format,
        private readonly bool $enforceFeedRatio = true,
        private readonly array $trims = [],
        private readonly ?string $platform = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail(__('File gagal diunggah. Coba lagi, dan periksa batas ukuran unggahan server.'));

            return;
        }

        $mime = (string) $value->getMimeType();
        $isPhoto = $mime === 'image/jpeg';
        $isVideo = in_array($mime, self::VIDEO_MIMES, true);

        if ($this->format === ScheduledPost::FORMAT_FEED && ! $isPhoto) {
            // Facebook baru mendukung foto untuk Feed; Instagram boleh mencampur foto dan video.
            if ($this->platform === 'facebook' || ! $isVideo) {
                $fail($this->platform === 'facebook'
                    ? __('Feed Facebook hanya menerima foto JPEG (.jpg atau .jpeg).')
                    : __('Feed menerima foto JPEG atau video MP4/MOV.'));

                return;
            }
        }

        if ($this->format === ScheduledPost::FORMAT_REEL && ! $isVideo) {
            $fail(__('Reels hanya menerima video MP4 atau MOV.'));

            return;
        }

        if (! $isPhoto && ! $isVideo) {
            $fail(__('Story menerima foto JPEG atau video MP4/MOV.'));

            return;
        }

        $isPhoto ? $this->validatePhoto($value, $attribute, $fail) : $this->validateVideo($value, $attribute, $fail);
    }

    private function validatePhoto(UploadedFile $file, string $attribute, Closure $fail): void
    {
        $maxMb = (int) config('scheduler.video.photo_max_mb', 8);

        if ($file->getSize() > $maxMb * 1048576) {
            $fail(__('Ukuran foto maksimal :mb MB.', ['mb' => $maxMb]));

            return;
        }

        $rule = new InstagramPhoto(enforceRatio: $this->format === ScheduledPost::FORMAT_FEED && $this->enforceFeedRatio);
        $rule->validate($attribute, $file, $fail);
    }

    private function validateVideo(UploadedFile $file, string $attribute, Closure $fail): void
    {
        $trim = $this->trimFor($attribute);

        // Batas ukuran diperiksa lebih dulu agar file besar tidak perlu dibaca. Video yang akan
        // dipotong boleh lebih besar; hasil potongannya diperiksa saat disimpan.
        $maxBytes = $trim ? VideoSpec::trimSourceMaxBytes() : VideoSpec::maxBytes($this->format);

        if ($file->getSize() > $maxBytes) {
            $fail($trim
                ? __('Ukuran video :size MB melebihi batas :max MB untuk video yang dipotong.', ['size' => VideoSpec::number($file->getSize() / 1048576), 'max' => (int) ($maxBytes / 1048576)])
                : VideoSpec::problem(
                    ['duration' => (float) config('scheduler.video.min_seconds', 3), 'video_codec' => 'h264', 'audio_codec' => null, 'has_audio' => false, 'fps' => null],
                    (int) $file->getSize(),
                    $this->format,
                    $this->platform
                ));

            return;
        }

        try {
            $info = app(Mp4Inspector::class)->inspect($file->getRealPath());
        } catch (Throwable $e) {
            $fail($e->getMessage() ?: __('Video tidak bisa dibaca. Gunakan MP4 atau MOV (H.264 + AAC).'));

            return;
        }

        $bytes = (int) $file->getSize();

        if ($trim) {
            if ($problem = $this->trimProblem($trim, $info['duration'])) {
                $fail($problem);

                return;
            }

            // Yang diperiksa adalah potongan: durasi sesuai rentang, ukuran diperiksa setelah dipotong.
            $info['duration'] = $trim['end'] - $trim['start'];
            $bytes = 0;
        }

        if ($problem = VideoSpec::problem($info, $bytes, $this->format, $this->platform)) {
            $fail($problem);
        }
    }

    /**
     * Rentang potong untuk file unggahan pada atribut seperti "media_story.2". Hanya Story, dan
     * hanya bila ffmpeg tersedia; selain itu rentang diabaikan dan batas durasi biasa berlaku.
     *
     * @return array{start: float, end: float}|null
     */
    private function trimFor(string $attribute): ?array
    {
        if ($this->format !== ScheduledPost::FORMAT_STORY || ! app(VideoTrimmer::class)->isAvailable()) {
            return null;
        }

        $range = $this->trims[(int) Str::afterLast($attribute, '.')] ?? null;

        if (! is_array($range) || ! is_numeric($range['start'] ?? null) || ! is_numeric($range['end'] ?? null)) {
            return null;
        }

        return ['start' => (float) $range['start'], 'end' => (float) $range['end']];
    }

    /**
     * @param  array{start: float, end: float}  $trim
     */
    private function trimProblem(array $trim, float $duration): ?string
    {
        $min = (int) config('scheduler.video.min_seconds', 3);
        $max = VideoSpec::maxSeconds(ScheduledPost::FORMAT_STORY);
        $length = $trim['end'] - $trim['start'];

        if ($trim['start'] < 0 || $trim['end'] > $duration + 0.5) {
            return __('Rentang potong di luar durasi video.');
        }

        if ($length < $min - 0.05) {
            return __('Bagian yang dipilih terlalu pendek. Minimal :min detik.', ['min' => $min]);
        }

        if ($length > $max + 0.05) {
            return __('Bagian yang dipilih lebih dari :duration. Persempit rentangnya.', ['duration' => VideoSpec::duration($max)]);
        }

        return null;
    }
}
