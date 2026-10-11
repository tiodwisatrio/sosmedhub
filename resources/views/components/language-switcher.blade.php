{{--
    Pilihan bahasa antarmuka (Indonesia/English). Menyimpan pilihan lewat POST ke route locale.update,
    jadi berlaku untuk seluruh halaman dan juga untuk tamu.
--}}
@props(['tone' => 'light'])

@php
    $locales = config('sosmedhub.locales', []);
    $current = app()->getLocale();
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="true"
        aria-label="{{ __('Pilih bahasa') }}" title="{{ __('Pilih bahasa') }}"
        class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $tone === 'dark' ? 'text-white/70 hover:text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
        <x-flag :locale="$current" class="h-5 w-5 shrink-0" />
        <span class="uppercase">{{ $current }}</span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="absolute right-0 z-30 mt-2 w-64 overflow-hidden rounded-lg border border-border bg-card shadow-dropdown">
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
</div>
