<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | User Resource Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the user resource.
    |
    */
    'users' => [
        'title' => 'Users',
        'group' => 'System',
        'label' => [
            'singular' => 'User',
            'plural' => 'Users',
        ],
        'form' => [
            'personal_information' => [
                'title' => 'Personal Information',
                'description' => 'Here you can edit the user\'s personal information.',
                'avatar' => 'Avatar',
                'name' => 'Name',
                'email' => 'Email',
            ],
            'security' => [
                'title' => 'Security',
                'description' => 'Here you can update the user\'s role and status. Passwords are automatically generated when creating a user.',
                'role' => 'Role',
                'status' => 'Status',
            ],
        ],
        'table' => [
            'avatar' => 'Avatar',
            'name' => 'Name',
            'email' => 'Email',
            'provider' => 'Via',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'filters' => 'Filters',
        ],
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
        'roles' => [
            'form' => [
                'title' => 'Role Information',
                'enum_key' => 'Enum Key',
                'name' => 'Name',
            ],
            'table' => [
                'enum_key' => 'Enum Key',
                'name' => 'Name',
                'linked_to_enum' => 'Linked to Enum',
                'linked_to_enum_tooltip' => 'Indicates whether the role is linked to an enum value',
                'permissions_count' => 'Permissions Count',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'linked_to_enum' => [
                    'title' => 'Linked to Enum',
                    'tooltip' => 'Indicates whether the role is linked to an enum value',
                    'true' => 'Yes',
                    'false' => 'No',
                ],
                'sync_roles' => [
                    'title' => 'Sync Roles',
                    'success' => [
                        'title' => 'Roles Synced Successfully',
                        'body' => 'Synced :count roles successfully',
                    ],
                ],
            ],
            'relation_managers' => [
                'permission' => [
                    'title' => 'Permissions',
                    'inverse_relationship' => 'Roles',
                    'name' => 'Name',
                    'enum_key' => 'Enum Key',
                    'linked_to_enum' => [
                        'title' => 'Linked to Enum',
                        'tooltip' => 'Indicates whether the permission is linked to an enum value',
                        'true' => 'Yes',
                        'false' => 'No',
                    ],
                    'permissions' => 'Permissions',
                    'permissions_placeholder' => 'Select Permissions',
                    'permissions_added' => 'Permissions Added: :count',
                    'add_permissions' => 'Add Permissions',
                    'add_permissions_modal_heading' => 'Add Permissions',
                    'detach_permission' => 'Detach Permission',
                    'detach_permission_modal_heading' => 'Detach Permission',
                    'detach_permission_modal_description' => 'Are you sure you want to detach this permission from the role?',
                    'permission_detached' => 'Permission Detached: :name',
                ],
            ],
        ],
        'permissions' => [
            'form' => [
                'title' => 'Permission Information',
                'enum_key' => 'Enum Key',
                'name' => 'Name',
            ],
            'table' => [
                'enum_key' => 'Enum Key',
                'name' => 'Name',
                'linked_to_enum' => [
                    'title' => 'Linked to Enum',
                    'tooltip' => 'Indicates whether the permission is linked to an enum value',
                    'true' => 'Yes',
                    'false' => 'No',
                ],
                'roles_count' => 'Roles Count',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
                'sync_permissions' => [
                    'title' => 'Sync Permissions',
                    'success' => [
                        'title' => 'Permissions Synced Successfully',
                        'body' => 'Synced :count permissions successfully',
                    ],
                ],
            ],
            'relation_managers' => [
                'role' => [
                    'title' => 'Roles',
                    'inverse_relationship' => 'Permissions',
                    'name' => 'Name',
                    'enum_key' => 'Enum Key',
                    'linked_to_enum' => [
                        'title' => 'Linked to Enum',
                        'tooltip' => 'Indicates whether the role is linked to an enum value',
                        'true' => 'Yes',
                        'false' => 'No',
                    ],
                    'roles' => 'Roles',
                    'roles_placeholder' => 'Select Roles',
                    'roles_added' => 'Roles Added: :count',
                    'add_roles' => 'Add Roles',
                    'add_roles_modal_heading' => 'Add Roles',
                    'detach_role' => 'Detach Role',
                    'detach_role_modal_heading' => 'Detach Role',
                    'detach_role_modal_description' => 'Are you sure you want to detach this role from the permission?',
                    'role_detached' => 'Role Detached: :name',
                ],
            ],
        ],
    ],
];
