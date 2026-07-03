<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta title="Post" description="Kabar dan wawasan terbaru dari kami." />
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
    <p class="text-[10px] tracking-[0.32em] uppercase text-white/40 mb-4">Post</p>
    <h1 class="text-4xl md:text-5xl lg:text-6xl text-white leading-tight"
        style="font-family: 'Instrument Serif', serif;">
        Kabar &amp; wawasan terbaru.
    </h1>
</section>

{{-- ================================================================
     POST LIST
     ================================================================ --}}
<section class="bg-white">
    <div class="max-w-6xl mx-auto px-6">
        @if ($posts->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 py-16">
                @foreach ($posts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="group block">
                        <div class="w-full h-48 overflow-hidden bg-slate-100">
                            @if ($post->image)
                                <img src="{{ Storage::url($post->image) }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="pt-5">
                            @if ($post->category)
                                <span class="text-[10px] tracking-[0.28em] uppercase text-slate-400">
                                    {{ $post->category->name }}
                                </span>
                            @endif
                            <h3 class="text-xl md:text-2xl text-slate-900 leading-snug font-semibold mt-2 group-hover:text-slate-600 transition-colors"
                                style="font-family: 'Instrument Serif', serif;">
                                {{ $post->title }}
                            </h3>
                            @if ($post->content)
                                <p class="text-slate-500 text-sm leading-relaxed mt-2">
                                    {{ Str::limit(strip_tags($post->content), 110) }}
                                </p>
                            @endif
                            <p class="text-xs text-slate-400 mt-3">
                                {{ $post->created_at->translatedFormat('d F Y') }}
                                @if ($post->author)
                                    &nbsp;·&nbsp; {{ $post->author }}
                                @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pb-16">
                {{ $posts->links() }}
            </div>
        @else
            <p class="text-slate-400 text-center py-24">Belum ada post yang tersedia.</p>
        @endif
    </div>
</section>

<x-footer />

</body>
</html>
