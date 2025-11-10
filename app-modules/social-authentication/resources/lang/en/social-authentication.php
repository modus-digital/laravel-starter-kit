<?php

declare(strict_types=1);

return [
    'title' => 'Socialite Providers',
    'group' => 'Modules',
    'label' => [
        'singular' => 'Socialite Provider',
        'plural' => 'Socialite Providers',
    ],
    'form' => [
        'provider_details' => [
            'title' => 'Provider Details',
            'description' => 'Provider type and name are pre-configured and cannot be changed',
            'name' => 'Provider Name',
            'provider_type' => 'Provider Type',
        ],
        'socialite_configuration' => [
            'title' => 'Socialite Configuration',
            'description' => 'Configure your OAuth credentials from the provider',
            'client_id' => 'Client ID',
            'client_id_helper' => 'Your OAuth application client ID',
            'client_secret' => 'Client Secret',
            'client_secret_helper' => 'Stored encrypted in the database',
            'redirect_uri' => 'Redirect URI',
            'redirect_uri_helper' => 'The callback URL for this provider. Copy this value into your OAuth provider dashboard.',
        ],
        'settings' => [
            'title' => 'Settings',
            'enable_provider' => 'Enable Provider',
            'enable_provider_helper' => 'When enabled, users can authenticate with this provider',
            'display_order' => 'Display Order',
            'display_order_helper' => 'Controls the button order on the login page',
        ],
    ],
    'table' => [
        'provider' => 'Provider',
        'enabled' => 'Enabled',
        'configured' => 'Configured',
        'order' => 'Order',
        'last_updated' => 'Last Updated',
        'configure' => 'Configure',
        'filters' => [
            'status' => 'Status',
            'status_all' => 'All providers',
            'status_enabled' => 'Enabled only',
            'status_disabled' => 'Disabled only',
            'configuration' => 'Configuration',
            'configuration_all' => 'All providers',
            'configuration_configured' => 'Configured only',
            'configuration_not_configured' => 'Not configured',
        ],
    ],
    'navigation' => [
        'badge_enabled' => 'enabled',
    ],
    'providers' => [
        'email' => 'Email',
        'google' => 'Google',
        'github' => 'GitHub',
        'facebook' => 'Facebook',
    ],
    'notifications' => [
        'saved' => 'Provider configuration saved',
    ],
    'auth' => [
        'continue_with' => 'Or continue with',
        'failed' => 'Authentication failed. Please try again.',
    ],
];
