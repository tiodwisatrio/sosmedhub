<?php

namespace Modules\Scheduler\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\Scheduler\Models\ScheduledPost;

class PostFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ScheduledPost $post) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Postingan Instagram gagal terbit')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Postingan yang dijadwalkan pada '.$this->post->formattedScheduledAt().' WIB gagal terbit.')
            ->line('Caption: "'.Str::limit(strip_tags($this->post->caption), 100).'"')
            ->line('Penyebab: '.($this->post->error_message ?: 'Tidak diketahui.'))
            ->action('Jadwalkan ulang', route('admin.scheduled-posts.edit', $this->post));
    }
}
