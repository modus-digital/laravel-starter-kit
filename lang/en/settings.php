<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for application settings and related enums.
    |
    */

    'header' => 'Settings',

    // Settings categories
    'categories' => [
        'localization' => 'Localization settings',
        'security' => 'Security settings',
        'display' => 'Display settings',
        'notifications' => 'Notifications settings',
    ],

    // Appearance settings
    'appearance' => [
        'title' => 'Appearance',
        'modes' => [
            'light' => 'Light mode',
            'dark' => 'Dark mode',
            'system' => 'System based',
        ],
        'labels' => [
            'light' => 'Light',
            'dark' => 'Dark',
            'system' => 'System',
        ],
    ],

    // Languages
    'language' => [
        'title' => 'Language',
        'options' => [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'nl' => 'Dutch',
        ],
    ],

    // Themes
    'theme' => [
        'title' => 'Theme',
        'options' => [
            'blue' => 'Blue',
            'indigo' => 'Indigo',
            'purple' => 'Purple',
            'pink' => 'Pink',
            'red' => 'Red',
            'orange' => 'Orange',
            'yellow' => 'Yellow',
            'green' => 'Green',
            'teal' => 'Teal',
            'cyan' => 'Cyan',
        ],
    ],

    // Security
    'security' => [
        'two_factor' => [
            'title' => 'Two-Factor Authentication',
            'status' => 'Two-factor status',
            'status_message' => 'Two-factor authentication provides additional security by requiring a second verification step.',
            'enabled' => 'Two-factor authentication is enabled',
            'disabled' => 'Two-factor authentication is disabled',
            'enable' => 'Enable Two-Factor Authentication',
            'disable' => 'Disable Two-Factor Authentication',
            'regenerate_backup_code' => 'Regenerate backup codes',
            'confirmed_at' => 'Confirmed at :date',
        ],
        'password' => [
            'last_updated' => 'Password last updated',
            'last_updated_not_set' => 'Never',
        ],
    ],

    // Date and time
    'datetime' => [
        'date_format' => 'Date Format',
        'timezone' => 'Timezone',
        'formats' => [
            'day_month_year_time' => '31-01-2025 23:59',
            'day_month_year' => '31-01-2025',
            'day_month_year_slash' => '31/01/2025',
            'day_month_year_dot' => '31.01.2025',
            'year_month_day' => '2025-01-31',
            'month_day_year' => '01/31/2025',
        ],
    ],

];
