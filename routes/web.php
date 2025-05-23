<?php

/** @noinspection RedundantRectorRule StringToClassConstantRector */

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StopImpersonating;

// Redirect to login page
Route::redirect('/', route('login'));

// Auth routes
Route::redirect('/login', 'auth/login')->name('login');
Route::redirect('/register', 'auth/register')->name('register');


// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/auth/logout', LogoutController::class)->name('auth.logout');

    Route::post('/impersonate/leave', StopImpersonating::class)->name('impersonate.leave');
});
