<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enums Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the enums.
    |
    */
    'activity_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'deleted' => 'Deleted',
    ],

    /*
    |--------------------------------------------------------------------------
    | RBAC Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the RBAC system.
    |
    */
    'rbac' => [
        'role' => [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'user' => 'User',
        ],
        'permission' => [
            'label' => [
                'access_control_panel' => 'Access control panel',
                'impersonate_users' => 'Impersonate users',
                'access_backups' => 'Access backups',
                'access_health_check' => 'Access health check',
                'access_activity_logs' => 'Access activity logs',
                'manage_settings' => 'Manage settings',
                'manage_oauth_providers' => 'Manage OAuth providers',
            ],
            'description' => [
                'access_control_panel' => 'Grants access to the administrative control panel where system management and configuration takes place.',
                'impersonate_users' => 'Allows signing in as another user to troubleshoot issues or provide support without needing their credentials.',
                'access_backups' => 'View, create, restore, and manage application backups to ensure data protection and recovery capabilities.',
                'access_health_check' => 'Monitor system health, performance metrics, and service availability to maintain application reliability.',
                'access_activity_logs' => 'Review detailed audit trails of user actions and system events for security and compliance purposes.',
                'manage_settings' => 'Configure and modify application settings, preferences, and system-wide configuration options.',
                'manage_oauth_providers' => 'Configure and manage third-party OAuth authentication providers like Google, GitHub, and Facebook.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the settings system.
    |
    */
    'settings' => [
        'user_settings' => [
            'label' => [
                'localization' => 'Localization',
                'security' => 'Security',
                'display' => 'Display',
                'notifications' => 'Notifications',
            ],
            'description' => [
                'localization' => 'Customize language, timezone, and date/time formats to match your regional preferences and locale.',
                'security' => 'Manage password policies, two-factor authentication, and other security measures to protect your account.',
                'display' => 'Personalize the visual appearance with theme colors and light/dark mode to suit your viewing preference.',
                'notifications' => 'Control how and when you receive alerts via email, push notifications, and in-app messages.',
            ],
        ],

        'appearance' => [
            'light' => 'Light',
            'dark' => 'Dark',
            'system' => 'System Based',
        ],

        'theme' => [
            'blue' => 'Blue',
            'indigo' => 'Indigo',
            'red' => 'Red',
            'orange' => 'Orange',
            'yellow' => 'Yellow',
            'green' => 'Green',
            'teal' => 'Teal',
            'cyan' => 'Cyan',
            'purple' => 'Purple',
        ],

        'language' => [
            'english' => 'English',
            'spanish' => 'Spanish',
            'french' => 'French',
            'german' => 'German',
            'italian' => 'Italian',
            'portuguese' => 'Portuguese',
            'dutch' => 'Dutch',
        ],

        'two_factor' => [
            'providers' => [
                'email' => 'Email',
                'authenticator' => 'Authenticator',
            ],
            'label' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'description' => [
                'enabled' => 'Two-factor authentication is enabled',
                'disabled' => 'Two-factor authentication is disabled',
            ],
            'action' => [
                'enabled' => 'Enable Two-Factor authentication',
                'disabled' => 'Disable Two-Factor authentication',
            ],
            'recovery_codes' => [
                'used' => ':used / :total used',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Priority Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the queue priority system.
    |
    */
    'queue_priority' => [
        'low' => 'Low Priority',
        'medium' => 'Medium Priority',
        'high' => 'High Priority',
    ],

];
