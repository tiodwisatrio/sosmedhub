<?php

use Illuminate\Support\Facades\Route;
use Modules\Paket\Http\Controllers\Admin\PaketController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('pakets', PaketController::class)->except(['show']);
    });
