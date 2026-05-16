<?php

use Illuminate\Support\Facades\Route;

// Routes registered with web middleware via bootstrap/app.php.
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest only (login)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login',  [\App\Http\Controllers\Admin\Auth\LoginController::class, 'show'])->name('login');
        Route::post('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'authenticate']);
    });

    // Authenticated admin only
    Route::middleware('auth:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}',                       [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/grant-premium',        [\App\Http\Controllers\Admin\UserController::class, 'grantPremium'])->name('users.grant-premium');
        Route::post('users/{user}/revoke-premium',       [\App\Http\Controllers\Admin\UserController::class, 'revokePremium'])->name('users.revoke-premium');
    });
});
