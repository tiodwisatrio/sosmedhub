<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $siteSetting->app_name ?? config('app.name') }}</title>

        @if ($siteSetting->icon ?? null)
            <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSetting->icon) }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f1dfd5] font-sans text-[#1c1c1c] antialiased">
        <div class="flex min-h-screen items-center justify-center p-4 sm:p-8">
            <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl md:min-h-[620px] md:grid-cols-2">
                <div class="auth-bg hidden bg-cover bg-center md:block" aria-hidden="true"></div>

                <div class="relative flex flex-col justify-center px-6 py-14 sm:px-12">
                    <div class="absolute left-6 right-6 top-5 flex items-center justify-between sm:left-12 sm:right-8">
                        <a href="{{ url('/') }}" class="text-sm font-semibold">{{ $siteSetting->app_name ?? config('app.name') }}</a>
                        <x-language-switcher />
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
