<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckFeatureIsEnabled;
use App\Http\Middleware\TwoFactorAuthentication;
use Illuminate\Support\Facades\App;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(aliases: [
            'feature' => CheckFeatureIsEnabled::class,
        ]);

        $middleware->web(
            append: [
                TwoFactorAuthentication::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {

        if (App::environment() !== 'local') {

            $schedule->command('backup:clean')
                ->description('Clean up old backups.')
                ->daily()
                ->at('01:00');

            $schedule->command('backup:run')
                ->description('Run the backup process.')
                ->daily()
                ->at('01:30');

            $schedule->command(ScheduleCheckHeartbeatCommand::class)
                ->description('Health check to ensure that the schedule is running every minute.')
                ->everyMinute();
        }
    })
    ->create();
