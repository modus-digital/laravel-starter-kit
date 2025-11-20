<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveImpersonationController;
use App\Http\Controllers\RedirectToApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', RedirectToApplicationController::class)->name('app.home');

Route::middleware(middleware: ['auth', 'verified'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)
            ->name('dashboard');

        Route::post('impersonate/leave', LeaveImpersonationController::class)
            ->name('impersonate.leave');
    });

// Module routes
if (config('modules.socialite.enabled')) require __DIR__.'/oauth.php';
if (config('modules.clients.enabled')) require __DIR__.'/clients.php';

require __DIR__.'/settings.php';
