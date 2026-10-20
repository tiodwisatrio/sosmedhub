<?php

use App\Support\PasswordResetLimiter;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        PasswordResetLimiter::ensureNotBlocked();
        PasswordResetLimiter::hit();

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="w-full">
    <h1 class="font-display text-3xl font-bold">{{ __('Reset Password') }}</h1>

    <p class="mt-4 text-sm leading-relaxed text-slate-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="mt-8 space-y-4">
        <div>
            <label for="email" class="sr-only">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username"
                placeholder="{{ __('Email') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full rounded-lg bg-[#1c1c1c] px-4 py-3.5 text-base font-semibold text-white transition hover:bg-black disabled:opacity-60">
            {{ __('Email Password Reset Link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-[#1c1c1c] underline underline-offset-4">{{ __('Log in') }}</a>
    </p>
</div>
