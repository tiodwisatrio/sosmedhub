<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostMedia;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\SocialAccount\Exceptions\InstagramAuthException;
use Modules\SocialAccount\Services\InstagramPublisher;
use RuntimeException;
use Throwable;

/**
 * Menjalankan penerbitan satu format (Feed, Story, atau Reels) langkah demi langkah:
 * membuat container, menunggu Instagram selesai memprosesnya, lalu menerbitkan.
 *
 * Dipanggil berulang oleh job. Kemajuan disimpan di kolom state, jadi pemanggilan berikutnya
 * melanjutkan tanpa membuat container ganda atau menerbitkan dua kali. Video diproses
 * Instagram dalam hitungan menit: runner mengembalikan WAITING agar job melepas diri dan
 * dijadwalkan ulang, bukan menahan worker.
 */
class PublicationRunner
{
    // Batas waktu satu pemanggilan, di bawah batas waktu job.
    private const BUDGET_SECONDS = 100;

    public function __construct(private readonly InstagramPublisher $publisher) {}

    public function advance(ScheduledPostPublication $publication): PublicationResult
    {
        $post = $publication->scheduledPost()->with(['socialAccount'])->first();
        $account = $post->socialAccount;

        try {
            if (! $account?->isActive() || ! $account->access_token) {
                throw new RuntimeException('Akun Instagram tidak aktif atau token tidak tersedia.');
            }

            $media = $post->media()->where('format', $publication->format)->orderBy('position')->get();
            $this->assertMedia($publication->format, $media);

            $state = $this->state($publication, $media);

            if ($publication->status !== ScheduledPostPublication::STATUS_PUBLISHING) {
                $publication->update(['status' => ScheduledPostPublication::STATUS_PUBLISHING]);
            }

            $result = $this->step($publication, $post, $account, $media, $state);
        } catch (InstagramAuthException $e) {
            $this->fail($publication, $e->getMessage());

            return PublicationResult::failed(authFailed: true);
        } catch (Throwable $e) {
            report($e);
            $this->fail($publication, $e->getMessage());

            return PublicationResult::failed();
        }

        return $result;
    }

    private function step($publication, ScheduledPost $post, $account, $media, array $state): PublicationResult
    {
        $started = microtime(true);
        $deadline = Carbon::parse($state['started_at'])->addMinutes((int) config('scheduler.video.poll_max_minutes', 10));

        // Semua container harus FINISHED sebelum ada yang diterbitkan.
        $hasVideo = $media->contains(fn (ScheduledPostMedia $m) => $m->isVideo());
        $attempts = max(1, (int) config('social-account.instagram.status_poll_attempts', 10));
        $seconds = (int) config('social-account.instagram.status_poll_seconds', 3);

        for ($i = 0; $i < $attempts; $i++) {
            // Idempotent; carousel induk baru dibuat setelah semua anaknya selesai diproses.
            $this->createContainers($publication, $post, $media, $state, $account);

            $notReady = ! $this->allContainersCreated($state);

            foreach ($this->pendingContainerIds($state) as $containerId) {
                $status = $this->publisher->containerStatus($account, $containerId);

                if (in_array($status['code'], ['ERROR', 'EXPIRED'], true)) {
                    throw new RuntimeException($this->statusProblem($status));
                }

                $notReady = $notReady || ! in_array($status['code'], ['FINISHED', 'PUBLISHED'], true);
            }

            if (! $notReady) {
                break;
            }

            if ($hasVideo || (microtime(true) - $started) > self::BUDGET_SECONDS || $i === $attempts - 1) {
                if (now()->greaterThan($deadline)) {
                    throw new RuntimeException('Instagram belum selesai memproses media dalam '.(int) config('scheduler.video.poll_max_minutes', 10).' menit. Coba jadwalkan ulang.');
                }

                $this->save($publication, $state);

                return PublicationResult::waiting();
            }

            if ($seconds > 0) {
                sleep($seconds);
            }
        }

        $this->publishItems($publication, $account, $state);

        return PublicationResult::done();
    }

