<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tasks:send-due-reminders --days=1')->daily();
Schedule::command('tasks:send-due-reminders --days=3')->daily();
Schedule::command('tasks:send-due-reminders --days=7')->daily();
