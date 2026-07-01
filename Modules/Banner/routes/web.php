<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\Http\Controllers\Admin\BannerController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('banners', BannerController::class)->except(['show']);
    });
