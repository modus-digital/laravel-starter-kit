<?php

declare(strict_types=1);

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('tasks')
    ->name('tasks.')
    ->controller(TaskController::class)
    ->group(function (): void {
        // Tasks
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');

        Route::prefix('{task}')->group(function (): void {
            Route::get('/', 'show')->name('show');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');

            // Comments
            Route::prefix('comments')->name('comments.')->group(function (): void {
                Route::post('/', 'addComment')->name('add');
            });

            // Activities (API for dialogs)
            Route::get('/activities', 'activities')->name('activities');

        });

        // Views
        Route::prefix('views')->name('views.')->group(function (): void {
            Route::post('/', 'createView')->name('create');
            Route::patch('/{taskView}', 'updateView')->name('update');
            Route::patch('/{taskView}/default', 'makeDefaultView')->name('makeDefault');
            Route::delete('/{taskView}', 'deleteView')->name('delete');
        });
    });
