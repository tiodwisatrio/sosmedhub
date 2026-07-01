<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\Models\Banner;
use Modules\Hero\Models\Hero;
use Modules\Klien\Models\Klien;
use Modules\Layanan\Models\Layanan;

Route::get('/', function () {
    $hero     = Hero::latest()->first();
    $layanans = Layanan::where('status', 1)->orderBy('urutan')->get();
    $kliens   = Klien::where('status', 1)->orderBy('urutan')->get();
    $banner   = Banner::where('status', 1)->latest()->first();
    return view('welcome', compact('hero', 'layanans', 'kliens', 'banner'));
});

Route::view('kontak', 'kontak')->name('kontak');

Route::redirect('dashboard', '/admin/dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
