<?php

use Illuminate\Support\Facades\Route;
use Modules\Keunggulan\Http\Controllers\KeunggulanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('keunggulans', KeunggulanController::class)->names('keunggulan');
});
