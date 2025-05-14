<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for main pages content.
    |
    */

    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'welcome' => 'Welcome :name',
        'description' => 'This is the Modus default dashboard. Start building or updating your new application!'
    ],

    // User
    'user' => [
        'profile' => [
            'title' => 'Profile',
            'headers' => [
                'personal_information' => 'Personal information',
                'settings' => 'Settings',
                'browser_sessions' => 'Browser sessions',
            ],
            'actions' => [
                'edit' => 'Edit',
                'clear_sessions' => 'Clear browser sessions',
                'download_backup_codes' => 'Download backup codes',
            ],

        ],
        'settings' => [
            'title' => 'Settings',
            'actions' => [
                'delete_account' => 'Delete account',
                'delete_account_description' => 'Permanently delete your account.',
                'delete_account_confirmation' => 'Are you sure you want to delete your account? This action cannot be undone.',
            ],
        ],
    ],

];
