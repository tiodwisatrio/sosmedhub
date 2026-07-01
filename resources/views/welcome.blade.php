<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteSetting->app_name ?? config('app.name') }}</title>
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');

        #hero-video {
            opacity: 0;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>

{{-- ================================================================
     LOADING SCREEN
     ================================================================ --}}
<style>
    #loader {
        position: fixed; inset: 0; z-index: 9999;
        background: #000;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        overflow: hidden;
        will-change: transform;
        background-image: repeating-linear-gradient(
            0deg, transparent, transparent 2px,
            rgba(255,255,255,0.013) 2px, rgba(255,255,255,0.013) 4px
        );
    }
    #ld-num {
        font-family: 'Instrument Serif', serif;
        font-size: clamp(7rem, 26vw, 22rem);
        color: #fff; line-height: 1;
        letter-spacing: -0.03em;
        will-change: transform, opacity, filter;
        user-select: none;
        display: flex; align-items: flex-start;
    }
    #ld-pct-sign {
        font-size: 0.38em;
        align-self: flex-end;
        margin-bottom: 0.12em;
        margin-left: 0.06em;
        opacity: 0.55;
        letter-spacing: 0;
    }
    #ld-brand-wrap { overflow: hidden; margin-top: 10px; }
    #ld-brand {
        display: block; font-size: 10px;
        letter-spacing: 0.5em; text-transform: uppercase;
        color: rgba(255,255,255,0.3);
        transform: translateY(100%);
        transition: transform 0.9s cubic-bezier(0.16,1,0.3,1) 0.35s;
    }
    #ld-bar-track {
        position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
        background: rgba(255,255,255,0.05);
    }
    #ld-bar {
        height: 100%; width: 0%;
        background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.85));
        will-change: width;
    }
    #ld-pct-wrap {
        position: absolute; bottom: 14px; right: 28px;
        font-size: 10px; font-family: monospace;
        color: rgba(255,255,255,0.2); letter-spacing: 0.1em;
    }
    /* Leading edge — white strip that "cuts" through screen on exit */
    #ld-edge {
        position: absolute; bottom: -1px; left: 0; right: 0;
        height: 1px; background: rgba(255,255,255,0);
        transition: background 0.15s ease;
    }
</style>

<div id="loader">
    <div style="text-align:center;position:relative;z-index:1;">
        <div id="ld-num"><span id="ld-count">00</span><span id="ld-pct-sign">%</span></div>
        <div id="ld-brand-wrap">
            <span id="ld-brand">{{ $siteSetting->app_name ?? config('app.name') }}</span>
        </div>
    </div>
    <div id="ld-bar-track"><div id="ld-bar"></div></div>
    <div id="ld-pct-wrap"><span id="ld-pct">0</span>%</div>
    <div id="ld-edge"></div>
</div>

<script>
(function () {
    var loader   = document.getElementById('loader');
    var ldNum    = document.getElementById('ld-num');
    var ldCount  = document.getElementById('ld-count');
    var ldBrand  = document.getElementById('ld-brand');
    var ldBar    = document.getElementById('ld-bar');
    var ldPct    = document.getElementById('ld-pct');
    var ldEdge   = document.getElementById('ld-edge');
    var beranda = document.getElementById('beranda');

    document.body.style.overflow = 'hidden';

    // Hero starts invisible — will fade in with loader exit
    if (beranda) {
        beranda.style.opacity  = '0';
        beranda.style.transform = 'translateY(14px)';
    }

    // Reveal brand name
    setTimeout(function () { ldBrand.style.transform = 'translateY(0)'; }, 50);

    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    var totalDuration = 2200;
    var startTime = null;

    function animateCounter(now) {
        if (!startTime) startTime = now;
        var t     = Math.min((now - startTime) / totalDuration, 1);
        var eased = easeOutExpo(t);
        var count = Math.floor(eased * 100);

        ldCount.textContent = count.toString().padStart(2, '0');
        ldPct.textContent = count;
        ldBar.style.width = (eased * 100).toFixed(2) + '%';

        var glow = Math.floor(eased * 55);
        ldNum.style.textShadow = '0 0 ' + glow + 'px rgba(255,255,255,' + (eased * 0.38).toFixed(2) + ')';

        if (t < 1) {
            requestAnimationFrame(animateCounter);
        } else {
            ldCount.textContent = '100';
            ldPct.textContent = '100';
            ldBar.style.width = '100%';
            setTimeout(exitLoader, 240);
        }
    }

    function exitLoader() {
        // 1. Number explodes
        ldNum.style.transition = 'transform 0.4s cubic-bezier(0.4,0,1,1), opacity 0.4s ease, filter 0.4s ease';
        ldNum.style.transform  = 'scale(1.3)';
        ldNum.style.opacity    = '0';
        ldNum.style.filter     = 'blur(28px)';
        ldBrand.style.transition = 'opacity 0.25s ease';
        ldBrand.style.opacity    = '0';

        setTimeout(function () {
            // 2. Light up leading edge (white "knife")
            ldEdge.style.background = 'rgba(255,255,255,0.75)';

            // 3. Loader wipes up — fast start, eases gently at end
            loader.style.transition = 'transform 0.95s cubic-bezier(0.87,0,0.13,1)';
            loader.style.transform  = 'translateY(-100%)';

            // 4. Simultaneously: hero slides up and fades in
            if (beranda) {
                beranda.style.transition = 'opacity 0.75s ease 0.05s, transform 0.95s cubic-bezier(0.16,1,0.3,1) 0.05s';
                beranda.style.opacity    = '1';
                beranda.style.transform  = 'translateY(0)';
            }

            document.body.style.overflow = '';
        }, 280);

        setTimeout(function () { loader.remove(); }, 1300);
    }

    requestAnimationFrame(animateCounter);
})();
</script>

