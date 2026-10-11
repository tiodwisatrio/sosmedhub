<?php

namespace Modules\Scheduler\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\Scheduler\Models\ScheduledPost;

class PostPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ScheduledPost $post) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $account = $this->post->socialAccount;
        $isFacebook = $account?->platform === 'facebook';
        $platform = $isFacebook ? 'Facebook' : 'Instagram';
        $handle = $account ? ($isFacebook ? ($account->display_name ?: $account->username) : '@'.$account->username) : null;

        return (new MailMessage)
            ->subject(__('Postingan :platform berhasil terbit', ['platform' => $platform]))
            ->greeting(__('Halo :name,', ['name' => $notifiable->name]))
            ->line($handle
                ? __('Postingan Anda sudah terbit di :account.', ['account' => $handle])
                : __('Postingan Anda sudah terbit.'))
            ->line(__('Format: :formats.', ['formats' => implode(', ', array_map(fn (string $f) => ScheduledPost::formatLabel($f), $this->post->formats()))]))
            ->line(__('Caption: ":caption"', ['caption' => Str::limit(strip_tags($this->post->caption), 100)]))
            ->action(__('Lihat penjadwalan'), route('admin.scheduled-posts.index'));
    }
}
