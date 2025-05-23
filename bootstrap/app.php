<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckFeatureIsEnabled;
use App\Http\Middleware\TwoFactorAuthentication;
use Illuminate\Support\Facades\App;
use Sentry\Laravel\Integration;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
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
        Integration::handles(exceptions: $exceptions);
    })
    ->withSchedule(function (Schedule $schedule): void {

        if (! App::isLocal()) {
            $schedule->command('telescope:prune --hours=48')->daily();
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
            $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
        }
    })
    ->create();
