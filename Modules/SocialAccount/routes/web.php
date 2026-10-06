<?php

use Illuminate\Support\Facades\Route;
use Modules\SocialAccount\Http\Controllers\Admin\FacebookConnectController;
use Modules\SocialAccount\Http\Controllers\Admin\SocialAccountController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::get('social-accounts/instagram/redirect', [SocialAccountController::class, 'redirect'])
            ->name('social-accounts.instagram.redirect');

        Route::get('social-accounts/instagram/callback', [SocialAccountController::class, 'callback'])
            ->name('social-accounts.instagram.callback');

        Route::get('social-accounts/facebook/redirect', [FacebookConnectController::class, 'redirect'])
            ->name('social-accounts.facebook.redirect');

        Route::get('social-accounts/facebook/callback', [FacebookConnectController::class, 'callback'])
            ->name('social-accounts.facebook.callback');

        Route::get('social-accounts/facebook/pages', [FacebookConnectController::class, 'pages'])
            ->name('social-accounts.facebook.pages');

        Route::post('social-accounts/facebook/pages', [FacebookConnectController::class, 'connect'])
            ->name('social-accounts.facebook.connect');

        Route::resource('social-accounts', SocialAccountController::class)->except(['show']);
    });
