<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

// Beranda tampil untuk semua. User yang sudah masuk melihat tombol ke dashboard sebagai ganti Masuk/Daftar.
Route::view('/', 'public.home')->name('home');

// Pilihan bahasa antarmuka; tersedia untuk tamu dan user yang sudah masuk.
Route::post('bahasa', LocaleController::class)->name('locale.update');

Route::view('kebijakan-privasi', 'public.privacy')->name('privacy');
Route::view('ketentuan-layanan', 'public.terms')->name('terms');
Route::view('hapus-data', 'public.data-deletion')->name('data-deletion');

Route::redirect('dashboard', '/admin/dashboard')
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'approved'])
    ->name('profile');

Route::view('approval-pending', 'auth.pending-approval')
    ->middleware(['auth'])
    ->name('approval.pending');

Route::get('sitemap.xml', function () {
    $sitemap = Sitemap::create()
        ->add(Url::create(url('/'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
        ->add(Url::create(route('privacy'))->setPriority(0.3)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
        ->add(Url::create(route('terms'))->setPriority(0.3)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
        ->add(Url::create(route('data-deletion'))->setPriority(0.3)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

    return $sitemap->toResponse(request());
})->name('sitemap');

Route::get('robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /forgot-password',
        'Disallow: /reset-password',
        'Disallow: /profile',
        'Disallow: /confirm-password',
        'Disallow: /verify-email',
        '',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

require __DIR__.'/auth.php';
