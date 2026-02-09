<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DashboardLayoutController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\MailgunAnalyticsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:'.Permission::AccessControlPanel->value])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        // Dashboard
        Route::get('/', DashboardController::class)->name('index');

        Route::put('/dashboard/layout', [DashboardLayoutController::class, 'update'])
            ->name('dashboard.layout.update');

        // Roles routes - bind role parameter to include internal roles for admin access
        Route::bind('role', function (string $value) {
            return App\Models\Role::withInternal()->findOrFail($value);
        });

        Route::prefix('roles')
            ->name('roles.')
            ->group(function (): void {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/create', [RoleController::class, 'create'])->name('create');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::get('/{role}', [RoleController::class, 'show'])->name('show');
                Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::put('/{role}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
            });

        // Activities routes
        Route::prefix('activities')
            ->name('activities.')
            ->group(function (): void {
                Route::get('/', [ActivityController::class, 'index'])->name('index');
                Route::get('/{activity}', [ActivityController::class, 'show'])->name('show');
            });

        // Branding routes
        Route::prefix('branding')
            ->name('branding.')
            ->group(function (): void {
                Route::get('/', [BrandingController::class, 'edit'])->name('edit');
                Route::put('/', [BrandingController::class, 'update'])->name('update');
            });

        // Integrations routes
        Route::prefix('integrations')
            ->name('integrations.')
            ->group(function (): void {
                Route::get('/', [IntegrationController::class, 'edit'])->name('edit');
                Route::put('/', [IntegrationController::class, 'update'])->name('update');
                Route::post('/test-s3', [IntegrationController::class, 'testS3Connection'])->name('test-s3');
            });

        // Mailgun Analytics routes
        Route::prefix('mailgun')
            ->name('mailgun.')
            ->group(function (): void {
                Route::get('/', [MailgunAnalyticsController::class, 'index'])->name('index');
            });

        // Users routes
        Route::prefix('users')
            ->name('users.')
            ->group(function (): void {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');
                Route::post('/bulk-restore', [UserController::class, 'bulkRestore'])->name('bulk-restore');
                Route::get('/{user}', [UserController::class, 'show'])->name('show');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::post('/{user}/restore', [UserController::class, 'restore'])->name('restore');
                Route::delete('/{user}/force', [UserController::class, 'forceDelete'])->name('force-delete');

                Route::post('/{targetUser}/impersonate', [ImpersonationController::class, 'start'])
                    ->middleware('can:'.Permission::ImpersonateUsers->value)
                    ->name('impersonate');
            });

        // Clients routes (only if module is enabled)
        if (config('modules.clients.enabled', false)) {
            Route::prefix('clients')
                ->name('clients.')
                ->group(function (): void {
                    Route::get('/', [ClientController::class, 'index'])->name('index');
                    Route::get('/create', [ClientController::class, 'create'])->name('create');
                    Route::post('/', [ClientController::class, 'store'])->name('store');
                    Route::post('/bulk-delete', [ClientController::class, 'bulkDelete'])->name('bulk-delete');
                    Route::post('/bulk-restore', [ClientController::class, 'bulkRestore'])->name('bulk-restore');
                    Route::get('/{client}', [ClientController::class, 'show'])->name('show');
                    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
                    Route::put('/{client}', [ClientController::class, 'update'])->name('update');
                    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
                    Route::post('/{clientId}/restore', [ClientController::class, 'restore'])->name('restore');
                    Route::delete('/{clientId}/force', [ClientController::class, 'forceDelete'])->name('force-delete');
                    Route::post('/{client}/add-user', [ClientController::class, 'addUserToClient'])->name('add-user');
                    Route::post('/{client}/users', [ClientController::class, 'storeNewUserForClient'])->name('users.store');
                    Route::put('/{client}/users/{user}/role', [ClientController::class, 'updateUserRole'])->name('users.update-role');
                    Route::delete('/{client}/users/{user}', [ClientController::class, 'removeUserFromClient'])->name('users.destroy');
                });
        }

        // Translations routes
        Route::prefix('translations')
            ->name('translations.')
            ->group(function (): void {
                Route::get('/', [TranslationController::class, 'index'])->name('index');
                Route::post('/language', [TranslationController::class, 'createLanguage'])->name('create-language');
                Route::post('/target-language', [TranslationController::class, 'setTargetLanguage'])->name('set-target-language');
                Route::get('/{group}', [TranslationController::class, 'show'])->name('show');
                Route::put('/{group}', [TranslationController::class, 'update'])->name('update');
                Route::get('/{group}/quick-translate', [TranslationController::class, 'quickTranslate'])->name('quick-translate');
                Route::post('/{group}/quick-translate', [TranslationController::class, 'saveQuickTranslate'])->name('save-quick-translate');
            });
    });
