<?php

use Illuminate\Support\Facades\Route;
use Modules\SiteSetting\Http\Controllers\SiteSettingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sitesettings', SiteSettingController::class)->names('sitesetting');
});
