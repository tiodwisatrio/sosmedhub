<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta title="Layanan" description="Layanan yang kami sediakan untuk membantu kebutuhan Anda." />
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');
    </style>
</head>
<body>

<x-navbar />

{{-- ================================================================
     PAGE HEADER
     ================================================================ --}}
<section class="bg-black pt-40 pb-20 px-6 text-center">
    <p class="text-[10px] tracking-[0.32em] uppercase text-white/40 mb-4">Layanan</p>
    <h1 class="text-4xl md:text-5xl lg:text-6xl text-white leading-tight"
        style="font-family: 'Instrument Serif', serif;">
        Apa yang bisa kami bantu.
    </h1>
</section>

{{-- ================================================================
     LAYANAN LIST
     ================================================================ --}}
<section class="bg-white">
    <div class="max-w-6xl mx-auto">
        @if ($layanans->isNotEmpty())
            <div class="flex flex-col gap-3 py-16">
                @foreach ($layanans as $index => $layanan)
                    <div class="flex flex-col sm:flex-row items-stretch bg-white overflow-hidden sm:h-[320px]">

                        {{-- Image — fixed size at sm+, full width on mobile --}}
                        <div class="flex-shrink-0 overflow-hidden w-full h-48 sm:h-auto sm:w-[320px]">
                            @if ($layanan->image)
                                <img src="{{ Storage::url($layanan->image) }}"
                                     alt="{{ $layanan->name }}"
                                     class="w-full h-full object-cover">
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
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-400 text-center py-24">Belum ada layanan yang tersedia.</p>
        @endif
    </div>
</section>

<x-footer />

</body>
</html>
