<?php

declare(strict_types=1);

use App\Http\Controllers\Webhooks\MailgunWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/mailgun', MailgunWebhookController::class)
    ->name('webhooks.mailgun')
    ->middleware('throttle:60,1'); // 60 requests per minute
