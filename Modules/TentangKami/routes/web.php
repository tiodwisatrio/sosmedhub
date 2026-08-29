<?php

use Illuminate\Support\Facades\Route;
use Modules\TentangKami\Http\Controllers\Admin\TentangKamiController;
use Modules\TentangKami\Http\Controllers\Frontend\TentangKamiController as FrontendTentangKamiController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::get('tentang-kami', [TentangKamiController::class, 'index'])->name('tentang-kami.index');
        Route::put('tentang-kami', [TentangKamiController::class, 'update'])->name('tentang-kami.update');
    });

Route::get('tentang-kami', [FrontendTentangKamiController::class, 'index'])->name('tentang-kami.index');
