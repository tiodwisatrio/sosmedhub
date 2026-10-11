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

        $platform = $this->post->socialAccount?->platform === 'facebook' ? 'Facebook' : 'Instagram';
        $time = $this->post->formattedScheduledAt();

        $mail = (new MailMessage)
            ->subject($partial
                ? __('Sebagian postingan :platform gagal terbit', ['platform' => $platform])
                : __('Postingan :platform gagal terbit', ['platform' => $platform]))
            ->greeting(__('Halo :name,', ['name' => $notifiable->name]))
            ->line($partial
                ? __('Postingan yang dijadwalkan pada :time WIB terbit sebagian. Format yang sudah terbit tidak akan diterbitkan ulang.', ['time' => $time])
                : __('Postingan yang dijadwalkan pada :time WIB gagal terbit.', ['time' => $time]));

        if ($this->post->caption !== '') {
            $mail->line(__('Caption: ":caption"', ['caption' => Str::limit(strip_tags($this->post->caption), 100)]));
        }

        $unknown = __('Tidak diketahui.');

        if ($failed->count() > 1 || ($failed->isNotEmpty() && $partial)) {
            foreach ($failed as $publication) {
                $mail->line($publication->label().': '.($publication->displayError() ?: $unknown));
            }
        } else {
            $mail->line(__('Penyebab: :cause', ['cause' => $failed->first()?->displayError() ?: $this->post->displayError() ?: $unknown]));
        }

        return $mail->action(__('Jadwalkan Ulang'), route('admin.scheduled-posts.edit', $this->post));
    }
}