<div id="beranda" class="min-h-screen bg-black overflow-hidden relative flex flex-col">

    {{-- Background Video --}}
    <div class="absolute inset-0 z-0">
        <video
            id="hero-video"
            class="absolute inset-0 w-full h-full object-cover translate-y-[17%]"
            muted
            playsinline
            preload="auto"
        >
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260328_115001_bcdaa3b4-03de-47e7-ad63-ae3e392c32d4.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Navbar -->
    <x-navbar />

    {{-- Hero Content --}}
    <div id="hero-content" class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 pt-32 pb-8 text-center -translate-y-[8%]">
        @php
            $words     = explode(' ', $hero->judul_hero ?? '');
            $firstLine = implode(' ', array_slice($words, 0, 3));
            $restLine  = implode(' ', array_slice($words, 3));
        @endphp
        <h1
            class="text-5xl md:text-6xl lg:text-7xl text-white mb-8 tracking-tight"
            style="font-family: 'Instrument Serif', serif;"
        >
            {{ $firstLine }}<br>{{ $restLine }}
        </h1>

        <div class="max-w-xl w-full space-y-4">
            @if ($hero?->deskripsi_hero)
                <p class="text-white text-sm leading-relaxed px-4">
                    {{ $hero->deskripsi_hero }}
                </p>
            @endif

            @if ($hero?->button_hero)
                <div class="flex justify-center">
                    <button class="liquid-glass rounded-full px-8 py-3 text-white text-sm font-medium hover:bg-white/5 transition-colors">
                        {{ $hero->button_hero }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Scroll Down --}}
    <div class="relative z-50 flex flex-col items-center gap-3 pb-10 select-none">
        {{-- Mouse icon --}}
        <div class="w-[22px] h-[34px] rounded-full border border-white/30 flex items-start justify-center pt-[5px]">
            <span class="block w-[2px] h-[6px] rounded-full bg-white/80" style="animation: mouseDot 1.8s ease-in-out infinite;"></span>
        </div>
        {{-- Animated chevrons --}}
        <div class="flex flex-col items-center gap-0" style="animation: chevronFade 1.8s ease-in-out infinite;">
            <svg class="w-3 h-3 text-white/50" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </div>
    </div>
    <style>
        @keyframes mouseDot {
            0%   { transform: translateY(0);    opacity: 1; }
            60%  { transform: translateY(10px); opacity: 0; }
            61%  { transform: translateY(0);    opacity: 0; }
            100% { transform: translateY(0);    opacity: 1; }
        }
        @keyframes chevronFade {
            0%, 100% { opacity: 0.3; transform: translateY(-2px); }
            50%      { opacity: 0.8; transform: translateY(2px);  }
        }
    </style>

</div>

{{-- ================================================================
     TENTANG KAMI SECTION
     ================================================================ --}}
