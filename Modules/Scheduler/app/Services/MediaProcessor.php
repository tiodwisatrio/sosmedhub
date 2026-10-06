<?php

namespace Modules\Scheduler\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use RuntimeException;

/**
 * Mengolah foto unggahan menjadi versi terbit (yang diambil Instagram) dan thumbnail.
 * File asli tidak pernah disimpan: Instagram memperkecil semua foto ke lebar 1080-1440px,
 * jadi mengirim file 5MB tidak menambah kualitas, hanya memperlambat dan menambah risiko gagal.
 */
class MediaProcessor
{
    /**
     * Membaca dimensi tanpa mendekode piksel (murah), sudah disesuaikan orientasi EXIF.
     *
     * @return array{width: int, height: int, format: string}
     */
    public function inspect(string $path): array
    {
        $this->ensureImagick();

        $image = new Imagick;
        $image->pingImage($path);

        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $format = strtoupper($image->getImageFormat());

        // Orientasi 5-8 berarti foto diputar 90 derajat saat ditampilkan.
        if (in_array($image->getImageOrientation(), [5, 6, 7, 8], true)) {
            [$width, $height] = [$height, $width];
        }

        $image->clear();

        return ['width' => $width, 'height' => $height, 'format' => $format];
    }

    /**
     * @return array{media_path: string, thumbnail_path: string, width: int, height: int, size: int}
     */
    public function process(string $sourcePath): array
    {
        $this->ensureImagick();
        $this->limitImagickResources();

        $config = config('scheduler.media');
        $publishWidth = (int) $config['publish_max_width'];

        $image = new Imagick;

        // libjpeg bisa mendekode langsung pada skala 1/2, 1/4, atau 1/8. Foto 50MP tidak perlu
        // dibuka penuh (sekitar 200MB); cukup sedikit di atas ukuran target. Petunjuk ini hanya
        // dipasang untuk foto yang lebih besar dari target, karena libjpeg juga bisa
        // memperbesar (hingga 2x) bila salah satu sisi foto lebih kecil dari petunjuk.
        $hint = $publishWidth;
        $probe = new Imagick;
        $probe->pingImage($sourcePath);
        $shortSide = min($probe->getImageWidth(), $probe->getImageHeight());
        $probe->clear();

        if ($shortSide >= $hint) {
            $image->setOption('jpeg:size', "{$hint}x{$hint}");
        }

        $image->readImage($sourcePath);

        // Diputar setelah didekode kecil, jadi salinan untuk rotasi juga kecil.
        $image->autoOrient();

        if ($image->getImageColorspace() === Imagick::COLORSPACE_CMYK) {
            $image->transformImageColorspace(Imagick::COLORSPACE_SRGB);
        }

        if ($image->getImageWidth() > $publishWidth) {
            $image->resizeImage($publishWidth, 0, Imagick::FILTER_LANCZOS, 1);
        }

        // Hapus semua metadata, termasuk lokasi GPS dan model kamera.
        $image->stripImage();
        $image->setImageFormat('jpeg');
        $image->setImageCompressionQuality((int) $config['publish_quality']);

        $publishBlob = $image->getImageBlob();
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        $thumbnail = clone $image;
        $thumbnail->thumbnailImage((int) $config['thumbnail_width'], 0);
        $thumbnail->setImageCompressionQuality((int) $config['thumbnail_quality']);
        $thumbnailBlob = $thumbnail->getImageBlob();

        $image->clear();
        $thumbnail->clear();

        $name = (string) Str::uuid();
        $mediaPath = "scheduled-posts/{$name}.jpg";
        $thumbnailPath = "scheduled-posts/thumbs/{$name}.jpg";

        $disk = Storage::disk('public');
        $disk->put($mediaPath, $publishBlob);
        $disk->put($thumbnailPath, $thumbnailBlob);

        return [
            'media_path' => $mediaPath,
            'thumbnail_path' => $thumbnailPath,
            'width' => $width,
            'height' => $height,
            'size' => strlen($publishBlob),
        ];
    }

    /**
     * Menyimpan video apa adanya (tanpa transkode). Syaratnya sudah diperiksa saat validasi;
     * di sini hanya metadata yang dicatat untuk tampilan dan pembersihan.
     *
     * @return array{media_path: string, thumbnail_path: null, width: int, height: int, size: int, duration_ms: int, mime: string, type: string}
     */
    public function storeVideo(UploadedFile $file): array
    {
        $info = app(Mp4Inspector::class)->inspect($file->getRealPath());
        $mime = $file->getMimeType() === 'video/quicktime' ? 'video/quicktime' : 'video/mp4';
        $path = 'scheduled-posts/videos/'.Str::uuid().($mime === 'video/quicktime' ? '.mov' : '.mp4');

        // Salin lewat stream: video bisa ratusan MB.
        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk('public')->put($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'media_path' => $path,
            'thumbnail_path' => null,
            'width' => $info['width'],
            'height' => $info['height'],
            'size' => (int) $file->getSize(),
            'duration_ms' => (int) round($info['duration'] * 1000),
            'mime' => $mime,
            'type' => 'video',
        ];
    }

    /**
     * Memori ImageMagick tidak dihitung dalam memory_limit PHP, tetapi tetap dihitung batas
     * proses hosting. Di atas batas ini ImageMagick memakai disk (lebih lambat) alih-alih dibunuh.
     */
    private function limitImagickResources(): void
    {
        $megabytes = (int) config('scheduler.media.imagick_memory_mb', 256);

        Imagick::setResourceLimit(Imagick::RESOURCETYPE_MEMORY, $megabytes * 1024 * 1024);
        Imagick::setResourceLimit(Imagick::RESOURCETYPE_MAP, $megabytes * 2 * 1024 * 1024);
    }

    private function ensureImagick(): void
    {
        if (! extension_loaded('imagick')) {
            throw new RuntimeException('Ekstensi PHP Imagick tidak tersedia. Aktifkan di pengaturan PHP hosting.');
        }
    }
}
