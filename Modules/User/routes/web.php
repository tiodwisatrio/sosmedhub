<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Admin\UserController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::patch('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::patch('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::patch('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

        Route::resource('users', UserController::class)->except(['show']);
    });
