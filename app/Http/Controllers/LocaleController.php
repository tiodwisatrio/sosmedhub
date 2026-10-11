<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Menyimpan pilihan bahasa di cookie (setahun) dan sesi, lalu kembali ke halaman asal.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(config('sosmedhub.locales', [])))],
        ]);

        $request->session()->put(SetLocale::COOKIE, $validated['locale']);

        // Disimpan juga di akun supaya email notifikasi memakai bahasa ini dan pilihan terbawa ke perangkat lain.
        $request->user()?->forceFill(['locale' => $validated['locale']])->save();

        return back()->withCookie(Cookie::make(SetLocale::COOKIE, $validated['locale'], 60 * 24 * 365));
    }
}
