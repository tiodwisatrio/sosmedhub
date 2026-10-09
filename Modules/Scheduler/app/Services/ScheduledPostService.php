<?php

namespace Modules\Scheduler\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Models\ScheduledPostPublication;
use RuntimeException;

class ScheduledPostService
{
    public function __construct(private readonly MediaProcessor $processor, private readonly VideoTrimmer $trimmer) {}

    /**
     * @param  array  $data  caption, scheduled_at, social_account_id, serta opsional formats (list) dan share_to_feed
     * @param  array|null  $media  daftar file (dianggap Feed) atau ['feed' => [...], 'story' => [...], 'reel' => [...]]
     */
    public function store(array $data, ?array $media, int $userId): ScheduledPost
    {
        $formats = $this->formatsFrom($data) ?: [ScheduledPost::FORMAT_FEED];
        $shareToFeed = (bool) ($data['share_to_feed'] ?? true);
        $order = (array) ($data['order'] ?? []);
        $trims = (array) ($data['trims'] ?? []);
        unset($data['formats'], $data['share_to_feed'], $data['order'], $data['trims']);

        $data['user_id'] = $userId;
        $data['caption'] = (string) ($data['caption'] ?? '');
        $data['status'] = ScheduledPost::STATUS_SCHEDULED;
        $data['scheduled_at'] = $this->toUtc($data['scheduled_at']);

        return DB::transaction(function () use ($data, $media, $formats, $shareToFeed, $order, $trims) {
            $post = ScheduledPost::create($data);

            foreach ($formats as $format) {
                $post->publications()->create([
                    'format' => $format,
                    'status' => ScheduledPostPublication::STATUS_PENDING,
                    'share_to_feed' => $shareToFeed,
                ]);
            }

            foreach ($this->normalizeMedia($media) as $format => $files) {
                if (in_array($format, $formats, true)) {
                    $created = $this->appendMedia($post, $format, $files, $trims[$format] ?? []);
                    $this->applyOrder($post, $format, $order[$format] ?? null, $created);
                }
            }

            return $post;
        });
    }

    public function update(ScheduledPost $post, array $data, ?array $media, ?array $removeMediaIds = []): void
    {
        $formats = $this->formatsFrom($data);
        $shareToFeed = array_key_exists('share_to_feed', $data) ? (bool) $data['share_to_feed'] : null;
        $order = (array) ($data['order'] ?? []);
        $trims = (array) ($data['trims'] ?? []);
        unset($data['formats'], $data['share_to_feed'], $data['order'], $data['trims']);

        $data['scheduled_at'] = $this->toUtc($data['scheduled_at']);
        $data['caption'] = (string) ($data['caption'] ?? '');

        DB::transaction(function () use ($post, $data, $media, $removeMediaIds, $formats, $shareToFeed, $order, $trims) {
            $post->fill($data);

            // Post gagal, sebagian terbit, atau draf yang disimpan ulang masuk antrean lagi.
            if ($post->status !== ScheduledPost::STATUS_SCHEDULED) {
                $post->status = ScheduledPost::STATUS_SCHEDULED;
                $post->error_message = null;
            }

            $post->save();

            $locked = $post->publications()->where('status', ScheduledPostPublication::STATUS_PUBLISHED)->pluck('format')->all();

            if ($formats !== []) {
                $this->syncPublications($post, array_values(array_unique([...$formats, ...$locked])), $shareToFeed);
            } elseif ($post->publications()->doesntExist()) {
                $post->publications()->create(['format' => ScheduledPost::FORMAT_FEED]);
            }

            if ($removeMediaIds) {
                $this->deleteMedia($post, $removeMediaIds, except: $locked);
            }

            $createdByFormat = [];

            foreach ($this->normalizeMedia($media) as $format => $files) {
                if (! in_array($format, $locked, true) && $post->publications()->where('format', $format)->exists()) {
                    $createdByFormat[$format] = $this->appendMedia($post, $format, $files, $trims[$format] ?? []);
                }
            }

            // Urutan dari form (seret-lepas): campuran media lama dan baru.
            foreach ($order as $format => $tokens) {
                if (! in_array($format, $locked, true) && $post->publications()->where('format', $format)->exists()) {
                    $this->applyOrder($post, $format, $tokens, $createdByFormat[$format] ?? []);
                }
            }

            // Publikasi yang gagal diulang dari awal; yang sudah terbit tidak disentuh.
            $post->publications()
                ->where('status', ScheduledPostPublication::STATUS_FAILED)
                ->get()
                ->each->resetForRetry();
        });
    }

