<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\SocialAccount\Exceptions\FacebookAuthException;
use Modules\SocialAccount\Services\FacebookPublisher;
use RuntimeException;
use Throwable;

/**
 * Menerbitkan Feed (foto), Story (foto atau video), dan Reels ke Page Facebook. Feed: satu foto langsung terbit
 * lewat /photos; beberapa foto diunggah dulu tanpa terbit, lalu digabung dalam satu postingan lewat /feed.
 * Story: foto lewat /photo_stories, video lewat /video_stories (start, unggah ke rupload, cek
 * unggahan lengkap, finish); tanpa caption.
 *
 * Foto diproses Facebook saat itu juga. Hanya video Story yang bisa mengembalikan WAITING, saat
 * unggahan belum tercatat lengkap di Facebook. Kemajuan disimpan di state (bentuknya sama dengan Instagram)
 * supaya pemanggilan ulang tidak menerbitkan dua kali: ID unggahan tersimpan per item, dan ID
 * postingan disimpan segera setelah terbit.
 */
class FacebookPublicationRunner
{
    // Lama menunggu konfirmasi "terbit" dari Facebook setelah finish Reels sebelum dianggap terbit.
    private const REEL_CONFIRM_SECONDS = 120;

    public function __construct(private readonly FacebookPublisher $publisher) {}

