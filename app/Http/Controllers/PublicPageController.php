<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Halaman publik (beranda, kebijakan privasi, ketentuan, penghapusan data). Tiap bahasa punya
 * view sendiri di resources/views/public/{kode}/, karena teks hukum lebih mudah ditinjau sebagai
 * satu dokumen utuh daripada sebagai ratusan kunci terjemahan. Bahasa sumber (Indonesia) ada di
 * resources/views/public/ dan menjadi cadangan bila view bahasa lain belum ada.
 */
class PublicPageController extends Controller
{
    public function __invoke(string $page): View
    {
        $localized = 'public.'.app()->getLocale().'.'.$page;

        return view(view()->exists($localized) ? $localized : 'public.'.$page);
    }
}