    /**
     * Salin caption, akun tujuan, format, dan media menjadi draf baru. Waktu terbit diisi slot
     * besok sebagai usulan. Media yang versi terbitnya sudah dihapus tidak ikut tersalin;
     * thumbnail saja terlalu kecil untuk diterbitkan ulang.
     */
    public function duplicate(ScheduledPost $post): ScheduledPost
    {
        $post->loadMissing(['media', 'publications']);

        $copy = DB::transaction(function () use ($post) {
            $copy = ScheduledPost::create([
                'user_id' => $post->user_id,
                'social_account_id' => $post->social_account_id,
                'caption' => $post->caption,
                'scheduled_at' => ScheduledPost::nextSlot(now()->addDay()),
                'status' => ScheduledPost::STATUS_DRAFT,
            ]);

            foreach ($post->formats() as $format) {
                $copy->publications()->create([
                    'format' => $format,
                    'share_to_feed' => $post->publications->firstWhere('format', $format)?->share_to_feed ?? true,
                ]);
            }

            return $copy;
        });

        $disk = Storage::disk('public');

        foreach ($post->media as $item) {
            if (! $item->media_path || ! $disk->exists($item->media_path)) {
                continue;
            }

            $name = (string) Str::uuid();
            $extension = pathinfo($item->media_path, PATHINFO_EXTENSION) ?: 'jpg';
            $folder = $item->isVideo() ? 'scheduled-posts/videos' : 'scheduled-posts';
            $newPath = "{$folder}/{$name}.{$extension}";
            $disk->copy($item->media_path, $newPath);

            $newThumbnail = null;
            if ($item->thumbnail_path && $disk->exists($item->thumbnail_path)) {
                $newThumbnail = "scheduled-posts/thumbs/{$name}.jpg";
                $disk->copy($item->thumbnail_path, $newThumbnail);
            }

            $copy->media()->create([
                'format' => $item->format,
                'type' => $item->type,
                'media_path' => $newPath,
                'thumbnail_path' => $newThumbnail,
                'width' => $item->width,
                'height' => $item->height,
                'size' => $item->size,
                'duration_ms' => $item->duration_ms,
                'mime' => $item->mime,
                'position' => $item->position,
            ]);
        }

        return $copy;
    }

    public function cancel(ScheduledPost $post): void
    {
        $post->status = ScheduledPost::STATUS_CANCELLED;
        $post->save();
    }

    /**
     * @param  list<string>  $except  format yang tidak boleh disentuh (sudah terbit)
     */
    public function deleteMedia(ScheduledPost $post, ?array $mediaIds = null, array $except = []): void
    {
        $media = $post->media()
            ->when($mediaIds, fn ($q) => $q->whereIn('id', $mediaIds))
            ->when($except, fn ($q) => $q->whereNotIn('format', $except))
            ->get();

        foreach ($media as $item) {
            Storage::disk('public')->delete(array_filter([$item->media_path, $item->thumbnail_path]));
            $item->delete();
        }

        $this->renumberPositions($post);
    }

    /**
     * Menyamakan publikasi dengan format yang dipilih. Format yang dihapus ikut menghapus
     * media-nya, kecuali sudah terbit.
     *
     * @param  list<string>  $formats
     */
    private function syncPublications(ScheduledPost $post, array $formats, ?bool $shareToFeed): void
    {
        $existing = $post->publications()->get()->keyBy('format');

        foreach ($existing as $format => $publication) {
            if (! in_array($format, $formats, true) && ! $publication->isPublished()) {
                $this->deleteFormatMedia($post, $format);
                $publication->delete();
            }
        }

        foreach ($formats as $format) {
            if (! $existing->has($format)) {
                $post->publications()->create(['format' => $format, 'share_to_feed' => $shareToFeed ?? true]);
            }
        }

        if ($shareToFeed !== null) {
            $post->publications()
                ->where('format', ScheduledPost::FORMAT_REEL)
                ->where('status', '!=', ScheduledPostPublication::STATUS_PUBLISHED)
                ->update(['share_to_feed' => $shareToFeed]);
        }
    }

    private function deleteFormatMedia(ScheduledPost $post, string $format): void
    {
        $post->media()->where('format', $format)->get()->each(function (ScheduledPostMedia $item) {
            Storage::disk('public')->delete(array_filter([$item->media_path, $item->thumbnail_path]));
            $item->delete();
        });
    }

