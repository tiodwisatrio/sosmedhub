<?php

namespace Modules\Scheduler\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Scheduler\Models\ScheduledPost;

class ScheduledPostService
{
    public function __construct(private readonly MediaProcessor $processor) {}

    public function store(array $data, ?array $media, int $userId): ScheduledPost
    {
        $data['user_id'] = $userId;
        $data['status'] = ScheduledPost::STATUS_SCHEDULED;
        $data['scheduled_at'] = $this->toUtc($data['scheduled_at']);

        $post = ScheduledPost::create($data);

        $this->appendMedia($post, $media ?? []);

        return $post;
    }

    public function update(ScheduledPost $post, array $data, ?array $media, ?array $removeMediaIds = []): void
    {
        $data['scheduled_at'] = $this->toUtc($data['scheduled_at']);

        $post->fill($data);

        // Post gagal atau draf yang disimpan ulang masuk antrean lagi.
        if ($post->status !== ScheduledPost::STATUS_SCHEDULED) {
            $post->status = ScheduledPost::STATUS_SCHEDULED;
            $post->error_message = null;
        }

        $post->save();

        if ($removeMediaIds) {
            $this->deleteMedia($post, $removeMediaIds);
        }

        if ($media) {
            $this->appendMedia($post, $media);
        }
    }

    /**
     * Salin caption, akun tujuan, dan foto menjadi draf baru. Waktu terbit diisi slot
     * besok sebagai usulan. Foto yang versi terbitnya sudah dihapus (lewat masa simpan)
     * tidak ikut tersalin; thumbnail saja terlalu kecil untuk diterbitkan ulang.
     */
    public function duplicate(ScheduledPost $post): ScheduledPost
    {
        $copy = ScheduledPost::create([
            'user_id' => $post->user_id,
            'social_account_id' => $post->social_account_id,
            'caption' => $post->caption,
            'scheduled_at' => ScheduledPost::nextSlot(now()->addDay()),
            'status' => ScheduledPost::STATUS_DRAFT,
        ]);

        $disk = Storage::disk('public');

        foreach ($post->media as $item) {
            if (! $item->media_path || ! $disk->exists($item->media_path)) {
                continue;
            }

            $name = (string) Str::uuid();
            $extension = pathinfo($item->media_path, PATHINFO_EXTENSION) ?: 'jpg';
            $newPath = "scheduled-posts/{$name}.{$extension}";
            $disk->copy($item->media_path, $newPath);

            $newThumbnail = null;
            if ($item->thumbnail_path && $disk->exists($item->thumbnail_path)) {
                $newThumbnail = "scheduled-posts/thumbs/{$name}.jpg";
                $disk->copy($item->thumbnail_path, $newThumbnail);
            }

            $copy->media()->create([
                'media_path' => $newPath,
                'thumbnail_path' => $newThumbnail,
                'width' => $item->width,
                'height' => $item->height,
                'size' => $item->size,
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

    public function deleteMedia(ScheduledPost $post, ?array $mediaIds = null): void
    {
        $media = $post->media()->when($mediaIds, fn ($q) => $q->whereIn('id', $mediaIds))->get();

        foreach ($media as $item) {
            Storage::disk('public')->delete(array_filter([$item->media_path, $item->thumbnail_path]));
            $item->delete();
        }

        $this->renumberPositions($post);
    }

    private function appendMedia(ScheduledPost $post, array $files): void
    {
        $nextPosition = ($post->media()->max('position') ?? -1) + 1;

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            // File asli hanya dibaca dari lokasi sementara upload; yang disimpan versi olahan.
            $post->media()->create([
                ...$this->processor->process($file->getRealPath()),
                'position' => $nextPosition + $index,
            ]);
        }
    }

    private function renumberPositions(ScheduledPost $post): void
    {
        $post->media->each(function ($item, $index) {
            $item->update(['position' => $index]);
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
