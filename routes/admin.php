<?php

use Illuminate\Support\Facades\Route;

// Routes registered with web middleware via bootstrap/app.php.
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest only (login)
    Route::middleware('guest:admin')->group(function () {
        // Phase 3 — Admin\Auth\LoginController endpoints
    });

    // Authenticated admin only
    Route::middleware('auth:admin')->group(function () {
        // Phase 3-8 — admin endpoints
    });
});
