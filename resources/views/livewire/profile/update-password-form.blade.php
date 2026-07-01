<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header class="mb-6">
        <h2 class="text-base font-semibold text-slate-800">Ubah Password</h2>
        <p class="mt-1 text-sm text-slate-500">Gunakan password yang panjang dan acak agar akun Anda tetap aman.</p>
    </header>

    <form wire:submit="updatePassword" class="space-y-5">
        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
            <input wire:model="current_password" id="update_password_current_password" name="current_password" type="password"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                autocomplete="current-password" />
            @error('current_password')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
            <input wire:model="password" id="update_password_password" name="password" type="password"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                autocomplete="new-password" />
            @error('password')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
            <input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                autocomplete="new-password" />
            @error('password_confirmation')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-colors">
                Simpan
            </button>

            <span x-data="{ show: false }"
                x-on:password-updated.window="show = true; setTimeout(() => show = false, 2500)"
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="text-sm text-success-text font-medium"
            >Tersimpan.</span>
        </div>
    </form>
</section>
