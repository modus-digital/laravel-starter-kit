<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socialite
    |--------------------------------------------------------------------------
    | If this module is enabled we allow an admin to configure
    | 3 pre-defined socialite providers (Google, GitHub, and Facebook).
    |
    | This will allow users to login with Google, GitHub, and Facebook.
    |
    */
    'socialite' => [
        'enabled' => false,
        'providers' => [
            'google' => true,
            'github' => false,
            'microsoft' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    | If this module is enabled we are allowing CRUD operations for clients.
    | Clients are used to represent the clients that are allowed to use the application.
    |
    | This module will include the following features:
    | - CRUD operations for clients
    | - Client settings management
    | - Client users management
    | - Client roles management
    | - Client permissions management
    |
    */
    'clients' => [
        'enabled' => false,
        'role_management' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Saas - CLIENTS MODULE REQUIRED!!
    |--------------------------------------------------------------------------
    | If this module it means that the application is a SaaS application.
    | This module is build on top of the clients module and will include the following features:
    | - Billing system
    | - Subscription management
    | - Multi tenant support
    |
    */
    'saas' => false,

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    | If this module is enabled we are allowing users to register themselves.
    |
    */
    'registration' => true,
];
