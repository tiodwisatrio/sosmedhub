<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta
        title="Tentang Kami"
        :description="strip_tags($tentangKami->tentangkami_deskripsi ?? '')"
        :image="$tentangKami->tentangkami_gambar ? Storage::url($tentangKami->tentangkami_gambar) : null"
    />
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');

        .about-content { color: #334155; line-height: 1.75; }
        .about-content p { margin-bottom: 1.25rem; }
        .about-content ul, .about-content ol { margin: 0 0 1.25rem 1.5rem; }
        .about-content ul { list-style: disc; }
        .about-content ol { list-style: decimal; }
        .about-content a { color: #0f172a; text-decoration: underline; }
    </style>
</head>
<body>

<x-navbar />

{{-- ================================================================
     PAGE HEADER
     ================================================================ --}}
<section class="bg-black pt-40 pb-20 px-6 text-center">
    <p class="text-[10px] tracking-[0.32em] uppercase text-white/40 mb-4">
        {{ $tentangKami->tentangkami_tagline ?: 'Tentang Kami' }}
    </p>
    <h1 class="text-4xl md:text-5xl lg:text-6xl text-white leading-tight"
        style="font-family: 'Instrument Serif', serif;">
        {{ $tentangKami->tentangkami_judul }}
    </h1>
</section>

{{-- ================================================================
     DESKRIPSI
     ================================================================ --}}
<section class="bg-white py-16 md:py-24 px-6">
    <div class="max-w-3xl mx-auto">
        @if ($tentangKami->tentangkami_gambar)
            <img src="{{ Storage::url($tentangKami->tentangkami_gambar) }}"
                 alt="{{ $tentangKami->tentangkami_judul }}"
                 class="w-full h-auto rounded-xl object-cover mb-12">
        @endif

        <div class="about-content">
            {!! $tentangKami->tentangkami_deskripsi !!}
        </div>
    </div>
</section>

{{-- ================================================================
     STATISTIK
     ================================================================ --}}
@if ($tentangKami->stats->isNotEmpty())
    <section class="bg-white pb-16 md:pb-24 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(140px,1fr))] gap-px bg-slate-100 rounded-2xl overflow-hidden">
                @foreach ($tentangKami->stats as $stat)
                    <div class="bg-white hover:bg-slate-50 transition-colors px-6 py-10 text-center">
                        @if ($stat->tentangkami_stats_gambar)
                            <img src="{{ Storage::url($stat->tentangkami_stats_gambar) }}"
                                 alt="{{ $stat->tentangkami_stats_label }}"
                                 class="w-8 h-8 object-contain mx-auto mb-3">
                        @endif
                        <p class="text-4xl md:text-5xl font-semibold text-slate-900 mb-2" style="font-family: 'Instrument Serif', serif;">
                            {{ $stat->tentangkami_stats_angka }}+
                        </p>
                        <p class="text-xs tracking-widest uppercase text-slate-400">
                            {{ $stat->tentangkami_stats_label }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ================================================================
     VISI & MISI
     ================================================================ --}}
@if ($tentangKami->tentangkami_visi || $tentangKami->tentangkami_misi)
    <section class="bg-slate-50 py-16 md:py-24 px-6">
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12">
            @if ($tentangKami->tentangkami_visi)
                <div>
                    <p class="text-[10px] tracking-[0.32em] uppercase text-slate-400 mb-3">Visi</p>
                    <p class="text-slate-800 text-lg md:text-xl leading-relaxed"
                       style="font-family: 'Instrument Serif', serif;">
                        {{ $tentangKami->tentangkami_visi }}
                    </p>
                </div>
            @endif

            @if ($tentangKami->tentangkami_misi)
                <div>
                    <p class="text-[10px] tracking-[0.32em] uppercase text-slate-400 mb-3">Misi</p>
                    <div class="about-content">
                        {!! $tentangKami->tentangkami_misi !!}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif

<x-footer />

</body>
</html>
