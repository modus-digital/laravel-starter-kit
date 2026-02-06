<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Facades\Activity;

final class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        /** @var User|null $user */
        $user = $event->user;

        $properties = [
            'guard' => $event->guard,
            'credentials' => [
                'email' => $event->credentials['email'] ?? null,
            ],
        ];

        // If user exists, log with subject
        if ($user instanceof User) {
            Activity::inLog('authentication')
                ->event('auth.login.failed')
                ->causedBy($user)
                ->withProperties($properties)
                ->log('activity.auth.login_failed');
        } else {
            // If user doesn't exist, log without subject (attempted login with non-existent email)
            Activity::inLog('authentication')
                ->event('auth.login.failed')
                ->withProperties($properties)
                ->log('activity.auth.login_failed');
        }
    }
}
