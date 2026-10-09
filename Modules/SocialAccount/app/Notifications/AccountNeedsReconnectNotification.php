<?php

namespace Modules\SocialAccount\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\SocialAccount\Models\SocialAccount;

class AccountNeedsReconnectNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SocialAccount $account,
        public readonly bool $expired,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isFacebook = $this->account->platform === SocialAccount::PLATFORM_FACEBOOK;
        $platform = $isFacebook ? 'Facebook' : 'Instagram';
        $handle = $isFacebook ? ($this->account->display_name ?: $this->account->username) : '@'.$this->account->username;

        $message = (new MailMessage)
            ->greeting('Halo '.$notifiable->name.',');

        if ($this->expired) {
            $message
                ->subject("Akun {$platform} {$handle} terputus")
                ->line("Koneksi akun {$platform} {$handle} sudah tidak berlaku, jadi postingan terjadwal untuk akun ini tidak bisa terbit.")
                ->line('Hubungkan ulang akun supaya penjadwalan berjalan lagi.');
        } else {
            $message
                ->subject("Koneksi akun {$platform} {$handle} akan berakhir")
                ->line("Koneksi akun {$platform} {$handle} akan berakhir pada {$this->expiresAtWib()} dan belum berhasil diperpanjang otomatis.")
                ->line('Hubungkan ulang akun sebelum tanggal itu supaya postingan terjadwal tetap terbit.');
        }

        return $message->action('Hubungkan ulang akun', route('admin.social-accounts.index'));
    }

    private function expiresAtWib(): string
    {
        return $this->account->token_expires_at
            ?->setTimezone('Asia/Jakarta')
            ->format('d M Y H:i').' WIB';
    }
}
