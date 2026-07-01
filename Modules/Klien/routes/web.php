<?php

use Illuminate\Support\Facades\Route;
use Modules\Klien\Http\Controllers\Admin\KlienController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('kliens', KlienController::class)->except(['show']);
    });
