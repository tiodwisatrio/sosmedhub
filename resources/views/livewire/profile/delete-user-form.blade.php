<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <header class="mb-6">
        <h2 class="text-base font-semibold text-slate-800">Hapus Akun</h2>
        <p class="mt-1 text-sm text-slate-500">
            Setelah akun dihapus, semua data akan hilang secara permanen. Unduh data Anda terlebih dahulu sebelum melanjutkan.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-4 py-2 bg-danger text-white text-sm font-medium rounded-lg hover:bg-danger/90 focus:outline-none focus:ring-2 focus:ring-danger/50 transition-colors"
    >
        Hapus Akun
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-base font-semibold text-slate-800">Yakin ingin menghapus akun Anda?</h2>

            <p class="mt-2 text-sm text-slate-500">
                Semua data akun akan dihapus secara permanen. Masukkan password Anda untuk mengkonfirmasi.
            </p>

            <div class="mt-5">
                <label for="password" class="sr-only">Password</label>
                <input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="block w-3/4 rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-danger focus:outline-none focus:ring-1 focus:ring-danger"
                    placeholder="Password"
                />
                @error('password')
                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    x-on:click="$dispatch('close')"
                    class="inline-flex items-center px-4 py-2 border border-border bg-white text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 focus:outline-none transition-colors">
                    Batal
                </button>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-danger text-white text-sm font-medium rounded-lg hover:bg-danger/90 focus:outline-none focus:ring-2 focus:ring-danger/50 transition-colors">
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
