<?php

declare(strict_types=1);

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSwitchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Module Routes
|--------------------------------------------------------------------------
|
| These routes are loaded when the clients module is enabled.
|
*/

Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::name('clients.')
        ->group(function (): void {
            // Client portal dashboard
            Route::get('/', [ClientController::class, 'show'])
                ->name('show');

            // Client management routes
            Route::prefix('manage')
                ->name('manage.')
                ->group(function (): void {
                    Route::get('/users', [ClientController::class, 'users'])
                        ->name('users');

                    Route::get('/settings', [ClientController::class, 'settings'])
                        ->name('settings');

                    Route::put('/settings', [ClientController::class, 'updateSettings'])
                        ->name('settings.update');
                });

            // Activities route
            Route::get('/activities', [ClientController::class, 'activities'])
                ->name('activities');

            // Client switch route
            Route::post('/switch-client/{client}', ClientSwitchController::class)
                ->name('switch');
        });
});
