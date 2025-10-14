<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\StopImpersonating;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', RedirectController::class)->name('app.home');

// Authentication Routes (Guest only)
Route::prefix('auth')
    ->middleware('guest')
    ->group(function () {
        // Authentication Routes
        Volt::route('login', 'auth.login')->name('login');
        Volt::route('register', 'auth.register')->name('register');

        // Password Routes
        Volt::route('forgot-password', 'auth.forgot-password')->name('password.forgot');
        Volt::route('reset-password/{token}', 'auth.reset-password')->name('password.reset');

        // Two Factor Routes
        // moved to authenticated group
    });

// Protected Routes (Authenticated users only)
Route::middleware('auth')->group(function () {
    Route::post('logout', LogoutController::class)->name('auth.logout');
    Route::post('impersonate/leave', StopImpersonating::class)->name('impersonate.leave');

    // Email Verification Routes (before 2FA and verified middleware)
    Volt::route('auth/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('auth/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Two Factor Routes (must be accessible to authenticated users before full access)
    Volt::route('auth/two-factor/verify', 'auth.two-factor.verify')->name('two-factor.verify');
    Volt::route('auth/two-factor/recover', 'auth.two-factor.recover')->name('two-factor.recover');

    Route::middleware('verified')->name('app.')->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');

        Route::prefix('user')->name('user.')->group(function () {
            Volt::route('profile', 'user.profile')->name('profile');
            Volt::route('profile/edit', 'user.profile-edit')->name('profile.edit');
        });
    });
});
