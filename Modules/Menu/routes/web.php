<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Admin\MenuController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::post('menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');

        Route::resource('menus', MenuController::class)->except('show');
    });
