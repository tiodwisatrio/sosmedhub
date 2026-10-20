@php
    $appName = $siteSetting->app_name ?? config('app.name');
    $menu = [
        ['label' => __('Kebijakan Privasi'), 'href' => route('privacy')],
        ['label' => __('Ketentuan Layanan'), 'href' => route('terms')],
        ['label' => __('Hapus Data'), 'href' => route('data-deletion')],
    ];
@endphp

{{-- Layout publik tidak memuat Alpine, jadi menu hamburger digerakkan skrip kecil di bawah lewat atribut data-open. --}}
<header data-navbar class="fixed inset-x-0 top-0 z-50 px-5 py-4 text-[#1c1c1c] bg-white shadow-sm transition-colors duration-200 md:bg-transparent md:shadow-none md:data-[scrolled=true]:bg-white md:data-[scrolled=true]:shadow-sm sm:px-6 sm:py-5 lg:px-24">
    <div class="relative z-50 mx-auto flex max-w-7xl items-center justify-between gap-6">
        <a href="{{ url('/') }}" class="text-base font-semibold md:text-sm">
            {{ $appName }}
        </a>

        <nav aria-label="{{ __('Menu utama') }}" class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-8 text-sm md:flex">
            @foreach ($menu as $item)
                <a href="{{ $item['href'] }}" class="whitespace-nowrap text-[#1c1c1c]/70 transition hover:text-[#1c1c1c]">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-5 text-sm md:flex">
            <x-language-switcher />

            @auth
                <a href="{{ route('admin.dashboard') }}" class="bg-[#1c1c1c] px-6 py-3 font-medium text-white transition hover:bg-black">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="font-medium transition hover:opacity-70">{{ __('Masuk') }}</a>
                <a href="{{ route('register') }}" class="bg-[#1c1c1c] px-6 py-3 font-medium text-white transition hover:bg-black">{{ __('Daftar') }}</a>
            @endauth
        </div>

        <div class="flex items-center gap-1 md:hidden">
            <x-language-switcher />

            <button type="button" data-menu-button aria-controls="mobile-menu" aria-expanded="false" aria-label="{{ __('Menu utama') }}"
                class="group relative h-10 w-10 md:hidden">
                <span class="absolute left-2.5 top-[14px] h-0.5 w-5 bg-current transition duration-300 group-data-[open=true]:translate-y-[6px] group-data-[open=true]:rotate-45"></span>
                <span class="absolute left-2.5 top-5 h-0.5 w-5 bg-current transition duration-200 group-data-[open=true]:opacity-0"></span>
                <span class="absolute left-2.5 top-[26px] h-0.5 w-5 bg-current transition duration-300 group-data-[open=true]:-translate-y-[6px] group-data-[open=true]:-rotate-45"></span>
            </button>
        </div>
    </div>

    <div id="mobile-menu" data-mobile-menu
        class="invisible fixed inset-0 z-40 flex -translate-y-3 flex-col overflow-y-auto bg-white px-5 pb-8 pt-24 opacity-0 transition-[opacity,transform,visibility] duration-300 ease-out data-[open=true]:visible data-[open=true]:translate-y-0 data-[open=true]:opacity-100 sm:px-6 md:hidden">
        <nav aria-label="{{ __('Menu utama') }}" class="flex flex-col">
            @foreach ($menu as $item)
                <a href="{{ $item['href'] }}" style="transition-delay: {{ 80 + $loop->index * 60 }}ms"
                    class="translate-y-3 border-b border-[#1c1c1c]/10 py-5 font-display text-3xl font-semibold opacity-0 transition duration-300 [[data-open=true]_&]:translate-y-0 [[data-open=true]_&]:opacity-100">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="mt-auto flex items-center gap-3 pt-10 text-base">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="flex-1 bg-[#1c1c1c] px-5 py-4 text-center font-medium text-white">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="flex-1 border-2 border-[#1c1c1c] px-5 py-[14px] text-center font-medium">{{ __('Masuk') }}</a>
                <a href="{{ route('register') }}" class="flex-1 bg-[#1c1c1c] px-5 py-4 text-center font-medium text-white">{{ __('Daftar') }}</a>
            @endauth
        </div>
    </div>

    <script>
        (function () {
            const root = document.documentElement;
            const bar = document.querySelector('header[data-navbar]');
            const menu = document.querySelector('[data-mobile-menu]');
            const button = document.querySelector('[data-menu-button]');

            const setOpen = function (open) {
                menu.dataset.open = open ? 'true' : 'false';
                button.dataset.open = open ? 'true' : 'false';
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                root.style.overflow = open ? 'hidden' : '';
            };

            const sync = function () {
                bar.dataset.scrolled = window.scrollY > 8 ? 'true' : 'false';
            };

            sync();
            window.addEventListener('scroll', sync, { passive: true });

            button.addEventListener('click', function () {
                setOpen(menu.dataset.open !== 'true');
            });

            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () { setOpen(false); });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') setOpen(false);
            });

            window.matchMedia('(min-width: 768px)').addEventListener('change', function (event) {
                if (event.matches) setOpen(false);
            });
        })();
    </script>
</header>
