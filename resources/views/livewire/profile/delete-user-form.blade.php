<?php

use App\Livewire\Actions\Logout;
use App\Support\UserDataEraser;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Hapus akun yang sedang masuk beserta seluruh datanya.
     */
    public function deleteUser(Logout $logout, UserDataEraser $eraser): void
    {
        $user = Auth::user();

        abort_if($user->isDeveloper(), 403);

        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $logout();

        $eraser->erase($user);

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-4">
    <div>
        <h2 class="text-base font-semibold text-slate-800">{{ __('Hapus Akun') }}</h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Menghapus akun akan menghapus permanen semua data Anda: akun Instagram dan Facebook yang terhubung, jadwal postingan, serta foto dan video yang Anda unggah. Postingan yang sudah terbit di Instagram atau Facebook tidak ikut terhapus.') }}
        </p>
    </div>

    <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center rounded-lg bg-danger px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-danger-hover">
        {{ __('Hapus Akun') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" max-width="md" centered focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-semibold text-slate-800">{{ __('Yakin ingin menghapus akun?') }}</h2>

            <p class="mt-2 text-sm text-slate-500">
                {{ __('Tindakan ini tidak bisa dibatalkan. Masukkan kata sandi untuk mengonfirmasi penghapusan akun secara permanen.') }}
            </p>

            <div class="mt-5">
                <label for="delete_password" class="sr-only">{{ __('Password') }}</label>
                <input wire:model="password" id="delete_password" type="password" placeholder="{{ __('Password') }}"
                    class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    autocomplete="current-password" />
                @error('password')
                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Batal') }}</button>
                <button type="submit"
                    class="rounded-lg bg-danger px-4 py-2 text-sm font-medium text-white hover:bg-danger-hover">{{ __('Hapus Akun') }}</button>
            </div>
        </form>
    </x-modal>
</section>
