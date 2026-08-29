<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\Models\Banner;
use Modules\Hero\Models\Hero;
use Modules\Klien\Models\Klien;
use Modules\Layanan\Models\Layanan;
use Modules\Post\Models\Post;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::get('/', function () {
    $hero = Hero::latest()->first();
    $layanans = Layanan::where('status', 1)->orderBy('urutan')->get();
    $kliens = Klien::where('status', 1)->orderBy('urutan')->get();
    $banner = Banner::where('status', 1)->latest()->first();

    return view('welcome', compact('hero', 'layanans', 'kliens', 'banner'));
});

Route::view('kontak', 'kontak')->name('kontak');

Route::redirect('dashboard', '/admin/dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('sitemap.xml', function () {
    $sitemap = Sitemap::create()
        ->add(Url::create(url('/'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
        ->add(Url::create(route('kontak'))->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
        ->add(Url::create(route('tentang-kami.index'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
        ->add(Url::create(route('layanan.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
        ->add(Url::create(route('posts.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

    Post::where('status', 1)->get()->each(
        fn (Post $post) => $sitemap->add(
            Url::create(route('posts.show', $post))
                ->setLastModificationDate($post->updated_at)
                ->setPriority(0.6)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        )
    );

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
