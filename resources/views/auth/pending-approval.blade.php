<x-guest-layout>
    @php
        $status = auth()->user()?->approval_status ?? \App\Models\User::APPROVAL_PENDING;
        $title = match ($status) {
            \App\Models\User::APPROVAL_REJECTED => 'Akun belum disetujui',
            \App\Models\User::APPROVAL_SUSPENDED => 'Akun sedang disuspend',
            default => 'Akun menunggu approval',
        };
        $message = match ($status) {
            \App\Models\User::APPROVAL_REJECTED => 'Registrasi kamu belum bisa disetujui. Hubungi developer Sosmedhub kalau menurut kamu ini perlu dicek ulang.',
            \App\Models\User::APPROVAL_SUSPENDED => 'Akses dashboard kamu sedang dihentikan sementara. Hubungi developer Sosmedhub untuk aktivasi ulang.',
            default => 'Akun kamu sudah terdaftar. Developer Sosmedhub perlu approve dulu sebelum kamu bisa masuk ke dashboard.',
        };
    @endphp

    <div class="space-y-4 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-lg font-semibold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                {{ $message }}
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-primary-button type="submit">
                Logout
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
