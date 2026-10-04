<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>Penjadwalan - {{ config('app.name', 'Laravel') }}</title>

        {{-- Vite CSS --}}
        {{-- {{ module_vite('build-scheduler', 'resources/assets/sass/app.scss') }} --}}
    </head>

    <body>
        {{ $slot }}

        {{-- Vite JS --}}
        {{-- {{ module_vite('build-scheduler', 'resources/assets/js/app.js') }} --}}
    </body>
</html>