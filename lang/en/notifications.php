<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to display notifications to the user.
    | This will be used for toasts and other notifications.
    */

    'toasts' => [
        'two_factor' => [
            'enabled' => 'Two-factor authentication enabled',
            'disabled' => 'Two-factor authentication disabled',
            'error' => 'Cannot enable two-factor authentication',
            'backup_codes_regenerated' => 'Your backup codes have been regenerated',
            'backup_codes_downloaded' => 'Backup codes downloaded',
        ],
        'sessions' => [
            'cleared' => 'All other browser sessions have been cleared',
            'cleared_error' => 'Failed to clear browser sessions',
            'no_sessions' => 'No other browser sessions to clear',
        ],
        'profile' => [
            'deleted' => 'Your account has been deleted',
            'updated' => 'Updated your personal information',
        ],
    ],
    'modals' => [
        'confirmable-password' => [
            'title' => 'Confirm Password',
            'description' => 'Please confirm your password to log out other browser sessions.',
        ],
        'two-factor' => [
            'enable' => [
                'title' => 'Enable Two-Factor Authentication',
                'description' => 'Please enter the code from your authenticator app to enable two-factor authentication.',
                'helper-text' => 'Insert the 6 digit code from your authenticator app.',
                'secret-key' => 'Secret Key: :secret',
                'button' => 'Enable',
            ],
            'disable' => [
                'title' => 'Disable Two-Factor Authentication',
                'description' => 'Please enter your password to disable two-factor authentication.',
                'password' => 'Password',
                'button' => 'Disable Two-Factor Authentication',
            ],
            'backup-codes' => [
                'title' => 'Regenerated Backup Codes',
                'description' => 'Your backup codes have been regenerated. Please save them in a secure location.',
            ],
        ],
    ],
];
