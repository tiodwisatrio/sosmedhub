<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta title="Akses Ditolak" description="Anda tidak memiliki akses ke halaman ini." />
    <meta name="robots" content="noindex, nofollow">
    @if ($siteSetting->icon ?? null)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Kanit:wght@600;700;800&display=swap');
    </style>
</head>
<body class="bg-[#f1dfd5] font-sans text-[#1c1c1c] antialiased">

<x-navbar-light />

<section class="bg-[#f1dfd5] min-h-screen flex items-center justify-center px-6 text-center">
    <div>
        <p class="text-[10px] tracking-[0.32em] uppercase text-[#1c1c1c]/40 mb-4">403</p>
        <h1 class="text-5xl md:text-7xl text-[#1c1c1c] leading-tight font-display font-bold">
            Akses ditolak.
        </h1>
        <p class="text-[#1c1c1c]/50 mt-6 max-w-md mx-auto leading-relaxed">
            Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika ini seharusnya tidak terjadi.
        </p>
        @auth
            <a href="{{ url('/admin/dashboard') }}"
               class="mt-10 inline-block bg-[#1c1c1c] transition hover:bg-black px-8 py-3 text-white text-sm font-medium">
                Kembali ke Dashboard
            </a>
        @else
            <a href="{{ url('/') }}"
               class="mt-10 inline-block bg-[#1c1c1c] transition hover:bg-black px-8 py-3 text-white text-sm font-medium">
                Kembali ke Beranda
            </a>
        @endauth
    </div>
</section>

<x-footer />

</body>
</html>