<section id="tentang-kami" class="bg-white py-24 px-6 overflow-hidden">
    <div class="max-w-6xl mx-auto">

        {{-- Label --}}
        <div class="reveal flex justify-center mb-6">
            <span class="text-xs tracking-[0.3em] uppercase text-slate-400 font-medium border border-slate-200 rounded-full px-4 py-1.5">
                Tentang Kami
            </span>
        </div>

        {{-- Heading --}}
        <h2 class="reveal reveal-delay-1 text-4xl md:text-5xl lg:text-6xl text-slate-900 text-center mb-6 leading-tight"
            style="font-family: 'Instrument Serif', serif;">
            Kami hadir untuk <em>membangun</em><br class="hidden md:block"> kepercayaan digital Anda.
        </h2>

        {{-- Subtitle --}}
        <p class="reveal reveal-delay-2 text-slate-500 text-sm md:text-base leading-relaxed text-center max-w-2xl mx-auto mb-20">
            Sejak 2020, kami telah membantu puluhan bisnis lokal dan nasional
            tampil lebih profesional di dunia digital — dari website company profile
            hingga sistem manajemen yang terintegrasi.
        </p>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-slate-100 rounded-2xl overflow-hidden mb-20">
            <div class="reveal reveal-delay-1 bg-white hover:bg-slate-50 transition-colors px-10 py-10 text-center">
                <p class="text-5xl font-semibold text-slate-900 mb-2" style="font-family: 'Instrument Serif', serif;">50+</p>
                <p class="text-xs tracking-widest uppercase text-slate-400">Klien Aktif</p>
            </div>
            <div class="reveal reveal-delay-2 bg-white hover:bg-slate-50 transition-colors px-10 py-10 text-center">
                <p class="text-5xl font-semibold text-slate-900 mb-2" style="font-family: 'Instrument Serif', serif;">120+</p>
                <p class="text-xs tracking-widest uppercase text-slate-400">Proyek Selesai</p>
            </div>
            <div class="reveal reveal-delay-3 bg-white hover:bg-slate-50 transition-colors px-10 py-10 text-center">
                <p class="text-5xl font-semibold text-slate-900 mb-2" style="font-family: 'Instrument Serif', serif;">5+</p>
                <p class="text-xs tracking-widest uppercase text-slate-400">Tahun Pengalaman</p>
            </div>
        </div>

        {{-- Two columns --}}
        <div class="grid md:grid-cols-2 gap-12 items-center">
            {{-- Left — text --}}
            <div class="reveal space-y-6">
                <h3 class="text-2xl text-slate-900 leading-snug" style="font-family: 'Instrument Serif', serif;">
                    Kami percaya setiap bisnis berhak tampil terbaik secara digital.
                </h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Tim kami terdiri dari desainer, developer, dan konsultan digital yang berpengalaman.
                    Kami tidak hanya membangun website — kami membangun fondasi digital yang kuat
                    untuk pertumbuhan bisnis Anda jangka panjang.
                </p>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Setiap proyek dikerjakan dengan pendekatan yang personal, memahami kebutuhan
                    spesifik klien, dan memastikan hasil akhir tidak hanya indah secara visual
                    tetapi juga fungsional dan mudah dikelola.
                </p>
                <a href="#" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors group">
                    Pelajari lebih lanjut
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

            {{-- Right — cards --}}
            <div class="reveal reveal-delay-2 space-y-4">
                <div class="border border-slate-100 rounded-xl p-6 hover:border-slate-200 hover:shadow-sm transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-800 text-sm font-medium mb-1">Desain yang Berpusat pada Pengguna</p>
                            <p class="text-slate-400 text-xs leading-relaxed">Setiap elemen dirancang untuk memberikan pengalaman terbaik bagi pengguna akhir.</p>
                        </div>
                    </div>
                </div>
                <div class="border border-slate-100 rounded-xl p-6 hover:border-slate-200 hover:shadow-sm transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-800 text-sm font-medium mb-1">Teknologi Modern & Terpercaya</p>
                            <p class="text-slate-400 text-xs leading-relaxed">Dibangun menggunakan stack terkini yang ringan, cepat, dan mudah dikembangkan.</p>
                        </div>
                    </div>
                </div>
                <div class="border border-slate-100 rounded-xl p-6 hover:border-slate-200 hover:shadow-sm transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-slate-800 text-sm font-medium mb-1">Dukungan Pasca-Peluncuran</p>
                            <p class="text-slate-400 text-xs leading-relaxed">Kami tidak berhenti setelah website live — tim kami siap mendukung pertumbuhan Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ================================================================
     LAYANAN SECTION
     ================================================================ --}}
