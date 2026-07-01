<?php

use Illuminate\Support\Facades\Route;
use Modules\Team\Http\Controllers\Admin\TeamController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::resource('teams', TeamController::class);
    });
