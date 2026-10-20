<section class="relative overflow-hidden bg-[#f1dfd5] text-[#1c1c1c]">
    <div class="hero-bg absolute inset-0 bg-cover bg-center bg-no-repeat" aria-hidden="true"></div>

    <div class="relative mx-auto flex min-h-[100svh] max-w-7xl flex-col items-center justify-center px-6 pb-16 pt-36 text-center md:pt-24">
        <h1 class="font-display text-5xl font-bold leading-[1.1] md:text-8xl">
            {{ __('The Best Use for') }}<br>
            <span class="grid" data-word-rotator>
                @foreach (['Social Media Specialist', 'Business Owner', 'Digital Marketer', 'Content Creator'] as $word)
                    <span class="rotator-word col-start-1 row-start-1" @if ($loop->first) data-state="active" @else aria-hidden="true" @endif>{{ __($word) }}</span>
                @endforeach
            </span>
        </h1>
        <p class="mt-8 max-w-xl text-sm leading-relaxed text-[#1c1c1c]/85">
            {{ __('Unggah foto atau video, tulis caption, pilih jam terbit dalam WIB. Sosmedhub menerbitkannya ke akun Instagram dan Halaman Facebook usaha Anda secara otomatis, tanpa perlu membuka aplikasi saat waktunya tiba.') }}
        </p>

        <div class="mt-12 flex flex-wrap items-center justify-center gap-3 text-sm font-medium">
            @auth
                {{-- Developer dan user yang sudah disetujui masuk ke dashboard; sisanya melihat status persetujuan. --}}
                @if (auth()->user()->isDeveloper() || auth()->user()->isApproved())
                    <a href="{{ route('admin.dashboard') }}" class="bg-[#1c1c1c] px-7 py-4 text-white transition hover:bg-black">{{ __('Buka dashboard') }}</a>
                @else
                    <a href="{{ route('approval.pending') }}" class="bg-[#1c1c1c] px-7 py-4 text-white transition hover:bg-black">{{ __('Lihat status persetujuan') }}</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="bg-[#1c1c1c] px-7 py-4 text-white transition hover:bg-black">{{ __('Coba Gratis 14 Hari') }}</a>
                <a href="{{ route('login') }}" class="border-2 border-[#1c1c1c] bg-white px-7 py-[14px] transition hover:bg-[#1c1c1c] hover:text-white">{{ __('Coba Demo') }}</a>
            @endauth
        </div>
    </div>
</section>

<script>
    (function () {
        const words = document.querySelectorAll('[data-word-rotator] .rotator-word');

        if (words.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let current = 0;

        setInterval(function () {
            const leaving = words[current];
            current = (current + 1) % words.length;
            const entering = words[current];

            leaving.dataset.state = 'leaving';
            leaving.setAttribute('aria-hidden', 'true');
            entering.dataset.state = 'active';
            entering.removeAttribute('aria-hidden');

            setTimeout(function () {
                if (leaving.dataset.state === 'leaving') delete leaving.dataset.state;
            }, 600);
        }, 2600);
    })();
</script>