<section id="layanan" class="overflow-hidden px-6">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="reveal pt-20 pb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-slate-200">
            <div>
                <p class="text-[10px] tracking-[0.32em] uppercase text-slate-400 mb-3">Layanan</p>
                <h2 class="text-4xl sm:text-5xl md:text-6xl text-slate-900 leading-[1.05]"
                    style="font-family: 'Instrument Serif', serif;">
                    Apa yang bisa<br>kami bantu.
                </h2>
            </div>
            <a href="{{ route('layanan.index') }}"
               class="reveal reveal-delay-1 inline-flex items-center gap-2 self-start sm:self-auto flex-shrink-0 text-xs tracking-wide text-slate-700 border border-slate-300 rounded-full px-5 py-2.5 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all duration-300">
                Lihat Semua
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        {{-- List --}}
        @if ($layanans->isNotEmpty())
            <div class="flex flex-col gap-3 mt-3">
                @foreach ($layanans as $index => $layanan)
                    <a href="#"
                       class="reveal group flex flex-col sm:flex-row items-stretch bg-white overflow-hidden sm:h-[320px]"
                       style="transition-delay: {{ $index * 0.1 }}s;">

                        {{-- Image — fixed size at sm+, full width on mobile --}}
                        <div class="flex-shrink-0 overflow-hidden w-full h-48 sm:h-auto sm:w-[320px]">
                            @if ($layanan->image)
                                <img src="{{ Storage::url($layanan->image) }}"
                                     alt="{{ $layanan->name }}"
                                     class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.04]">
                            @else
                                <div class="w-full h-full bg-slate-100"></div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 flex flex-col justify-between px-6 sm:px-10 md:px-14 py-8 sm:py-12">
                            <h3 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl text-slate-900 leading-snug font-semibold"
                                style="font-family: 'Instrument Serif', serif;">
                                {{ $layanan->name }}
                            </h3>
                            @if ($layanan->description)
                                <p class="text-slate-500 text-base leading-relaxed mt-4 max-w-md">
                                    {{ $layanan->description }}
                                </p>
                            @endif
                            <span class="text-[10px] tracking-[0.28em] uppercase text-slate-400 mt-6">
                                Layanan Digital &nbsp;·&nbsp; {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        {{-- Arrow --}}
                        <div class="hidden sm:flex flex-shrink-0 items-center pr-8 md:pr-12">
                            <svg class="w-8 h-8 text-slate-300 group-hover:text-slate-700 group-hover:translate-x-2 transition-all duration-300"
                                 fill="none" stroke="currentColor" stroke-width="1.25" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </div>

                    </a>
                @endforeach
            </div>
        @endif

        <div class="pb-24"></div>
    </div>
</section>

{{-- ================================================================
     KLIEN SECTION
     ================================================================ --}}
@if ($kliens->isNotEmpty())
<section id="klien" class="bg-white overflow-hidden py-20">

    {{-- Heading --}}
    <div class="reveal text-center mb-12 px-6">
        <h2 class="text-3xl md:text-4xl text-slate-900"
            style="font-family: 'Instrument Serif', serif;">
            Beberapa klien kami
        </h2>
    </div>

    {{-- Marquee — satu baris --}}
    @php $klienArr = $kliens->all(); @endphp
    <div class="relative overflow-hidden">
        {{-- Fade edges --}}
        <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-24 z-10"
             style="background: linear-gradient(to right, #fff, transparent);"></div>
        <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-24 z-10"
             style="background: linear-gradient(to left, #fff, transparent);"></div>

        <div class="marquee-l whitespace-nowrap py-2">
            @foreach ([...$klienArr, ...$klienArr] as $klien)
                <span class="inline-flex items-center justify-center mx-3
                             border border-slate-200 rounded-xl
                             px-8 py-5 bg-white"
                      style="min-width: 180px; height: 88px;">
                    @if ($klien->logo_klien)
                        <img src="{{ Storage::url($klien->logo_klien) }}"
                             alt="{{ $klien->nama_klien }}"
                             class="max-h-12 w-auto object-contain">
                    @else
                        <span class="text-slate-300 text-sm tracking-wide">{{ $klien->nama_klien }}</span>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

</section>

<style>
    @keyframes marquee-l {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
    .marquee-l { display: inline-block; animation: marquee-l 40s linear infinite; }
    .marquee-l:hover { animation-play-state: paused; }
</style>
@endif

{{-- ================================================================
     BANNER / CTA SECTION
     ================================================================ --}}
@if ($banner)
<section class="relative flex items-center justify-center text-center overflow-hidden"
         style="min-height: 60vh;">

    {{-- Background image --}}
    @if ($banner->gambar_banner)
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ Storage::url($banner->gambar_banner) }}');"></div>
    @else
        <div class="absolute inset-0 bg-slate-900"></div>
    @endif

    {{-- Dark overlay agar teks terbaca --}}
    <div class="absolute inset-0 bg-black/85"></div>

    {{-- Content --}}
    <div class="relative z-10 flex flex-col items-center px-6 py-16">

        @php
            $bannerWords = explode(' ', $banner->nama_banner);
            $bannerLine1 = implode(' ', array_slice($bannerWords, 0, 2));
            $bannerLine2 = implode(' ', array_slice($bannerWords, 2));
        @endphp
        <h2 class="text-white leading-[1.04] mb-5"
            style="font-family: 'Instrument Serif', serif;
                   font-size: clamp(2.5rem, 7vw, 6rem);">
            {{ $bannerLine1 }}@if($bannerLine2)<br>{{ $bannerLine2 }}@endif
        </h2>

        @if ($banner->deskripsi_banner)
            <p class="text-white/60 text-sm md:text-base leading-relaxed max-w-lg mb-10">
                {{ $banner->deskripsi_banner }}
            </p>
        @endif

        <a href="#kontak"
           class="inline-flex items-center gap-3 bg-white text-gray-900 font-medium text-sm
                  px-8 py-4 rounded-full hover:bg-white/90 transition-colors duration-200">
            Hubungi Kami
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
        </a>

    </div>

