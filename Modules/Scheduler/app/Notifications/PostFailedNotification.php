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
        $partial = $this->post->status === ScheduledPost::STATUS_PARTIAL;
        $failed = $this->post->publications()->get()->filter->isFailed();

        $mail = (new MailMessage)
            ->subject($partial ? 'Sebagian postingan Instagram gagal terbit' : 'Postingan Instagram gagal terbit')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($partial
                ? 'Postingan yang dijadwalkan pada '.$this->post->formattedScheduledAt().' WIB terbit sebagian. Format yang sudah terbit tidak akan diterbitkan ulang.'
                : 'Postingan yang dijadwalkan pada '.$this->post->formattedScheduledAt().' WIB gagal terbit.');

        if ($this->post->caption !== '') {
            $mail->line('Caption: "'.Str::limit(strip_tags($this->post->caption), 100).'"');
        }

        if ($failed->count() > 1 || ($failed->isNotEmpty() && $partial)) {
            foreach ($failed as $publication) {
                $mail->line($publication->label().': '.($publication->error_message ?: 'Tidak diketahui.'));
            }
        } else {
            $mail->line('Penyebab: '.($failed->first()?->error_message ?: $this->post->error_message ?: 'Tidak diketahui.'));
        }

        return $mail->action('Jadwalkan ulang', route('admin.scheduled-posts.edit', $this->post));
    }
}
