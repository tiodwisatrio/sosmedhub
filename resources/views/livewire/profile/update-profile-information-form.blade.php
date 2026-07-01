<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $avatar = null;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function save(): void
    {
        $user = Auth::user();

        $rules = [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone'  => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        if ($this->password !== '') {
            $rules['current_password'] = ['required', 'string', 'current_password'];
            $rules['password']         = ['required', 'string', Password::defaults(), 'confirmed'];
        }

        try {
            $validated = $this->validate($rules);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        $user->name  = $validated['name'];
        $user->phone = $validated['phone'] ?? null;

        if ($user->email !== $validated['email']) {
            $user->email             = $validated['email'];
            $user->email_verified_at = null;
        }

        if ($this->password !== '') {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->avatar               = null;
        $this->current_password     = '';
        $this->password             = '';
        $this->password_confirmation = '';

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <form wire:submit="save" class="space-y-5">

        {{-- Avatar --}}
        <div >
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Profil</label>
            <div class="flex items-end gap-4 mt-5">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-32 h-32 rounded-full object-cover border border-border">
                @elseif (Auth::user()->avatar)
                    <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-32 h-32 rounded-full object-cover border border-border">
                @else
                    <div class="w-32 h-32 rounded-full bg-primary-light flex items-center justify-center text-primary font-semibold text-base flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <input type="file" wire:model="avatar" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-light file:text-primary hover:file:bg-primary/10 cursor-pointer" />
                    <p class="mt-1 text-xs text-slate-400">JPG, PNG, GIF. Maks 2MB.</p>
                </div>
            </div>
            @error('avatar')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
            <input wire:model="name" id="name" type="text"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                required autofocus autocomplete="name" />
            @error('name')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input wire:model="email" id="email" type="email"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                required autocomplete="username" />
            @error('email')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-slate-700">
                        Email Anda belum diverifikasi.
                        <button wire:click.prevent="sendVerification" class="text-primary underline text-sm hover:text-primary/80 focus:outline-none">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 text-xs font-medium text-success-text">
                            Link verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Nomor Telepon</label>
            <input wire:model="phone" id="phone" type="text"
                class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                autocomplete="tel" placeholder="081234567890" />
            @error('phone')
                <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="border-t border-border pt-5">
            <p class="text-sm font-medium text-slate-700 mb-4">Ubah Password <span class="text-slate-400 font-normal">(kosongkan jika tidak ingin mengubah)</span></p>

            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                    <input wire:model="current_password" id="current_password" type="password"
                        class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        autocomplete="current-password" />
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                    <input wire:model="password" id="new_password" type="password"
                        class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        autocomplete="new-password" />
                    @error('password')
                        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password"
                        class="block w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        autocomplete="new-password" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-colors">
                Simpan
            </button>

            <span x-data="{ show: false }"
                x-on:profile-updated.window="show = true; setTimeout(() => show = false, 2500)"
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