</section>
@endif

<x-footer />

<style>
    .reveal {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
</style>

<script>
    (function () {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach(function (el) {
            observer.observe(el);
        });
    })();
</script>

<script>
    (function () {
        const video = document.getElementById('hero-video');
        if (!video) return;

        let rafId = null;
        let fadingOutRef = false;

        function cancelFade() {
            if (rafId !== null) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        }

        function fadeIn(duration) {
            cancelFade();
            const startTime = performance.now();
            const startOpacity = parseFloat(video.style.opacity) || 0;

            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                video.style.opacity = startOpacity + (1 - startOpacity) * progress;
                if (progress < 1) {
                    rafId = requestAnimationFrame(step);
                } else {
                    rafId = null;
                }
            }

            rafId = requestAnimationFrame(step);
        }

        function fadeOut(duration) {
            cancelFade();
            const startTime = performance.now();
            const startOpacity = parseFloat(video.style.opacity) || 1;

            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                video.style.opacity = startOpacity * (1 - progress);
                if (progress < 1) {
                    rafId = requestAnimationFrame(step);
                } else {
                    rafId = null;
                }
            }

            rafId = requestAnimationFrame(step);
        }

        video.addEventListener('canplay', function onReady() {
            video.removeEventListener('canplay', onReady);
            fadingOutRef = false;
            video.play().then(function () {
                fadeIn(500);
            }).catch(function () {});
        });

        video.addEventListener('timeupdate', function () {
            if (!fadingOutRef && video.duration > 0 && (video.duration - video.currentTime) <= 0.55) {
                fadingOutRef = true;
                fadeOut(500);
            }
        });

        video.addEventListener('ended', function () {
            cancelFade();
            video.style.opacity = '0';
            setTimeout(function () {
                video.currentTime = 0;
                fadingOutRef = false;
                video.play().then(function () {
                    fadeIn(500);
                }).catch(function () {});
            }, 100);
        });
    })();
</script>

<script>
    (function () {
        const videoEl      = document.getElementById('hero-video');
        const heroContent  = document.getElementById('hero-content');
        const heroSection  = videoEl ? videoEl.closest('.min-h-screen') : null;

        if (!videoEl || !heroContent) return;

        let ticking = false;

        function updateParallax() {
            const scrollY = window.scrollY;
            const viewH   = window.innerHeight;

            // Only apply while hero is visible
            if (scrollY < viewH * 1.2) {
                // Video moves down at 40% of scroll speed → looks like it stays behind
                videoEl.style.transform = `translateY(calc(17% + ${scrollY * 0.4}px))`;

                // Hero text drifts upward slightly → feels like it floats away
                heroContent.style.transform = `translateY(calc(-8% + ${scrollY * -0.12}px))`;
            }

            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });

        // Set initial state so CSS class and JS are in sync from the start
        updateParallax();
    })();
</script>

</body>
</html>
