<?php

namespace Modules\Scheduler\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya tempat yang menentukan disk penyimpanan media jadwal (foto, thumbnail, video).
 * Diatur lewat SCHEDULER_MEDIA_DISK; bawaannya "public" (lokal). Untuk object storage
 * (S3, Cloudflare R2) cukup arahkan ke disk S3 di config/filesystems.php.
 *
 * Path yang disimpan di database relatif terhadap disk ini, jadi berpindah disk tidak mengubah data.
 */
final class MediaDisk
{
    public static function name(): string
    {
        return (string) config('scheduler.media.disk', 'public');
    }

    public static function get(): Filesystem
    {
        return Storage::disk(self::name());
    }

    public static function url(string $path): string
    {
        return self::get()->url($path);
    }
}