    /**
     * Membuat container yang belum ada. Disimpan segera setelah tiap pembuatan.
     */
    private function createContainers($publication, ScheduledPost $post, $media, array &$state, $account): void
    {
        $caption = $post->caption;

        switch ($publication->format) {
            case ScheduledPost::FORMAT_STORY:
                foreach ($media as $index => $item) {
                    if ($state['items'][$index]['published_id'] || $state['items'][$index]['container_id']) {
                        continue;
                    }

                    $state['items'][$index]['container_id'] = $this->publisher->createContainer($account, [
                        'media_type' => 'STORIES',
                        $item->isVideo() ? 'video_url' : 'image_url' => $this->url($item),
                    ]);
                    $this->save($publication, $state);
                }

                return;

            case ScheduledPost::FORMAT_REEL:
                if ($state['items'][0]['published_id'] || $state['items'][0]['container_id']) {
                    return;
                }

                $state['items'][0]['container_id'] = $this->publisher->createContainer($account, [
                    'media_type' => 'REELS',
                    'video_url' => $this->url($media->first()),
                    'caption' => $caption,
                    'share_to_feed' => $publication->share_to_feed ? 'true' : 'false',
                ]);
                $this->save($publication, $state);

                return;

            default:
                if ($state['items'][0]['published_id'] || $state['items'][0]['container_id']) {
                    return;
                }

                if ($media->count() === 1) {
                    $state['items'][0]['container_id'] = $this->publisher->createContainer($account, [
                        'image_url' => $this->url($media->first()),
                        'caption' => $caption,
                    ]);
                    $this->save($publication, $state);

                    return;
                }

                foreach ($media as $index => $item) {
                    if ($state['children'][$index]['container_id']) {
                        continue;
                    }

                    $state['children'][$index]['container_id'] = $this->publisher->createContainer($account, [
                        'image_url' => $this->url($item),
                        'is_carousel_item' => 'true',
                    ]);
                    $this->save($publication, $state);
                }

                // Induk carousel baru dibuat setelah semua container anak FINISHED.
                foreach ($state['children'] as $child) {
                    if ($this->publisher->containerStatus($account, $child['container_id'])['code'] !== 'FINISHED') {
                        return;
                    }
                }

                $state['items'][0]['container_id'] = $this->publisher->createContainer($account, [
                    'media_type' => 'CAROUSEL',
                    'children' => implode(',', array_column($state['children'], 'container_id')),
                    'caption' => $caption,
                ]);
                $this->save($publication, $state);
        }
    }

    private function allContainersCreated(array $state): bool
    {
        foreach ($state['items'] as $item) {
            if (! $item['container_id'] && ! $item['published_id']) {
                return false;
            }
        }

        return true;
    }

    private function publishItems($publication, $account, array &$state): void
    {
        foreach ($state['items'] as $index => $item) {
            if ($item['published_id']) {
                continue;
            }

            $state['items'][$index]['published_id'] = $this->publisher->publishContainer($account, $item['container_id']);
            // Segera disimpan: bila proses mati setelah ini, item ini tidak diterbitkan dua kali.
            $this->save($publication, $state);
        }

        $publication->update([
            'status' => ScheduledPostPublication::STATUS_PUBLISHED,
            'ig_media_id' => $state['items'][0]['published_id'],
            'error_message' => null,
            'published_at' => now(),
            'state' => $state,
        ]);
    }

    /**
     * Container yang belum diterbitkan dan harus diperiksa statusnya.
     *
     * @return list<string>
     */
    private function pendingContainerIds(array $state): array
    {
        $ids = [];

        foreach ($state['children'] ?? [] as $child) {
            if ($child['container_id']) {
                $ids[] = $child['container_id'];
            }
        }

        foreach ($state['items'] as $item) {
            if ($item['container_id'] && ! $item['published_id']) {
                $ids[] = $item['container_id'];
            }
        }

        return $ids;
    }

    /**
     * Kerangka state sesuai media saat ini. Item yang sudah terbit dipertahankan
     * (dicocokkan lewat ID media), supaya jadwal ulang tidak menerbitkan ulang item itu.
     */
    private function state(ScheduledPostPublication $publication, $media): array
    {
        $old = $publication->state ?? [];
        $oldItems = collect($old['items'] ?? [])->keyBy(fn ($item) => $item['media_id'] ?? 'post');
        $oldChildren = collect($old['children'] ?? [])->keyBy('media_id');

        $items = $publication->format === ScheduledPost::FORMAT_STORY
            ? $media->map(fn ($m) => $oldItems->get($m->id) ?? ['media_id' => $m->id, 'container_id' => null, 'published_id' => null])->values()->all()
            : [$oldItems->get('post') ?? $oldItems->first() ?? ['media_id' => null, 'container_id' => null, 'published_id' => null]];

        $children = $publication->format === ScheduledPost::FORMAT_FEED && $media->count() > 1
            ? $media->map(fn ($m) => $oldChildren->get($m->id) ?? ['media_id' => $m->id, 'container_id' => null])->values()->all()
            : [];

        return [
            'started_at' => $old['started_at'] ?? now()->toIso8601String(),
            'items' => $items,
            'children' => $children,
        ];
    }

    private function assertMedia(string $format, $media): void
    {
        $label = ScheduledPost::formatLabel($format);

        if ($media->isEmpty()) {
            throw new RuntimeException("{$label} tidak memiliki media. Unggah ulang medianya.");
        }

        if ($media->contains(fn (ScheduledPostMedia $m) => ! $m->media_path || ! Storage::disk('public')->exists($m->media_path))) {
            throw new RuntimeException("Sebagian media {$label} sudah tidak tersedia di server. Unggah ulang medianya.");
        }
    }

    private function url(ScheduledPostMedia $media): string
    {
        return Storage::disk('public')->url($media->media_path);
    }

    private function statusProblem(array $status): string
    {
        $detail = $status['detail'] ? ' ('.$status['detail'].')' : '';

        return $status['code'] === 'EXPIRED'
            ? 'Container Instagram kedaluwarsa sebelum diterbitkan. Coba jadwalkan ulang.'
            : "Instagram gagal memproses media{$detail}.";
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
