<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only(['email', 'password']);
        $credentials['status'] = 1;

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->recordFailedAttempt();

            throw ValidationException::withMessages([
                'form.email' => __('Email atau Kata Sandi salah.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Count a failed attempt. On the fifth failure within a minute the login is locked for a full minute.
     */
    protected function recordFailedAttempt(): void
    {
        $attempts = RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

        if ($attempts >= self::MAX_ATTEMPTS) {
            RateLimiter::hit($this->lockoutKey(), self::DECAY_SECONDS);
            RateLimiter::clear($this->throttleKey());
        }
    }

    /**
     * Ensure the authentication request is not locked out.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->lockoutKey(), 1)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->lockoutKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    protected function lockoutKey(): string
    {
        return $this->throttleKey().'|lockout';
    }
}
