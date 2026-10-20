<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Pembatas permintaan reset password per IP. Lima permintaan dalam satu menit mengunci IP itu
 * selama 30 menit, dan kunci dipakai bersama oleh halaman lupa password dan halaman atur ulang
 * supaya penyerang tidak bisa bergantian di antara keduanya.
 */
class PasswordResetLimiter
{
    private const MAX_ATTEMPTS = 5;

    private const WINDOW_SECONDS = 60;

    private const BLOCK_SECONDS = 1800;

    /**
     * @throws ValidationException
     */
    public static function ensureNotBlocked(string $field = 'email'): void
    {
        if (! RateLimiter::tooManyAttempts(self::blockKey(), 1)) {
            return;
        }

        $minutes = (int) ceil(RateLimiter::availableIn(self::blockKey()) / 60);

        throw ValidationException::withMessages([
            $field => __('Terlalu banyak permintaan. Coba lagi dalam :minutes menit.', ['minutes' => $minutes]),
        ]);
    }

    /**
     * Catat satu permintaan. Permintaan kelima menyalakan kunci 30 menit untuk permintaan berikutnya.
     */
    public static function hit(): void
    {
        $attempts = RateLimiter::hit(self::attemptsKey(), self::WINDOW_SECONDS);

        if ($attempts >= self::MAX_ATTEMPTS) {
            RateLimiter::hit(self::blockKey(), self::BLOCK_SECONDS);
            RateLimiter::clear(self::attemptsKey());
        }
    }

    private static function attemptsKey(): string
    {
        return 'password-reset|'.request()->ip();
    }

    private static function blockKey(): string
    {
        return self::attemptsKey().'|blocked';
    }
}
