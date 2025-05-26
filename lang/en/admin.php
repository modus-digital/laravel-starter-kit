<?php

return [
    'navigation' => [
        'groups' => [
            'beheer' => 'Management',
            'toegangsbeheer' => 'Access control',
            'applicatie-info' => 'Core',
        ],

        'pages' => [
            'settings' => 'Settings',
            'emails' => 'Emails',
            'users' => 'Users',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'translations' => 'Translation manager',
            'health' => 'Health checks',
            'backups' => 'Backups',

        ],

        'back_to_app_button' => 'Back to application',
    ],

    'resources' => [
        'rbac' => [
            'roles' => [
                'label' => [
                    'singular' => 'Rol',
                    'plural' => 'Roles',
                ],

                'form' => [
                    'enum_key' => 'Enum Key',
                    'name' => 'Name',
                    'description' => 'Description',
                ],

                'table' => [
                    'linked_to_enum' => 'Linked to enum',
                    'permissions_count' => 'Permissions count',
                    'created_at' => 'Created at',
                    'updated_at' => 'Updated at',
                ],

                'actions' => [
                    'sync' => 'Sync permissions with enums',
                ],

                'notifications' => [
                    'sync_success' => [
                        'title' => 'Synchronization successful',
                        'message' => ':count roles have been synchronized.',
                    ],
                ],
            ],

            'permissions' => [
                'label' => [
                    'singular' => 'Permission',
                    'plural' => 'Permissions',
                ],

                'form' => [
                    'enum_key' => 'Enum Key',
                    'name' => 'Name',
                    'description' => 'Description',
                ],

                'table' => [
                    'enum_key' => 'Enum Key',
                    'name' => 'Name',
                    'description' => 'Description',
                    'linked_to_enum' => 'Linked to enum',
                    'roles_count' => 'Roles count',
                    'created_at' => 'Created at',
                    'updated_at' => 'Updated at',
                ],

                'actions' => [
                    'sync' => 'Sync permissions with enums',
                ],

                'notifications' => [
                    'sync_success' => [
                        'title' => 'Synchronization successful',
                        'message' => ':count permissions have been synchronized.',
                    ],
                ],
            ],
        ],

        'users' => [
            'label' => [
                'singular' => 'User',
                'plural' => 'Users',
            ],

            'form' => [
                'sections' => [
                    'personal_information' => [
                        'label' => 'Personal information',
                        'description' => 'Here you can edit the personal information of this user.',
                        'first_name' => 'First name',
                        'last_name_prefix' => 'Last name prefix',
                        'last_name' => 'Last name',
                        'email' => 'Email',
                    ],
                    'session_information' => [
                        'label' => 'Session information',
                        'description' => 'Here you can find information about the active sessions of this user.',
                        'last_login_at' => 'Last login at',
                        'not_logged_in' => 'Not logged in yet',
                        'sessions' => [
                            'label' => 'Active Sessions',
                            'ip_address' => 'IP address',
                            'expires_at' => 'Expires at',
                            'device_info' => 'Device info',
                            'browser' => 'Browser',
                            'platform' => 'Platform',
                            'device' => 'Device',
                            'desktop' => 'Desktop',
                            'mobile' => 'Mobile',
                            'tablet' => 'Tablet',
                            'unknown' => 'Unknown',
                        ],
                    ],
                    'access_control' => [
                        'label' => 'Access control',
                        'description' => 'Here you can manage the access of this user to the application.',
                        'role' => 'Role',
                        'new_password' => 'New password',
                    ],
                ],
            ],

            'table' => [
                'name' => 'Full name',
                'email' => 'Email',
                'role' => 'Role',
            ],

            'actions' => [
                'impersonate' => 'Impersonate',
                'edit' => 'Edit',
            ],

            'notifications' => [
                'impersonate' => [
                    'title' => 'You do not have access to this action',
                    'message' => 'You do not have the necessary permissions to perform this action.',
                ],

            ],

        ],
    ],
];