    /**
     * @param  list<UploadedFile>  $files
     * @param  array<int, array{start: float, end: float}>  $trims  rentang potong per urutan file (hanya video Story)
     * @return list<int> ID media yang dibuat, berurutan sesuai urutan unggah
     */
    private function appendMedia(ScheduledPost $post, string $format, array $files, array $trims = []): array
    {
        $created = [];
        $nextPosition = ($post->media()->where('format', $format)->max('position') ?? -1) + 1;

        foreach (array_values($files) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

            // File asli hanya dibaca dari lokasi sementara upload; yang disimpan versi olahan.
            $stored = $isVideo
                ? $this->storeVideo($file, $trims[$index] ?? null)
                : [...$this->processor->process($file->getRealPath()), 'type' => 'image', 'mime' => 'image/jpeg'];

            $created[] = $post->media()->create([
                ...$stored,
                'format' => $format,
                'position' => $nextPosition + $index,
            ])->id;
        }

        return $created;
    }

    /**
     * Menyimpan video; bila ada rentang potong, video dipotong dulu dan yang disimpan hanya potongannya.
     *
     * @param  array{start: float, end: float}|null  $trim
     */
    private function storeVideo(UploadedFile $file, ?array $trim): array
    {
        if ($trim === null) {
            return $this->processor->storeVideo($file);
        }

        $path = $this->trimmer->trim($file->getRealPath(), $trim['start'], $trim['end']);

        try {
            $stored = $this->processor->storeVideo(new UploadedFile($path, $file->getClientOriginalName(), 'video/mp4', null, true));

            // Hasil potongan harus memenuhi syarat Story; Instagram menolak yang lebih besar dari batas.
            if ($stored['size'] > VideoSpec::maxBytes(ScheduledPost::FORMAT_STORY)) {
                Storage::disk('public')->delete($stored['media_path']);

                throw new RuntimeException('Hasil potongan lebih besar dari batas '.(VideoSpec::maxBytes(ScheduledPost::FORMAT_STORY) / 1048576).' MB untuk Story. Pilih rentang yang lebih pendek.');
            }

            return $stored;
        } finally {
            @unlink($path);
        }
    }

    /**
     * Menyimpan urutan media satu format sesuai urutan dari form. "e:ID" menunjuk media yang sudah
     * ada (hanya milik jadwal dan format ini), "n:N" menunjuk file unggahan ke-N. Media yang tidak
     * disebut tetap ada, ditaruh di belakang menurut urutan sebelumnya.
     *
     * @param  list<string>|null  $tokens
     * @param  list<int>  $created
     */
    private function applyOrder(ScheduledPost $post, string $format, ?array $tokens, array $created): void
    {
        if ($tokens === null) {
            return;
        }

        $media = $post->media()->where('format', $format)->get()->keyBy('id');
        $sequence = [];

        foreach ($tokens as $token) {
            [$kind, $number] = explode(':', $token) + [1 => '-1'];
            $id = $kind === 'e' ? (int) $number : ($created[(int) $number] ?? null);

            if ($id && $media->has($id) && ! in_array($id, $sequence, true)) {
                $sequence[] = $id;
            }
        }

        foreach ($media->sortBy('position') as $item) {
            if (! in_array($item->id, $sequence, true)) {
                $sequence[] = $item->id;
            }
        }

        foreach ($sequence as $position => $id) {
            if ($media[$id]->position !== $position) {
                $media[$id]->update(['position' => $position]);
            }
        }
    }

    /**
     * @return array<string, list<UploadedFile>>
     */
    private function normalizeMedia(?array $media): array
    {
        if (! $media) {
            return [];
        }

        // Daftar file biasa (cara lama) berarti media Feed.
        if (array_is_list($media)) {
            return [ScheduledPost::FORMAT_FEED => $media];
        }

        return array_intersect_key($media, array_flip(ScheduledPost::FORMATS));
    }

    /**
     * @return list<string>
     */
    private function formatsFrom(array $data): array
    {
        $formats = $data['formats'] ?? [];

        return is_array($formats)
            ? array_values(array_intersect(ScheduledPost::FORMATS, array_map('strval', $formats)))
            : [];
    }

    private function renumberPositions(ScheduledPost $post): void
    {
        $post->media()->get()->groupBy('format')->each(function ($items) {
            $items->sortBy('position')->values()->each(fn ($item, $index) => $item->update(['position' => $index]));
        });
    }

    /**
     * Waktu dari antarmuka ditulis dalam WIB; disimpan UTC.
     */
    private function toUtc(string $value): Carbon
    {
        return Carbon::parse($value, ScheduledPost::WIB)->utc();
    }
}
