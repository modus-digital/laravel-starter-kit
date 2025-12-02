<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::get('docs', DocumentationController::class)->name('api.docs');
Route::get('openapi.json', [DocumentationController::class, 'specFile'])->name('api.openapi.json');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function (): void {
    // Current user endpoint
    Route::get('/me', MeController::class)->name('api.me');

    // User management routes
    Route::prefix('users')->name('api.users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::patch('/{user}', [UserController::class, 'update'])->name('patch');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Soft delete management
        Route::patch('/{userId}/restore', [UserController::class, 'restore'])->name('restore');
        Route::delete('/{userId}/force-delete', [UserController::class, 'forceDelete'])->name('force-delete');
    });
});
