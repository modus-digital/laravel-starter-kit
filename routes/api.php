<?php

use App\Http\Controllers\Api\SentryController;
use Illuminate\Support\Facades\Route;

Route::get('sentry', SentryController::class);
