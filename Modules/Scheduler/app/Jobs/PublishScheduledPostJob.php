<?php

namespace Modules\Scheduler\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;

/**
 * Titik masuk saat jadwal jatuh tempo: mengklaim jadwal (agar tidak terbit dua kali) lalu
 * membagi pekerjaan menjadi satu job per format.
 */
class PublishScheduledPostJob implements ShouldQueue
{
    use Queueable;

    // Tidak diulang otomatis: mengulang bisa menerbitkan postingan dua kali.
    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public readonly int $scheduledPostId) {}

    public function handle(): void
    {
        $claimed = ScheduledPost::query()
            ->whereKey($this->scheduledPostId)
            ->where('status', ScheduledPost::STATUS_SCHEDULED)
            ->update(['status' => ScheduledPost::STATUS_PUBLISHING, 'error_message' => null]);

        if (! $claimed) {
            return;
        }

        $post = ScheduledPost::query()->findOrFail($this->scheduledPostId);

        // Jadwal lama tanpa baris publikasi diperlakukan sebagai Feed.
        if ($post->publications()->doesntExist()) {
            $post->publications()->create(['format' => ScheduledPost::FORMAT_FEED]);
        }

        $publications = $post->publications()->where('status', '!=', ScheduledPostPublication::STATUS_PUBLISHED)->get();

        // Berurutan Feed, Story, Reels agar hasil di akun mengikuti urutan yang dipilih.
        foreach (ScheduledPost::FORMATS as $format) {
            foreach ($publications->where('format', $format) as $publication) {
                PublishPublicationJob::dispatch($publication->id);
            }
        }
    }
}
