{{-- Navbar --}}
<nav id="site-navbar" class="fixed top-0 inset-x-0 z-[60] pl-6 pr-6 py-6">
    <div class="nav-surface liquid-glass rounded-full px-6 py-3 flex items-center justify-between max-w-5xl mx-auto relative z-50">
        {{-- Logo --}}
        <div class="flex items-center gap-2">
            @if ($siteSetting->logo_atas)
                <img src="{{ Storage::url($siteSetting->logo_atas) }}"
                     alt="{{ $siteSetting->app_name }}"
                     class="h-10 w-auto object-contain rounded-full">
            @elseif ($siteSetting->icon)
                <img src="{{ Storage::url($siteSetting->icon) }}"
                     alt="{{ $siteSetting->app_name }}"
                     class="h-7 w-7 object-contain rounded">
            @endif
            <span class="nav-text font-semibold text-lg">
                {{ $siteSetting->app_name ?? config('app.name') }}
            </span>
        </div>

        <!-- Link Menu (desktop) -->
        <div class="hidden md:flex items-center justify-center gap-8">
            <a href="/" class="nav-text text-sm font-medium">Beranda</a>
            <a href="tentang-kami" class="nav-text text-sm font-medium">Tentang Kami</a>
            <a href="layanan" class="nav-text text-sm font-medium">Layanan</a>
            <a href="{{ route('kontak') }}" class="nav-text text-sm font-medium">Kontak</a>
        </div>

        {{-- Auth buttons (desktop) --}}
        <div class="hidden md:flex items-center gap-4">
            <a href="{{ route('login') }}" class="nav-login liquid-glass rounded-full px-6 py-2 text-sm font-medium">Login</a>
        </div>

        {{-- Hamburger (mobile) --}}
        <button
            id="nav-hamburger-btn"
            type="button"
            class="nav-text md:hidden p-1 -mr-1"
            aria-label="Buka menu"
            aria-expanded="false"
        >
            <svg id="nav-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
            <svg id="nav-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile full-screen menu --}}
    <div
        id="nav-mobile-panel"
        class="hidden md:hidden fixed inset-0 z-40 bg-black flex-col items-center justify-center gap-8 px-6"
    >
        <a href="/" class="text-white text-4xl sm:text-5xl tracking-tight" style="font-family: 'Instrument Serif', serif;">Beranda</a>
        <a href="tentang-kami" class="text-white text-4xl sm:text-5xl tracking-tight" style="font-family: 'Instrument Serif', serif;">Tentang Kami</a>
        <a href="layanan" class="text-white text-4xl sm:text-5xl tracking-tight" style="font-family: 'Instrument Serif', serif;">Layanan</a>
        <a href="{{ route('kontak') }}" class="text-white text-4xl sm:text-5xl tracking-tight" style="font-family: 'Instrument Serif', serif;">Kontak</a>
        <a href="{{ route('login') }}" class="mt-4 liquid-glass rounded-full px-8 py-3 text-white text-sm font-medium tracking-wide">Login</a>
    </div>
</nav>

<style>
    #site-navbar .nav-text {
        color: #fff;
        transition: color 0.3s ease;
    }
    #site-navbar .nav-login {
        color: #fff;
        transition: color 0.3s ease, background-color 0.3s ease;
    }
    #site-navbar .nav-surface {
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    #site-navbar.scrolled .nav-surface {
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.08);
    }
    #site-navbar.scrolled .nav-text {
        color: #0f172a;
    }
    #site-navbar.scrolled .nav-login {
        color: #0f172a;
        background-color: rgba(15, 23, 42, 0.06);
    }

    /* Curtain animation — buka atas ke bawah, tutup bawah ke atas */
    #nav-mobile-panel {
        clip-path: inset(0 0 100% 0);
        transition: clip-path 0.5s cubic-bezier(0.65, 0, 0.35, 1);
    }
    #nav-mobile-panel.nav-menu-open {
        clip-path: inset(0 0 0 0);
    }
</style>

<script>
    (function () {
        var navbar = document.getElementById('site-navbar');
        if (!navbar) {
            return;
        }

        var threshold = 40;

        function updateNavbarState() {
            navbar.classList.toggle('scrolled', window.scrollY > threshold);
        }

        window.addEventListener('scroll', updateNavbarState, { passive: true });
        updateNavbarState();

        // Mobile menu toggle — animasi tirai (curtain): buka atas ke bawah, tutup bawah ke atas
        var hamburgerBtn = document.getElementById('nav-hamburger-btn');
        var iconOpen = document.getElementById('nav-icon-open');
        var iconClose = document.getElementById('nav-icon-close');
        var mobilePanel = document.getElementById('nav-mobile-panel');
        var menuAnimationMs = 500;
        var closeTimeoutId = null;

        function openMobileMenu() {
            clearTimeout(closeTimeoutId);

            mobilePanel.classList.remove('hidden');
            mobilePanel.classList.add('flex');

            // Paksa reflow supaya browser "mencatat" state tertutup (clip-path)
            // dulu sebelum kelas nav-menu-open ditambahkan, kalau tidak transisinya di-skip.
            void mobilePanel.offsetHeight;

            mobilePanel.classList.add('nav-menu-open');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
            hamburgerBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobilePanel.classList.remove('nav-menu-open');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';

            clearTimeout(closeTimeoutId);
            closeTimeoutId = setTimeout(function () {
                mobilePanel.classList.add('hidden');
                mobilePanel.classList.remove('flex');
            }, menuAnimationMs);
        }

        hamburgerBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            var isOpen = hamburgerBtn.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        mobilePanel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMobileMenu);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                closeMobileMenu();
            }
        });
    })();
</script>
