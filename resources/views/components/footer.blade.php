<footer class="border-t border-[#1c1c1c]/10 bg-[#f1dfd5] text-[#1c1c1c]">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 sm:px-6 lg:px-24 text-sm text-[#1c1c1c]/60 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {{ date('Y') }} {{ $siteSetting->app_name ?? config('app.name') }}.</p>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
            <a href="{{ route('privacy') }}" class="transition hover:text-[#1c1c1c]">{{ __('Kebijakan Privasi') }}</a>
            <a href="{{ route('terms') }}" class="transition hover:text-[#1c1c1c]">{{ __('Ketentuan Layanan') }}</a>
            <a href="{{ route('data-deletion') }}" class="transition hover:text-[#1c1c1c]">{{ __('Hapus Data') }}</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#1c1c1c]">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-[#1c1c1c]">{{ __('Masuk') }}</a>
            @endauth
        </div>
    </div>
</footer>
