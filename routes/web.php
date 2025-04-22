<?php

use Illuminate\Support\Facades\Route;
use TekPart\License\Http\Controllers\LicenseController;

// مسارات واجهة المستخدم للتراخيص
Route::middleware(['web'])->prefix('license')->group(function () {
    // صفحة عرض حالة الترخيص
    Route::get('/status', [LicenseController::class, 'status'])->name('license.status');

    // صفحة التحقق من الترخيص
    Route::post('/verify', [LicenseController::class, 'verify'])->name('license.verify');

    // صفحة الترخيص غير الصالح
    Route::get('/invalid', [LicenseController::class, 'invalid'])->name('license.invalid');

    // صفحة تفعيل الترخيص
    Route::get('/activate', [LicenseController::class, 'activateForm'])->name('license.activate.form');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('license.activate');
});