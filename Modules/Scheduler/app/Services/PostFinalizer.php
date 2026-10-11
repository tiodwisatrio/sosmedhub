<?php

namespace Modules\Scheduler\Services;

use Illuminate\Support\Facades\DB;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Notifications\PostFailedNotification;
use Modules\Scheduler\Notifications\PostPublishedNotification;
use Throwable;

/**
 * Menetapkan hasil akhir sebuah jadwal setelah semua formatnya selesai, dan mengirim email.
 * Dipakai job biasa, hook failed() job, dan perintah pemulihan, supaya hasilnya sama dari mana pun.
 */
class PostFinalizer
{
    /**
     * Bila semua format sudah selesai, tetapkan status jadwal sekali saja dan kirim email.
     */
    public function finalize(ScheduledPost $post): void
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
     * Menandai satu format gagal karena prosesnya berhenti di luar jalur normal (job kehabisan
     * percobaan, kena batas waktu, atau worker mati), lalu menetapkan hasil akhir jadwalnya.
     * Format yang sudah final tidak disentuh.
     */
    public function abandon(ScheduledPostPublication $publication, string $reason): void
    {
        $fresh = $publication->fresh();

        if (! $fresh || $fresh->isFinal()) {
            return;
        }

        $fresh->update([
            'status' => ScheduledPostPublication::STATUS_FAILED,
            'error_message' => mb_substr($reason, 0, 500),
        ]);

        $post = $fresh->scheduledPost()->first();

        if ($post) {
            $this->finalize($post);
        }
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
