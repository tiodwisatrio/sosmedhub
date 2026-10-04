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

        return (new MailMessage)
            ->subject('Postingan Instagram berhasil terbit')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Postingan kamu sudah terbit'.($account ? ' di @'.$account->username : '').'.')
            ->line('Caption: "'.Str::limit(strip_tags($this->post->caption), 100).'"')
            ->action('Lihat penjadwalan', route('admin.scheduled-posts.index'));
    }
}
