<?php

use Illuminate\Support\Facades\Route;
use Modules\Scheduler\Http\Controllers\Admin\ScheduledPostController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::resource('scheduled-posts', ScheduledPostController::class)->except(['show']);

        Route::patch('scheduled-posts/{scheduled_post}/cancel', [ScheduledPostController::class, 'cancel'])
            ->name('scheduled-posts.cancel');
    });
