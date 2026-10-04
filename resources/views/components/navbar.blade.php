<nav class="fixed top-0 inset-x-0 z-50 px-6 py-5">
    <div class="mx-auto flex max-w-5xl items-center justify-between rounded-full border border-white/10 bg-black/45 px-5 py-3 text-white backdrop-blur">
        <a href="{{ url('/') }}" class="text-sm font-semibold">
            {{ $siteSetting->app_name ?? config('app.name') }}
        </a>

        <div class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="text-white/70 transition hover:text-white">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="text-white/70 transition hover:text-white">
                    Masuk
                </a>
            @endauth
        </div>
    </div>
</nav>
