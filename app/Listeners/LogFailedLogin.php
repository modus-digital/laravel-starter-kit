<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Facades\Activity;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {

        /** @var User $user */
        $user = $event->user;

        $properties = [
            'event' => 'login_failed',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'guard' => $event->guard,
            'credentials' => [
                'email' => $event->credentials['email'] ?? null,
            ],
        ];

        // If user exists, log with subject
        if ($user) {
            Activity::inLog('authentication')
                ->event('auth.login.failed')
                ->causedBy($user)
                ->withProperties($properties)
                ->log('Failed login attempt');
        } else {
            // If user doesn't exist, log without subject (attempted login with non-existent email)
            Activity::inLog('authentication')
                ->event('auth.login.failed')
                ->withProperties($properties)
                ->log('Failed login attempt with non-existent user');
        }
    }
}
