<?php

use Illuminate\Support\Facades\Route;
use Modules\Post\Http\Controllers\Admin\PostController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('posts', PostController::class)->except(['show']);
    });