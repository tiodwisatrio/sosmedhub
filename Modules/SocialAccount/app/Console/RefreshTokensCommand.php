<?php

namespace Modules\SocialAccount\Console;

use Illuminate\Console\Command;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Notifications\AccountNeedsReconnectNotification;
use Modules\SocialAccount\Services\InstagramPublisher;
use Throwable;

class RefreshTokensCommand extends Command
{
    protected $signature = 'social-accounts:refresh-tokens';

    protected $description = 'Perpanjang token Instagram yang hampir kedaluwarsa';

    public function handle(InstagramPublisher $publisher): int
    {
        $days = (int) config('social-account.instagram.refresh_before_days', 10);

        $accounts = SocialAccount::query()
            ->where('platform', SocialAccount::PLATFORM_INSTAGRAM)
            ->where('status', SocialAccount::STATUS_ACTIVE)
            ->whereNotNull('access_token')
            ->where('token_expires_at', '<=', now()->addDays($days))
            ->get();

        foreach ($accounts as $account) {
            try {
                $publisher->refreshToken($account);
            } catch (Throwable $e) {
                report($e);

                $expired = (bool) $account->token_expires_at?->isPast();

                if ($expired) {
                    $account->update(['status' => SocialAccount::STATUS_EXPIRED]);
                }

                // Peringatan hanya dalam 3 hari terakhir agar tidak mengirim email setiap hari sejak awal.
                if ($expired || $account->token_expires_at?->lte(now()->addDays(3))) {
                    $account->user?->notify(new AccountNeedsReconnectNotification($account, $expired));
                }
            }
        }

        $this->info("{$accounts->count()} akun diproses.");

        return self::SUCCESS;
    }
}
