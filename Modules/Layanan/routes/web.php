<?php

use Illuminate\Support\Facades\Route;
use Modules\Layanan\Http\Controllers\Admin\LayananController;
use Modules\Layanan\Http\Controllers\Frontend\LayananController as FrontendLayananController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('layanans', LayananController::class);
    });

Route::get('layanan', [FrontendLayananController::class, 'index'])->name('layanan.index');
