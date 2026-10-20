<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full">
    <h1 class="font-display text-3xl font-bold">{{ __('Log in') }}</h1>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="login" class="mt-8 space-y-4">
        <div>
            <label for="email" class="sr-only">{{ __('Email') }}</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                placeholder="{{ __('Email') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="sr-only">{{ __('Password') }}</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                placeholder="{{ __('Password') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        @if (Route::has('password.request'))
            <div class="text-right">
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-slate-600 underline underline-offset-4 hover:text-[#1c1c1c]">{{ __('Forgot your password?') }}</a>
            </div>
        @endif

        <button type="submit" wire:loading.attr="disabled"
            class="w-full rounded-lg bg-[#1c1c1c] px-4 py-3.5 text-base font-semibold text-white transition hover:bg-black disabled:opacity-60">
            {{ __('Log in') }}
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-slate-600">
            {{ __('Sudah Memiliki Akun?') }}
            <a href="{{ route('register') }}" wire:navigate class="font-semibold text-[#1c1c1c] underline underline-offset-4">{{ __('Daftar') }}</a>
        </p>
    @endif
</div>
