<?php

use Illuminate\Support\Facades\Route;
use Modules\Post\Http\Controllers\Admin\PostController;
use Modules\Post\Http\Controllers\Frontend\PostController as FrontendPostController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('posts', PostController::class)->except(['show']);
    });

Route::get('posts', [FrontendPostController::class, 'index'])->name('posts.index');
Route::get('posts/{post:slug}', [FrontendPostController::class, 'show'])->name('posts.show');
