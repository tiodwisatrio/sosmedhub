<?php

namespace Modules\Scheduler\Console;

use Illuminate\Console\Command;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Services\PostFinalizer;

/**
 * Jaring pengaman: format yang berstatus pending/publishing terlalu lama tanpa kemajuan (worker
 * mati, job hilang, atau masuk failed_jobs tanpa sempat memperbarui status) ditandai gagal supaya
 * jadwalnya tidak menggantung dan pemiliknya mendapat email.
 */
class RecoverStuckPublicationsCommand extends Command
{
    protected $signature = 'scheduler:recover-stuck {--minutes= : Batas tanpa kemajuan sebelum dianggap macet (bawaan dari config)}';

    protected $description = 'Tandai gagal format yang macet di status publishing, lalu kirim email ke pemilik';

    public function handle(PostFinalizer $finalizer): int
    {
        $minutes = max(1, (int) ($this->option('minutes') ?: config('scheduler.stuck_minutes', 30)));
        $cutoff = now()->subMinutes($minutes);

        $stuck = ScheduledPostPublication::query()
            ->whereIn('status', [ScheduledPostPublication::STATUS_PENDING, ScheduledPostPublication::STATUS_PUBLISHING])
            ->where('updated_at', '<', $cutoff)
            ->whereHas('scheduledPost', fn ($post) => $post->where('status', ScheduledPost::STATUS_PUBLISHING))
            ->get();

        foreach ($stuck as $publication) {
            $finalizer->abandon($publication, "Proses penerbitan tidak selesai dalam {$minutes} menit. Periksa akun tujuan lebih dulu: postingan mungkin sudah terbit. Bila belum, jadwalkan ulang.");
        }

        $this->info("{$stuck->count()} format macet ditandai gagal.");

        return self::SUCCESS;
    }
}
