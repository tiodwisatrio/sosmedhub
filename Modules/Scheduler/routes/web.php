<?php

use Illuminate\Support\Facades\Route;
use Modules\Scheduler\Http\Controllers\Admin\PostHistoryController;
use Modules\Scheduler\Http\Controllers\Admin\ScheduledPostController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::get('post-history', [PostHistoryController::class, 'index'])->name('post-history.index');

        Route::resource('scheduled-posts', ScheduledPostController::class)->except(['show']);

        Route::post('scheduled-posts/{scheduled_post}/duplicate', [ScheduledPostController::class, 'duplicate'])
            ->name('scheduled-posts.duplicate');

        Route::patch('scheduled-posts/{scheduled_post}/cancel', [ScheduledPostController::class, 'cancel'])
            ->name('scheduled-posts.cancel');
    });
