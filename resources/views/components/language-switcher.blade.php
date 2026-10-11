{{--
    Pilihan bahasa antarmuka (Indonesia/English). Menyimpan pilihan lewat POST ke route locale.update,
    jadi berlaku untuk seluruh halaman dan juga untuk tamu.

    Memakai <details> bawaan HTML, bukan Alpine: layout publik tidak memuat Alpine (hanya layout admin
    yang mendapatkannya lewat Livewire), jadi komponen ini harus berjalan tanpa JavaScript framework.
--}}
@props(['tone' => 'light'])

@php
    $locales = config('sosmedhub.locales', []);
    $current = app()->getLocale();
@endphp

<details class="relative" data-language-switcher>
    <summary aria-label="{{ __('Pilih bahasa') }}" title="{{ __('Pilih bahasa') }}"
        class="flex cursor-pointer list-none items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 [&::-webkit-details-marker]:hidden {{ $tone === 'dark' ? 'text-white/70 hover:text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
        <x-flag :locale="$current" class="h-5 w-5 shrink-0" />
        <span class="uppercase">{{ $current }}</span>
    </summary>

    <div class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-lg border border-border bg-card shadow-dropdown">
        @foreach ($locales as $code => $label)
            <form method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}">
                <button type="submit" @if ($code === $current) aria-current="true" @endif
                    class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-left text-sm transition-colors hover:bg-slate-50 {{ $code === $current ? 'font-semibold text-primary' : 'text-slate-700' }}">
                    <span class="flex items-center gap-2.5">
                        <x-flag :locale="$code" class="h-5 w-5 shrink-0" />
                        <span>{{ $label }}</span>
                    </span>
                    @if ($code === $current)
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</details>

{{-- Menutup dropdown saat klik di luar atau menekan Escape. Satu kali per halaman, tanpa framework. --}}
@once
    <script>
        (function () {
            const open = () => document.querySelectorAll('details[data-language-switcher][open]');

            document.addEventListener('click', function (event) {
                open().forEach(function (el) {
                    if (! el.contains(event.target)) el.removeAttribute('open');
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') open().forEach(function (el) { el.removeAttribute('open'); });
            });
        })();
    </script>
@endonce
