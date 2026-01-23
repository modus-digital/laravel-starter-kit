<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\MailgunAnalyticsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        // Dashboard
        Route::get('/', DashboardController::class)
            ->middleware('can:'.Permission::ACCESS_CONTROL_PANEL->value)
            ->name('index');

        // Roles routes
        Route::prefix('roles')
            ->name('roles.')
            ->group(function (): void {
                Route::get('/', [RoleController::class, 'index'])
                    ->middleware('can:'.Permission::READ_ROLES->value)
                    ->name('index');

                Route::get('/create', [RoleController::class, 'create'])
                    ->middleware('can:'.Permission::CREATE_ROLES->value)
                    ->name('create');

                Route::post('/', [RoleController::class, 'store'])
                    ->middleware('can:'.Permission::CREATE_ROLES->value)
                    ->name('store');

                Route::get('/{role}', [RoleController::class, 'show'])
                    ->middleware('can:'.Permission::READ_ROLES->value)
                    ->name('show');

                Route::get('/{role}/edit', [RoleController::class, 'edit'])
                    ->middleware('can:'.Permission::UPDATE_ROLES->value)
                    ->name('edit');

                Route::put('/{role}', [RoleController::class, 'update'])
                    ->middleware('can:'.Permission::UPDATE_ROLES->value)
                    ->name('update');

                Route::delete('/{role}', [RoleController::class, 'destroy'])
                    ->middleware('can:'.Permission::DELETE_ROLES->value)
                    ->name('destroy');
            });

        // Activities routes
        Route::prefix('activities')
            ->name('activities.')
            ->group(function (): void {
                Route::get('/', [ActivityController::class, 'index'])
                    ->middleware('can:'.Permission::ACCESS_ACTIVITY_LOGS->value)
                    ->name('index');

                Route::get('/{activity}', [ActivityController::class, 'show'])
                    ->middleware('can:'.Permission::ACCESS_ACTIVITY_LOGS->value)
                    ->name('show');
            });

        // Branding routes
        Route::prefix('branding')
            ->name('branding.')
            ->group(function (): void {
                Route::get('/', [BrandingController::class, 'edit'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('edit');

                Route::put('/', [BrandingController::class, 'update'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('update');
            });

        // Integrations routes
        Route::prefix('integrations')
            ->name('integrations.')
            ->group(function (): void {
                Route::get('/', [IntegrationController::class, 'edit'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('edit');

                Route::put('/', [IntegrationController::class, 'update'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('update');
            });

        // Mailgun Analytics routes
        Route::prefix('mailgun')
            ->name('mailgun.')
            ->group(function (): void {
                Route::get('/', [MailgunAnalyticsController::class, 'index'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('index');
            });

        // Users routes
        Route::prefix('users')
            ->name('users.')
            ->group(function (): void {
                Route::get('/', [UserController::class, 'index'])
                    ->middleware('can:'.Permission::READ_USERS->value)
                    ->name('index');

                Route::get('/create', [UserController::class, 'create'])
                    ->middleware('can:'.Permission::CREATE_USERS->value)
                    ->name('create');

                Route::post('/', [UserController::class, 'store'])
                    ->middleware('can:'.Permission::CREATE_USERS->value)
                    ->name('store');

                Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])
                    ->middleware('can:'.Permission::DELETE_USERS->value)
                    ->name('bulk-delete');

                Route::post('/bulk-restore', [UserController::class, 'bulkRestore'])
                    ->middleware('can:'.Permission::RESTORE_USERS->value)
                    ->name('bulk-restore');

                Route::get('/{user}', [UserController::class, 'show'])
                    ->middleware('can:'.Permission::READ_USERS->value)
                    ->name('show');

                Route::get('/{user}/edit', [UserController::class, 'edit'])
                    ->middleware('can:'.Permission::UPDATE_USERS->value)
                    ->name('edit');

                Route::put('/{user}', [UserController::class, 'update'])
                    ->middleware('can:'.Permission::UPDATE_USERS->value)
                    ->name('update');

                Route::delete('/{user}', [UserController::class, 'destroy'])
                    ->middleware('can:'.Permission::DELETE_USERS->value)
                    ->name('destroy');

                Route::post('/{user}/restore', [UserController::class, 'restore'])
                    ->middleware('can:'.Permission::RESTORE_USERS->value)
                    ->name('restore');

                Route::delete('/{user}/force', [UserController::class, 'forceDelete'])
                    ->middleware('can:'.Permission::DELETE_USERS->value)
                    ->name('force-delete');

                Route::post('/{targetUser}/impersonate', [ImpersonationController::class, 'start'])
                    ->middleware('can:'.Permission::IMPERSONATE_USERS->value)
                    ->name('impersonate');
            });

        // Clients routes (only if module is enabled)
        if (config('modules.clients.enabled', false)) {
            Route::prefix('clients')
                ->name('clients.')
                ->group(function (): void {
                    Route::get('/', [ClientController::class, 'index'])
                        ->middleware('can:'.Permission::READ_CLIENTS->value)
                        ->name('index');

                    Route::get('/create', [ClientController::class, 'create'])
                        ->middleware('can:'.Permission::CREATE_CLIENTS->value)
                        ->name('create');

                    Route::post('/', [ClientController::class, 'store'])
                        ->middleware('can:'.Permission::CREATE_CLIENTS->value)
                        ->name('store');

                    Route::post('/bulk-delete', [ClientController::class, 'bulkDelete'])
                        ->middleware('can:'.Permission::DELETE_CLIENTS->value)
                        ->name('bulk-delete');

                    Route::post('/bulk-restore', [ClientController::class, 'bulkRestore'])
                        ->middleware('can:'.Permission::RESTORE_CLIENTS->value)
                        ->name('bulk-restore');

                    Route::get('/{client}', [ClientController::class, 'show'])
                        ->middleware('can:'.Permission::READ_CLIENTS->value)
                        ->name('show');

                    Route::get('/{client}/edit', [ClientController::class, 'edit'])
                        ->middleware('can:'.Permission::UPDATE_CLIENTS->value)
                        ->name('edit');

                    Route::put('/{client}', [ClientController::class, 'update'])
                        ->middleware('can:'.Permission::UPDATE_CLIENTS->value)
                        ->name('update');

                    Route::delete('/{client}', [ClientController::class, 'destroy'])
                        ->middleware('can:'.Permission::DELETE_CLIENTS->value)
                        ->name('destroy');

                    Route::post('/{client}/restore', [ClientController::class, 'restore'])
                        ->middleware('can:'.Permission::RESTORE_CLIENTS->value)
                        ->name('restore');

                    Route::delete('/{client}/force', [ClientController::class, 'forceDelete'])
                        ->middleware('can:'.Permission::DELETE_CLIENTS->value)
                        ->name('force-delete');
                });
        }

        // Translations routes
        Route::prefix('translations')
            ->name('translations.')
            ->group(function (): void {
                Route::get('/', [TranslationController::class, 'index'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('index');

                Route::post('/language', [TranslationController::class, 'createLanguage'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('create-language');

                Route::post('/target-language', [TranslationController::class, 'setTargetLanguage'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('set-target-language');

                Route::get('/{group}', [TranslationController::class, 'show'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('show');

                Route::put('/{group}', [TranslationController::class, 'update'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('update');

                Route::get('/{group}/quick-translate', [TranslationController::class, 'quickTranslate'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('quick-translate');

                Route::post('/{group}/quick-translate', [TranslationController::class, 'saveQuickTranslate'])
                    ->middleware('can:'.Permission::MANAGE_SETTINGS->value)
                    ->name('save-quick-translate');
            });
    });