    public function advance(ScheduledPostPublication $publication): PublicationResult
    {
        $post = $publication->scheduledPost()->with(['socialAccount'])->first();
        $account = $post->socialAccount;

        try {
            if (! $account?->isActive() || ! $account->access_token) {
                throw new RuntimeException('Akun Facebook tidak aktif atau token tidak tersedia.');
            }

            if (! in_array($publication->format, ScheduledPost::FORMATS, true)) {
                throw new RuntimeException('Format postingan tidak dikenal.');
            }

            $media = $post->media()->where('format', $publication->format)->orderBy('position')->get();
            $this->assertMedia($media, $publication->format);

            if ($publication->status !== ScheduledPostPublication::STATUS_PUBLISHING) {
                $publication->update(['status' => ScheduledPostPublication::STATUS_PUBLISHING]);
            }

            $state = $this->state($publication, $media);

            if ($publication->format === ScheduledPost::FORMAT_STORY) {
                if (! $this->publishStories($publication, $account, $media, $state)) {
                    // Video masih diproses Facebook: simpan kemajuan, job melepas diri lalu dicoba lagi.
                    $this->save($publication, $state);

                    return PublicationResult::waiting();
                }
            } elseif ($publication->format === ScheduledPost::FORMAT_REEL) {
                if (! $state['items'][0]['published_id']
                    && ! $this->publishReel($publication, $account, $post, $media->first(), $state)) {
                    $this->save($publication, $state);

                    return PublicationResult::waiting();
                }
            } elseif (! $state['items'][0]['published_id']) {
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
     * Story: satu foto atau video satu Story. ID unggahan (container_id) dan ID Story (published_id)
     * disimpan per item segera setelah didapat, jadi pemanggilan ulang tidak mengunggah ulang atau
     * menerbitkan Story yang sudah terbit. Item diproses berurutan; video yang belum selesai
     * diproses menghentikan putaran ini.
     *
     * @return bool false bila ada video yang masih diproses Facebook (tunggu, lalu panggil lagi)
     */
    private function publishStories($publication, $account, $media, array &$state): bool
    {
        foreach ($media as $index => $item) {
            if ($state['items'][$index]['published_id']) {
                continue;
            }

            $published = $item->isVideo()
                ? $this->publishVideoStory($publication, $account, $item, $state, $index)
                : $this->publishPhotoStory($publication, $account, $item, $state, $index);

            if (! $published) {
                return false;
            }
        }

        return true;
    }

    /**
     * Foto: diunggah tanpa terbit lalu diterbitkan lewat /photo_stories (langsung jadi).
     */
    private function publishPhotoStory($publication, $account, $item, array &$state, int $index): bool
    {
        if (! $state['items'][$index]['container_id']) {
            $state['items'][$index]['container_id'] = $this->publisher
                ->uploadPhoto($account, $this->path($item), null, published: false)['id'];
            $this->save($publication, $state);
        }

        $state['items'][$index]['published_id'] = $this->publisher
            ->publishPhotoStory($account, $state['items'][$index]['container_id']);
        $this->save($publication, $state);

        return true;
    }

    /**
     * Video Story: unggah sampai lengkap, lalu terbitkan (finish). Pemrosesan di Facebook baru
     * berjalan setelah finish, jadi tidak ada yang ditunggu selain unggahan. Tiap tahap dicatat
     * di state supaya tidak diulang.
     */
    private function publishVideoStory($publication, $account, $item, array &$state, int $index): bool
    {
        if (! $this->uploadVideo($publication, $account, $item, $state, $index, 'video_stories', 'Story')) {
            return false;
        }

        $state['items'][$index]['published_id'] = $this->publisher
            ->finishVideoStory($account, $state['items'][$index]['container_id']);
        $this->save($publication, $state);

        return true;
    }

    /**
     * Reels: unggah sampai lengkap, terbitkan (finish dengan caption sebagai description), lalu
     * tunggu konfirmasi sebentar. finish hanya membalas success tanpa ID, jadi ID Reels adalah
     * video_id. Bila Facebook belum mengonfirmasi dalam REEL_CONFIRM_SECONDS, finish yang sudah
     * diterima dianggap terbit (mengulang berisiko menerbitkan dua kali); galat pemrosesan yang
     * muncul sebelum itu tetap menggagalkan.
     */
    private function publishReel($publication, $account, ScheduledPost $post, $item, array &$state): bool
    {
        if (! $this->uploadVideo($publication, $account, $item, $state, 0, 'video_reels', 'Reels')) {
            return false;
        }

        $entry = &$state['items'][0];

        if (empty($entry['finished_at'])) {
            $this->publisher->finishReel($account, $entry['container_id'], $post->caption);
            $entry['finished_at'] = now()->toIso8601String();
            $this->save($publication, $state);
        }

        $status = $this->publisher->videoStatus($account, $entry['container_id']);

        if ($status['failed']) {
            throw new RuntimeException('Facebook gagal memproses Reels'.($status['error'] ? ": {$status['error']}" : '.'));
        }

        $confirmed = $status['published']
            || now()->greaterThan(Carbon::parse($entry['finished_at'])->addSeconds(self::REEL_CONFIRM_SECONDS));

        if (! $confirmed) {
            return false;
        }

        $entry['published_id'] = $entry['container_id'];
        $this->save($publication, $state);

        return true;
    }

    /**
     * Membuka sesi unggah (start), mengirim file ke rupload, dan memastikan unggahan tercatat
     * lengkap di Facebook. Tahapnya dicatat di state (container_id, upload_url, uploaded).
     *
     * @return bool false bila unggahan belum tercatat lengkap (tunggu, lalu panggil lagi)
     */
    private function uploadVideo($publication, $account, $item, array &$state, int $index, string $edge, string $label): bool
    {
        $entry = &$state['items'][$index];

        if (! $entry['container_id']) {
            $session = $this->publisher->startVideoUpload($account, $edge);
            $entry['container_id'] = $session['video_id'];
            $entry['upload_url'] = $session['upload_url'];
            $entry['uploaded'] = false;
            $entry['finished_at'] = null;
            $this->save($publication, $state);
        }

        if (empty($entry['uploaded'])) {
            $this->publisher->uploadVideo($account, $entry['upload_url'], $this->path($item));
            $entry['uploaded'] = true;
            $this->save($publication, $state);
        }

        // Setelah finish, status sudah tidak perlu dicek ulang untuk unggahan.
        if (! empty($entry['finished_at'])) {
            return true;
        }

        $status = $this->publisher->videoStatus($account, $entry['container_id']);

        if ($status['failed']) {
            throw new RuntimeException("Facebook gagal memproses video {$label}".($status['error'] ? ": {$status['error']}" : '.'));
        }

        if (! $status['uploaded']) {
            $minutes = (int) config('scheduler.video.poll_max_minutes', 10);

            if (now()->greaterThan(Carbon::parse($state['started_at'])->addMinutes($minutes))) {
                throw new RuntimeException("Facebook belum selesai menerima video dalam {$minutes} menit. Coba jadwalkan ulang.");
            }

            return false;
        }

        return true;
    }

    /**
     * Kerangka state sesuai media saat ini; ID foto yang sudah terunggah dipertahankan
     * (dicocokkan lewat ID media) dan ID postingan yang sudah terbit tidak hilang.
     */
    private function state(ScheduledPostPublication $publication, $media): array
    {
        $old = $publication->state ?? [];
        $oldChildren = collect($old['children'] ?? [])->keyBy('media_id');

        if ($publication->format === ScheduledPost::FORMAT_STORY) {
            $oldItems = collect($old['items'] ?? [])->keyBy('media_id');

            return [
                'started_at' => $old['started_at'] ?? now()->toIso8601String(),
                'items' => $media->map(fn ($m) => $oldItems->get($m->id) ?? ['media_id' => $m->id, 'container_id' => null, 'published_id' => null])->values()->all(),
                'children' => [],
            ];
        }

        return [
            'started_at' => $old['started_at'] ?? now()->toIso8601String(),
            'items' => [$old['items'][0] ?? ['media_id' => null, 'container_id' => null, 'published_id' => null]],
            'children' => $media->count() > 1
                ? $media->map(fn ($m) => $oldChildren->get($m->id) ?? ['media_id' => $m->id, 'container_id' => null])->values()->all()
                : [],
        ];
    }

    private function assertMedia($media, string $format): void
    {
        $label = ScheduledPost::formatLabel($format);

        if ($media->isEmpty()) {
            throw new RuntimeException("{$label} tidak memiliki media. Unggah ulang medianya.");
        }

        if ($format === ScheduledPost::FORMAT_FEED && $media->contains(fn ($m) => $m->isVideo())) {
            throw new RuntimeException("Facebook baru mendukung foto untuk {$label}.");
        }

        if ($format === ScheduledPost::FORMAT_REEL && $media->contains(fn ($m) => ! $m->isVideo())) {
            throw new RuntimeException('Reels hanya menerima video.');
        }

        if ($media->contains(fn ($m) => ! $m->media_path || ! Storage::disk('public')->exists($m->media_path))) {
            throw new RuntimeException("Sebagian media {$label} sudah tidak tersedia di server. Unggah ulang medianya.");
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
