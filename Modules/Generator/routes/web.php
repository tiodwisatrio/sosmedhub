<?php

use Illuminate\Support\Facades\Route;
use Modules\Generator\Http\Controllers\Admin\GeneratorController;

Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {
        Route::get('generator', [GeneratorController::class, 'index'])->name('generator.index');
        Route::post('generator', [GeneratorController::class, 'store'])->name('generator.store');
    });
