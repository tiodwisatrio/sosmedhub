<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Http\Controllers\Admin\FaqController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('faqs', FaqController::class)->except(['show']);
    });
