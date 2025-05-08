<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\App;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {

        if(App::environment() !== 'local') {
        
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
