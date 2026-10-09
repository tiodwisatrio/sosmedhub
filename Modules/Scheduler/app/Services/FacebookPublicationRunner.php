<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\SocialAccount\Exceptions\FacebookAuthException;
use Modules\SocialAccount\Services\FacebookPublisher;
use RuntimeException;
use Throwable;

/**
 * Menerbitkan Feed ke Page Facebook. Satu foto langsung terbit lewat /photos; beberapa foto
 * diunggah dulu tanpa terbit, lalu digabung dalam satu postingan lewat /feed.
 *
 * Facebook memproses foto saat itu juga, jadi tidak pernah mengembalikan WAITING. Kemajuan
 * disimpan di state (bentuknya sama dengan Instagram) supaya pemanggilan ulang tidak
 * menerbitkan dua kali: ID foto tersimpan per foto, dan ID postingan disimpan segera setelah terbit.
 */
class FacebookPublicationRunner
{
    public function __construct(private readonly FacebookPublisher $publisher) {}

    public function advance(ScheduledPostPublication $publication): PublicationResult
    {
        $post = $publication->scheduledPost()->with(['socialAccount'])->first();
        $account = $post->socialAccount;

        try {
            if (! $account?->isActive() || ! $account->access_token) {
                throw new RuntimeException('Akun Facebook tidak aktif atau token tidak tersedia.');
            }

            if ($publication->format !== ScheduledPost::FORMAT_FEED) {
                throw new RuntimeException('Facebook baru mendukung format Feed.');
            }

            $media = $post->media()->where('format', $publication->format)->orderBy('position')->get();
            $this->assertMedia($media);

            if ($publication->status !== ScheduledPostPublication::STATUS_PUBLISHING) {
                $publication->update(['status' => ScheduledPostPublication::STATUS_PUBLISHING]);
            }

            $state = $this->state($publication, $media);

            if (! $state['items'][0]['published_id']) {
                $state['items'][0]['published_id'] = $media->count() === 1
                    ? $this->publishSingle($account, $post, $media->first())
                    : $this->publishAlbum($publication, $account, $post, $media, $state);
                $this->save($publication, $state);
            }

            $publication->update([
                'status' => ScheduledPostPublication::STATUS_PUBLISHED,
                'ig_media_id' => $state['items'][0]['published_id'],
                'error_message' => null,
                'published_at' => now(),
                'state' => $state,
            ]);
        } catch (FacebookAuthException $e) {
            $this->fail($publication, $e->getMessage());

            return PublicationResult::failed(authFailed: true);
        } catch (Throwable $e) {
            report($e);
            $this->fail($publication, $e->getMessage());

            return PublicationResult::failed();
        }

        return PublicationResult::done();
    }

    private function publishSingle($account, ScheduledPost $post, $item): string
    {
        $photo = $this->publisher->uploadPhoto($account, $this->path($item), $post->caption, published: true);

        return $photo['post_id'] ?: $photo['id'];
    }

    private function publishAlbum($publication, $account, ScheduledPost $post, $media, array &$state): string
    {
        foreach ($media as $index => $item) {
            if ($state['children'][$index]['container_id']) {
                continue;
            }

            $state['children'][$index]['container_id'] = $this->publisher
                ->uploadPhoto($account, $this->path($item), null, published: false)['id'];
            $this->save($publication, $state);
        }

        return $this->publisher->publishPost($account, $post->caption, array_column($state['children'], 'container_id'));
    }

    /**
     * Kerangka state sesuai media saat ini; ID foto yang sudah terunggah dipertahankan
     * (dicocokkan lewat ID media) dan ID postingan yang sudah terbit tidak hilang.
     */
    private function state(ScheduledPostPublication $publication, $media): array
    {
        $old = $publication->state ?? [];
        $oldChildren = collect($old['children'] ?? [])->keyBy('media_id');

        return [
            'started_at' => $old['started_at'] ?? now()->toIso8601String(),
            'items' => [$old['items'][0] ?? ['media_id' => null, 'container_id' => null, 'published_id' => null]],
            'children' => $media->count() > 1
                ? $media->map(fn ($m) => $oldChildren->get($m->id) ?? ['media_id' => $m->id, 'container_id' => null])->values()->all()
                : [],
        ];
    }

    private function assertMedia($media): void
    {
        if ($media->isEmpty()) {
            throw new RuntimeException('Feed tidak memiliki media. Unggah ulang medianya.');
        }

        if ($media->contains(fn ($m) => $m->isVideo())) {
            throw new RuntimeException('Facebook baru mendukung foto untuk Feed.');
        }

        if ($media->contains(fn ($m) => ! $m->media_path || ! Storage::disk('public')->exists($m->media_path))) {
            throw new RuntimeException('Sebagian media Feed sudah tidak tersedia di server. Unggah ulang medianya.');
        }
    }

    private function path($item): string
    {
        return Storage::disk('public')->path($item->media_path);
    }

    private function save(ScheduledPostPublication $publication, array $state): void
    {
        $publication->update(['state' => $state]);
    }

    private function fail(ScheduledPostPublication $publication, string $message): void
    {
        $publication->update([
            'status' => ScheduledPostPublication::STATUS_FAILED,
            'error_message' => mb_substr($message, 0, 500),
        ]);
    }
}
