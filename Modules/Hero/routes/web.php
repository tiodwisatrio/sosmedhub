<?php

use Illuminate\Support\Facades\Route;
use Modules\Hero\Http\Controllers\Admin\HeroController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('heroes', HeroController::class)->except(['show']);
    });
