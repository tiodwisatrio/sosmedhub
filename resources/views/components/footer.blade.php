<footer class="bg-slate-950 text-white">
    <div class="mx-auto flex max-w-5xl flex-col gap-3 px-6 py-8 text-sm text-white/55 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {{ date('Y') }} {{ $siteSetting->app_name ?? config('app.name') }}.</p>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
            <a href="{{ route('privacy') }}" class="transition hover:text-white">Kebijakan Privasi</a>
            <a href="{{ route('terms') }}" class="transition hover:text-white">Ketentuan Layanan</a>
            <a href="{{ route('data-deletion') }}" class="transition hover:text-white">Hapus Data</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-white">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-white">Masuk</a>
            @endauth
        </div>
    </div>
</footer>
