@props([
    'platform',
    'label',
    'description' => null,
    'accounts' => collect(),
    'connectUrl' => null,
    'available' => false,
    'showOwner' => false,
])

{{--
    Kartu satu platform di halaman Akun Sosial.
    - Belum ada akun: tombol Hubungkan (nonaktif bila platform belum tersedia).
    - Sudah ada akun: tiap akun tampil dengan avatar, nama, username, Edit, dan Putuskan;
      tombol Tambah Akun di bawahnya.
--}}

@php
    $statusExpired = \Modules\SocialAccount\Models\SocialAccount::STATUS_EXPIRED;
    $count = $accounts->count();
    $outline = 'inline-flex items-center justify-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 px-3 py-1.5 text-xs border border-border hover:bg-slate-50 text-slate-700';
    $primary = 'inline-flex w-full items-center justify-center gap-1.5 font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary/30 px-4 py-2 text-sm bg-primary hover:bg-primary-hover text-white shadow-sm';
@endphp

<section class="flex flex-col rounded-xl border border-border bg-card shadow-card" aria-labelledby="platform-{{ $platform }}">
    <header class="flex items-center gap-3 border-b border-border px-5 py-4">
        <x-social-account::brand-icon :platform="$platform" @class(['opacity-50 grayscale' => ! $available]) />

        <div class="min-w-0 flex-1">
            <h2 id="platform-{{ $platform }}" class="text-sm font-semibold text-slate-800">{{ $label }}</h2>
            <p class="truncate text-xs text-slate-400">
                @if (! $available)
                    {{ $description ?? 'Belum tersedia' }}
                @elseif ($count > 0)
                    {{ $count }} akun terhubung
                @else
                    Belum ada akun terhubung
                @endif
            </p>
        </div>

        @unless ($available)
            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">Segera hadir</span>
        @endunless
    </header>

    <div class="flex flex-1 flex-col">
        @if ($count > 0)
            <ul class="divide-y divide-border">
                @foreach ($accounts as $account)
                    @php
                        $name = $account->display_name ?: $account->username;
                        $expired = $account->status === $statusExpired;
                    @endphp
                    <li class="px-5 py-4" data-account="{{ $account->username }}">
                        <div class="flex items-center gap-3">
                            <div class="relative h-11 w-11 shrink-0">
                                @if ($account->avatar_url)
                                    <img src="{{ $account->avatar_url }}" alt="Foto profil {{ $account->username }}" loading="lazy" referrerpolicy="no-referrer"
                                        class="h-11 w-11 rounded-full border border-border object-cover"
                                        onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
                                @endif
                                <span @class([
                                    'grid h-11 w-11 place-items-center rounded-full bg-primary-light text-sm font-semibold text-primary',
                                    'hidden' => $account->avatar_url,
                                ])>{{ strtoupper(mb_substr($account->username, 0, 2)) }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $name }}</p>
                                @if ($platform === 'instagram')
                                    <p class="truncate text-xs text-slate-500">{{ '@'.$account->username }}</p>
                                @elseif ($account->provider_account_id)
                                    {{-- Halaman Facebook bisa bernama sama, jadi ID membedakannya. --}}
                                    <p class="truncate text-xs text-slate-500">ID {{ $account->provider_account_id }}</p>
                                @endif
                                @if ($showOwner && $account->user)
                                    <p class="truncate text-xs text-slate-400">Pemilik: {{ $account->user->name }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($expired)
                            <p class="mt-3 rounded-md bg-warning-light px-3 py-2 text-xs text-warning-text">
                                Koneksi berakhir. Hubungkan ulang agar jadwal tetap terbit.
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if ($expired && $available && $connectUrl)
                                @can('social-account.create')
                                    <a href="{{ $connectUrl }}" class="{{ $outline }} w-full !border-primary !py-2 !text-primary">Hubungkan Ulang</a>
                                @endcan
                            @endif

                            @can('social-account.edit')
                                <a href="{{ route('admin.social-accounts.edit', $account) }}" class="{{ $outline }}">Edit</a>
                            @endcan

                            @can('social-account.delete')
                                <form method="POST" action="{{ route('admin.social-accounts.destroy', $account) }}"
                                    onsubmit="return confirm('Putuskan akun {{ $account->username }}? Postingan terjadwal untuk akun ini tidak akan terbit.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="{{ $outline }} !border-danger/30 !text-danger hover:!bg-danger-light">Putuskan</button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="flex flex-1 items-center px-5 py-8">
                <p class="text-sm text-slate-500">
                    @if ($available)
                        Hubungkan akun {{ $label }} untuk mulai menjadwalkan postingan.
                    @else
                        Dukungan {{ $label }} sedang disiapkan.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <footer class="border-t border-border px-5 py-4">
        @if (! $available)
            <button type="button" disabled aria-disabled="true"
                class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-md bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400">
                Hubungkan
            </button>
        @else
            @can('social-account.create')
                @if ($count > 0)
                    <a href="{{ $connectUrl }}" class="{{ $outline }} w-full !py-2 !text-sm">+ Tambah Akun</a>
                @else
                    <a href="{{ $connectUrl }}" class="{{ $primary }}">Hubungkan</a>
                @endif
            @else
                <p class="text-center text-xs text-slate-400">Anda tidak punya izin untuk menghubungkan akun.</p>
            @endcan
        @endif
    </footer>
</section>
