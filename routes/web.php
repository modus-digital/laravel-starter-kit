<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveImpersonationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RedirectToApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', RedirectToApplicationController::class)->name('home');

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)
            ->name('dashboard');

        Route::post('impersonate/leave', LeaveImpersonationController::class)
            ->name('impersonate.leave');

        Route::prefix('notifications')
            ->name('notifications.')
            ->group(function (): void {
                Route::get('/', [NotificationController::class, 'index'])->name('index');
                Route::post('/bulk/read', [NotificationController::class, 'bulkMarkRead'])->name('bulk.read');
                Route::post('/bulk/unread', [NotificationController::class, 'bulkMarkUnread'])->name('bulk.unread');
                Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
                Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
                Route::post('/{notification}/unread', [NotificationController::class, 'markUnread'])->name('unread');
                Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
                Route::delete('/', [NotificationController::class, 'clearAll'])->name('clear');
            });
    });

// Module routes
if (config('modules.socialite.enabled')) {
    require __DIR__.'/modules/oauth.php';
}
if (config('modules.clients.enabled')) {
    require __DIR__.'/modules/clients.php';
}

require __DIR__.'/settings.php';
