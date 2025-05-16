<?php

use App\Http\Controllers\Auth\ClearBrowserSessionsController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', route('login'));

// Auth routes
Route::redirect('/login', 'auth/login')->name('login');
Route::redirect('/register', 'auth/register')->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::delete('auth/clear-sessions', ClearBrowserSessionsController::class)->name('auth.clear-sessions');
    Route::post('/auth/logout', LogoutController::class)->name('auth.logout');
});
