<?php

namespace Modules\Scheduler\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;

class ScheduledPostService
{
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
        $post->save();

        if ($removeMediaIds) {
            $this->deleteMedia($post, $removeMediaIds);
        }

        if ($media) {
            $this->appendMedia($post, $media);
        }
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
            Storage::disk('public')->delete($item->media_path);
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

            $post->media()->create([
                'media_path' => $file->store('scheduled-posts', 'public'),
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
