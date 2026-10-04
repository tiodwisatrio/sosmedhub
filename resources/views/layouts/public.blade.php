<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta
        :title="trim($__env->yieldContent('title')) ?: null"
        :description="trim($__env->yieldContent('description')) ?: null" />
    @if ($siteSetting->icon ?? null)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black font-sans text-white antialiased">
    <x-navbar />

    <main>
        @yield('content')
    </main>

    <x-footer />
</body>
</html>
