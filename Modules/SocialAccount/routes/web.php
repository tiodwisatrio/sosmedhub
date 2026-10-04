<?php

use Illuminate\Support\Facades\Route;
use Modules\SocialAccount\Http\Controllers\Admin\SocialAccountController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::get('social-accounts/instagram/redirect', [SocialAccountController::class, 'redirect'])
            ->name('social-accounts.instagram.redirect');

        Route::get('social-accounts/instagram/callback', [SocialAccountController::class, 'callback'])
            ->name('social-accounts.instagram.callback');

        Route::resource('social-accounts', SocialAccountController::class)->except(['show']);
    });
