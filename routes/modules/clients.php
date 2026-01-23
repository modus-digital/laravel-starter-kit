<?php

declare(strict_types=1);

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
    Route::post('/clients/{client}/switch', ClientSwitchController::class)
        ->name('clients.switch');
});
