<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $autoApprove = (bool) config('sosmedhub.registration.auto_approve', false);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => true,
            'approval_status' => $autoApprove ? User::APPROVAL_APPROVED : User::APPROVAL_PENDING,
        ]);

        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $user->assignRole($clientRole);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(
            route($autoApprove ? 'admin.dashboard' : 'approval.pending', absolute: false),
            navigate: true
        );
    }
}; ?>

<div class="w-full">
    <h1 class="font-display text-3xl font-bold">{{ __('Register') }}</h1>

    <form wire:submit="register" class="mt-8 space-y-4">
        <div>
            <label for="name" class="sr-only">{{ __('Name') }}</label>
            <input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name"
                placeholder="{{ __('Name') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="sr-only">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" name="email" required autocomplete="username"
                placeholder="{{ __('Email') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="sr-only">{{ __('Password') }}</label>
            <input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password"
                placeholder="{{ __('Password') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="sr-only">{{ __('Confirm Password') }}</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                placeholder="{{ __('Confirm Password') }}"
                class="block w-full rounded-lg border border-slate-300 px-4 py-3.5 text-base placeholder:text-slate-400 focus:border-[#1c1c1c] focus:ring-2 focus:ring-[#1c1c1c]/10">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full rounded-lg bg-[#1c1c1c] px-4 py-3.5 text-base font-semibold text-white transition hover:bg-black disabled:opacity-60">
            {{ __('Register') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        {{ __('Sudah Memiliki Akun?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-[#1c1c1c] underline underline-offset-4">{{ __('Masuk') }}</a>
    </p>
</div>
