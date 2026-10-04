<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\Admin\RoleController;

Route::prefix('admin')
    ->middleware(['auth', 'approved'])
    ->name('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });
