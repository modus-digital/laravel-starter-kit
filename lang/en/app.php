<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Language Lines
    |--------------------------------------------------------------------------
    |
    | This file contains core application texts. Most translations have been
    | moved to more specific files:
    |
    | - auth.php: Authentication-related translations
    | - notifications.php: User notifications and alerts
    | - pages.php: Main pages content
    | - settings.php: Settings and preferences
    | - ui.php: Common UI elements
    | - user.php: User profile and account
    | - validation.php: Form validation messages
    |
    */

    // Application core
    'name' => 'Modus Starter Kit',
    'tagline' => 'Start your next project faster',
    'company' => 'Modus',
    'copyright' => '© :year Modus. All rights reserved.',

    // Common messages
    'success' => 'Success!',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Information',

    // Content blocks
    'empty_states' => [
        'no_results' => 'No results found',
        'no_data' => 'No data available',
    ],

    // Error pages
    'errors' => [
        '403' => [
            'title' => 'Forbidden',
            'message' => 'You don\'t have permission to access this resource.',
        ],
        '404' => [
            'title' => 'Not Found',
            'message' => 'The page you are looking for could not be found.',
        ],
        '500' => [
            'title' => 'Server Error',
            'message' => 'Something went wrong on our end. Please try again later.',
        ],
    ],

];
