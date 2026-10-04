<?php

namespace Modules\Scheduler\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;
use Modules\SocialAccount\Exceptions\InstagramAuthException;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;
use Modules\SocialAccount\Services\InstagramPublisher;
use Throwable;

class PublishScheduledPostJob implements ShouldQueue
{
    use Queueable;

    // Tidak diulang otomatis: mengulang bisa menerbitkan postingan dua kali.
    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(public readonly int $scheduledPostId) {}

    public function handle(InstagramPublisher $publisher): void
    {
        $claimed = ScheduledPost::query()
            ->whereKey($this->scheduledPostId)
            ->where('status', ScheduledPost::STATUS_SCHEDULED)
            ->update(['status' => ScheduledPost::STATUS_PUBLISHING, 'error_message' => null]);

        if (! $claimed) {
            return;
        }

        $post = ScheduledPost::with(['socialAccount', 'media'])->findOrFail($this->scheduledPostId);

        try {
            $account = $post->socialAccount;

            if (! $account?->isActive() || ! $account->access_token) {
                throw new \RuntimeException('Akun Instagram tidak aktif atau token tidak tersedia.');
            }

            $urls = $post->media
                ->map(fn ($media) => Storage::disk('public')->url($media->media_path))
                ->all();

            $mediaId = $publisher->publish($account, $urls, $post->caption);

            $post->update([
                'status' => ScheduledPost::STATUS_PUBLISHED,
                'ig_media_id' => $mediaId,
                'published_at' => now(),
            ]);

            if (config('scheduler.notify_published', true)) {
                $this->notifyOwner($post, new PostPublishedNotification($post));
            }
        } catch (Throwable $e) {
            report($e);

            $post->update([
                'status' => ScheduledPost::STATUS_FAILED,
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);

            $this->notifyOwner($post, new PostFailedNotification($post));

            if ($e instanceof InstagramAuthException && $post->socialAccount) {
                $post->socialAccount->update(['status' => SocialAccount::STATUS_EXPIRED]);

                $this->notifyOwner($post, new AccountNeedsReconnectNotification($post->socialAccount, expired: true));
            }
        }
    }

    /**
     * Gagal mengirim email tidak boleh mengubah hasil publikasi.
     */
    private function notifyOwner(ScheduledPost $post, object $notification): void
    {
        try {
            $post->user?->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
