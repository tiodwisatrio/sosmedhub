<?php

use Illuminate\Support\Facades\Route;
use Modules\SiteSetting\Http\Controllers\Admin\SiteSettingController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::get('site-settings', [SiteSettingController::class, 'index'])->name('site-settings.index');
        Route::put('site-settings', [SiteSettingController::class, 'update'])->name('site-settings.update');
    });
