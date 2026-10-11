<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan bahasa antarmuka tiap permintaan: pilihan di cookie/sesi bila valid, selain itu bawaan aplikasi.
 */
class SetLocale
{
    public const COOKIE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('sosmedhub.locales', []));
        // Urutan: cookie perangkat ini, lalu pilihan tersimpan di akun (lintas perangkat), lalu bawaan.
        $chosen = $request->cookie(self::COOKIE)
            ?? $request->session()->get(self::COOKIE)
            ?? $request->user()?->locale;

        $locale = in_array($chosen, $supported, true)
            ? $chosen
            : config('sosmedhub.default_locale', 'id');

        app()->setLocale($locale);

        return $next($request);
    }
}
