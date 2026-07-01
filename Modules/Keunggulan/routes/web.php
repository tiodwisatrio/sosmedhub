<?php

use Illuminate\Support\Facades\Route;
use Modules\Keunggulan\Http\Controllers\Admin\KeunggulanController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('keunggulans', KeunggulanController::class);
    });