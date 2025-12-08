<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\V1\Admin\ActivityLogController;
use App\Http\Controllers\Api\V1\Admin\ClientController;
use App\Http\Controllers\Api\V1\Admin\RbacController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\MeController;
use App\Models\Activity;
use App\Models\Modules\Clients\Client;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::get('docs', DocumentationController::class)->name('api.docs');
Route::get('openapi.json', [DocumentationController::class, 'specFile'])->name('api.openapi.json');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        // Current user endpoint
        Route::get('/me', MeController::class)->name('me');

        // Admin routes
        Route::prefix('admin')->name('admin.')->group(function (): void {
            // User management routes
            Route::prefix('users')->name('users.')->group(function (): void {
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

            // Client management routes
            if (config('modules.clients.enabled')) {
                Route::prefix('clients')->name('clients.')->group(function (): void {
                    Route::get('/', [ClientController::class, 'index'])->name('index');
                    Route::post('/', [ClientController::class, 'store'])->name('store');
                    Route::get('/{client}', [ClientController::class, 'show'])->name('show');
                    Route::put('/{client}', [ClientController::class, 'update'])->name('update');
                    Route::patch('/{client}', [ClientController::class, 'update'])->name('patch');
                    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');

                    // Soft delete management
                    Route::patch('/{clientId}/restore', [ClientController::class, 'restore'])->name('restore');
                    Route::delete('/{clientId}/force-delete', [ClientController::class, 'forceDelete'])->name('force-delete');
                });

                // Explicit route model binding for Client
                Route::bind('client', function (string $value) {
                    return Client::findOrFail($value);
                });
            }

            // RBAC routes
            Route::prefix('rbac')->name('rbac.')->group(function (): void {
                // Roles CRUD
                Route::prefix('roles')->name('roles.')->group(function (): void {
                    Route::get('/', [RbacController::class, 'index'])->name('index');
                    Route::post('/', [RbacController::class, 'store'])->name('store');
                    Route::get('/{role}', [RbacController::class, 'show'])->name('show');
                    Route::put('/{role}', [RbacController::class, 'update'])->name('update');
                    Route::patch('/{role}', [RbacController::class, 'update'])->name('patch');
                    Route::delete('/{role}', [RbacController::class, 'destroy'])->name('destroy');

                    // Permission management for roles
                    Route::post('/{role}/permissions/attach', [RbacController::class, 'attachPermissions'])->name('permissions.attach');
                    Route::post('/{role}/permissions/detach', [RbacController::class, 'detachPermissions'])->name('permissions.detach');
                });

                // Permissions listing
                Route::get('/permissions', [RbacController::class, 'permissions'])->name('permissions.index');

                // Explicit route model binding for Role
                Route::bind('role', function (string $value) {
                    return Role::findOrFail($value);
                });
            });

            // Activity logs routes
            Route::prefix('activities')->name('activities.')->group(function (): void {
                Route::get('/', [ActivityLogController::class, 'index'])->name('index');
                Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');

                // Explicit route model binding for Activity
                Route::bind('activity', function (string $value) {
                    return Activity::findOrFail($value);
                });
            });
        });
    });
