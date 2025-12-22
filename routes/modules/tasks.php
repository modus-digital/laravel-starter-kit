<?php

declare(strict_types=1);

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');
});
