<?php

use Illuminate\Support\Facades\Route;
use TekPart\License\Http\Controllers\Api\LicenseApiController;

// API routes for license verification
Route::prefix('api/license')->middleware(['api'])->group(function () {
    // License validation
    Route::post('/validate', [LicenseApiController::class, 'validate']);

    // License activation
    Route::post('/activate', [LicenseApiController::class, 'activate']);

    // License deactivation
    Route::post('/deactivate', [LicenseApiController::class, 'deactivate']);

    // License check
    Route::post('/check', [LicenseApiController::class, 'check']);

    // Token generation
    Route::post('/token/generate', [LicenseApiController::class, 'generateToken']);

    // Token verification
    Route::post('/token/verify', [LicenseApiController::class, 'verifyToken']);
});
