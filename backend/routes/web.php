<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'ToDoTest API',
]));

Route::prefix('api/admin')->name('api.admin.')->group(function (): void {
    Route::get('csrf-cookie', [AdminAuthController::class, 'csrfCookie'])->name('csrf-cookie');
    Route::post('login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login');

    Route::middleware('auth:admin')->group(function (): void {
        Route::get('session', [AdminAuthController::class, 'show'])->name('session.show');
        Route::delete('session', [AdminAuthController::class, 'logout'])->name('session.destroy');
    });
});
