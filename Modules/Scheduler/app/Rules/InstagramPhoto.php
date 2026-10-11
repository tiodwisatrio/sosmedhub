<?php

namespace Modules\Scheduler\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Modules\Scheduler\Services\MediaProcessor;
use Modules\Scheduler\Services\VideoSpec;
use Throwable;

/**
 * Memeriksa foto sejak diunggah agar tidak baru gagal saat jam terbit:
 * batas resolusi (pengaman memori server) dan rasio yang diterima Instagram.
 */
class InstagramPhoto implements ValidationRule
{
    // Instagram menerima rasio dari 4:5 (potret) sampai 1,91:1 (lanskap).
    private const MIN_RATIO = 0.8;

    private const MAX_RATIO = 1.91;

    private const TOLERANCE = 0.01;

    /**
     * @param  bool  $enforceRatio  Story hanya menyarankan 9:16, jadi rasio tidak dipaksa.
     */
    public function __construct(private readonly bool $enforceRatio = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        try {
            $info = app(MediaProcessor::class)->inspect($value->getRealPath());
        } catch (Throwable) {
            $fail(__('Foto tidak bisa dibaca. Pastikan filenya JPEG yang tidak rusak.'));

            return;
        }

        if ($info['format'] !== 'JPEG') {
            $fail(__('Foto harus berformat JPEG (.jpg atau .jpeg).'));

            return;
        }

        $maxMegapixels = (int) config('scheduler.media.max_megapixels', 50);
        $megapixels = $info['width'] * $info['height'] / 1_000_000;

        if ($megapixels > $maxMegapixels) {
            $fail(__('Resolusi foto terlalu besar (:mp MP). Maksimal :max MP; kecilkan dulu sebelum diunggah.', [
                'mp' => VideoSpec::number($megapixels),
                'max' => $maxMegapixels,
            ]));

            return;
        }

        if (! $this->enforceRatio) {
            return;
        }

        $ratio = $info['width'] / max($info['height'], 1);

        if ($ratio < self::MIN_RATIO - self::TOLERANCE || $ratio > self::MAX_RATIO + self::TOLERANCE) {
            $fail(__('Rasio foto :width×:height tidak diterima Instagram. Gunakan rasio antara 4:5 (potret) dan 1,91:1 (lanskap), misalnya 1:1 atau 4:5.', [
                'width' => $info['width'],
                'height' => $info['height'],
            ]));
        }
    }
}
