<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta
        :title="$post->title"
        :description="strip_tags($post->content ?? '')"
        :image="$post->image ? Storage::url($post->image) : null"
        type="article"
    />
    @if ($siteSetting->icon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif

    @php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'datePublished' => $post->created_at->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author ?: ($siteSetting->app_name ?? config('app.name')),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteSetting->app_name ?? config('app.name'),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current(),
            ],
        ];

        if ($post->image) {
            $jsonLd['image'] = [Storage::url($post->image)];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');

        .post-content { color: #334155; line-height: 1.75; }
        .post-content p { margin-bottom: 1.25rem; }
        .post-content h1, .post-content h2, .post-content h3 { color: #0f172a; font-weight: 600; margin: 1.75rem 0 0.75rem; }
        .post-content ul, .post-content ol { margin: 0 0 1.25rem 1.5rem; }
        .post-content ul { list-style: disc; }
        .post-content ol { list-style: decimal; }
        .post-content blockquote { border-left: 3px solid #cbd5e1; padding-left: 1rem; color: #64748b; font-style: italic; margin: 1.25rem 0; }
        .post-content a { color: #0f172a; text-decoration: underline; }
    </style>
</head>
<body>

<x-navbar />

{{-- ================================================================
     PAGE HEADER
     ================================================================ --}}
<section class="bg-black pt-40 pb-16 px-6 text-center">
    @if ($post->category)
        <p class="text-[10px] tracking-[0.32em] uppercase text-white/40 mb-4">
            {{ $post->category->name }}
        </p>
    @endif
    <h1 class="text-3xl md:text-5xl text-white leading-tight max-w-4xl mx-auto"
        style="font-family: 'Instrument Serif', serif;">
        {{ $post->title }}
    </h1>
    <p class="text-xs text-white/40 mt-6">
        {{ $post->created_at->translatedFormat('d F Y') }}
        @if ($post->author)
            &nbsp;·&nbsp; {{ $post->author }}
        @endif
    </p>
</section>

{{-- ================================================================
     POST CONTENT
     ================================================================ --}}
<section class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-16">
        @if ($post->image)
            <img src="{{ Storage::url($post->image) }}"
                 alt="{{ $post->title }}"
                 class="w-full h-auto rounded-xl object-cover mb-10">
        @endif

        <div class="post-content">
            {!! $post->content !!}
        </div>

        <div class="mt-12 pt-8 border-t border-slate-100">
            <a href="{{ route('posts.index') }}" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
                &larr; Kembali ke daftar post
            </a>
        </div>
    </div>
</section>

<x-footer />

</body>
</html>
