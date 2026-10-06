<?php

namespace Modules\Scheduler\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;
use Modules\Scheduler\Services\PublicationRunner;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;
use Throwable;

/**
 * Menerbitkan satu format dari sebuah jadwal. Bila Instagram masih memproses video, job melepas
 * diri dan dicoba lagi nanti (release), tanpa menahan worker. Kegagalan tidak diulang otomatis,
 * karena mengulang bisa menerbitkan postingan dua kali; ulang lewat Jadwalkan Ulang.
 */
class PublishPublicationJob implements ShouldQueue
{
    use Queueable;

    // Cukup untuk menunggu video: setiap release dihitung sebagai satu percobaan.
    public int $tries = 40;

    public int $timeout = 150;

    public function __construct(public readonly int $publicationId) {}

    public function handle(PublicationRunner $runner): void
    {
        $publication = ScheduledPostPublication::query()->find($this->publicationId);

        if (! $publication || $publication->isFinal()) {
            return;
        }

        $result = $runner->advance($publication);

        if ($result->isWaiting()) {
            $this->release((int) config('scheduler.video.poll_delay_seconds', 30));

            return;
        }

        $post = $publication->scheduledPost()->with(['socialAccount', 'user'])->first();

        if ($result->authFailed && $post->socialAccount) {
            $post->socialAccount->update(['status' => SocialAccount::STATUS_EXPIRED]);
            $this->notify($post, new AccountNeedsReconnectNotification($post->socialAccount, expired: true));
        }

        $this->finalize($post);
    }

    /**
     * Bila semua format sudah selesai, tetapkan status jadwal sekali saja dan kirim email.
     */
    private function finalize(ScheduledPost $post): void
    {
        $final = DB::transaction(function () use ($post) {
            $locked = ScheduledPost::query()->lockForUpdate()->find($post->id);

            if (! $locked || $locked->status !== ScheduledPost::STATUS_PUBLISHING) {
                return null;
            }

            $status = $locked->resolveFinalStatus();

            if ($status === null) {
                return null;
            }

            $publications = $locked->publications()->get();
            $failed = $publications->filter->isFailed();

            $locked->update([
                'status' => $status,
                'ig_media_id' => $publications->firstWhere('format', ScheduledPost::FORMAT_FEED)?->ig_media_id
                    ?? $publications->first(fn ($p) => $p->ig_media_id)?->ig_media_id,
                'published_at' => $status === ScheduledPost::STATUS_FAILED ? null : now(),
                'error_message' => $this->errorSummary($failed, $publications->count()),
            ]);

            return $locked->fresh(['socialAccount', 'user']);
        });

        if (! $final) {
            return;
        }

        if ($final->status === ScheduledPost::STATUS_PUBLISHED) {
            if (config('scheduler.notify_published', true)) {
                $this->notify($final, new PostPublishedNotification($final));
            }

            return;
        }

        $this->notify($final, new PostFailedNotification($final));
    }

    /**
     * Satu format: pesan apa adanya. Beberapa format: diawali nama format.
     */
    private function errorSummary($failed, int $total): ?string
    {
        if ($failed->isEmpty()) {
            return null;
        }

        $parts = $failed->map(fn ($p) => $total > 1 ? $p->label().': '.$p->error_message : $p->error_message);

        return mb_substr($parts->implode(' | '), 0, 500);
    }

    /**
     * Gagal mengirim email tidak boleh mengubah hasil publikasi.
     */
    private function notify(ScheduledPost $post, object $notification): void
    {
        try {
            $post->user?->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
