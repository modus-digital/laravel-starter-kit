<?php

declare(strict_types=1);

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function (): void {
        // * Task routes
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::patch('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

        // * Task View routes
        Route::prefix('views')
            ->name('views.')
            ->group(function (): void {
                Route::post('/', [TaskController::class, 'createView'])->name('create');
                Route::patch('/{taskView}', [TaskController::class, 'updateView'])->name('update');
                Route::patch('/{taskView}/default', [TaskController::class, 'makeDefaultView'])->name('makeDefault');
                Route::delete('/{taskView}', [TaskController::class, 'deleteView'])->name('delete');
            });
    });
