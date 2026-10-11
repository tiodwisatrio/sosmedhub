<?php

namespace Modules\Scheduler\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Models\ScheduledPostPublication;
use Modules\Scheduler\Services\FacebookPublicationRunner;
use Modules\Scheduler\Services\PostFinalizer;
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

        $platform = $publication->scheduledPost()->first()?->socialAccount?->platform;

        if ($platform === SocialAccount::PLATFORM_FACEBOOK) {
            $runner = app(FacebookPublicationRunner::class);
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

        app(PostFinalizer::class)->finalize($post);
    }

    /**
     * Dipanggil bila job berhenti di luar jalur normal: kehabisan percobaan, kena batas waktu, atau
     * pengecualian yang tidak tertangani. Tanpa ini format tetap berstatus publishing selamanya.
     */
    public function failed(?Throwable $exception): void
    {
        $publication = ScheduledPostPublication::query()->find($this->publicationId);

        if (! $publication) {
            return;
        }

        app(PostFinalizer::class)->abandon($publication, $exception
            ? __('Proses penerbitan berhenti sebelum selesai (:exception). Periksa akun tujuan lebih dulu: postingan mungkin sudah terbit. Bila belum, jadwalkan ulang.', ['exception' => class_basename($exception)])
            : __('Proses penerbitan berhenti sebelum selesai. Periksa akun tujuan lebih dulu: postingan mungkin sudah terbit. Bila belum, jadwalkan ulang.'));
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
