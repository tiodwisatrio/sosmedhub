<?php

use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

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
        ->add(Url::create(url('/'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

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
