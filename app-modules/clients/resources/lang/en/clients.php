<?php

declare(strict_types=1);

return [
    'title' => 'Clients',
    'group' => 'Modules',
    'label' => [
        'singular' => 'Client',
        'plural' => 'Clients',
    ],
    'form' => [
        'information' => [
            'title' => 'Client Information',
            'description' => 'Manage the client\'s basic details.',
            'name' => 'Name',
            'website' => 'Website',
            'status' => 'Status',
        ],
    ],
    'table' => [
        'name' => 'Name',
        'website' => 'Website',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'filters' => 'Filters',
    ],
    'relation_managers' => [
        'user' => [
            'title' => 'Users',
            'name' => 'Name',
            'email' => 'Email',
            'status' => 'Status',
            'role' => 'Role',
            'password' => 'Password',
            'personal_information' => 'Personal Information',
            'security' => 'Security',
            'attach_user' => 'Attach User',
            'attach_user_modal_heading' => 'Attach Existing Users',
            'users' => 'Users',
            'users_placeholder' => 'Select users to attach',
            'users_attached' => ':count user(s) attached successfully',
            'create_user' => 'Create User',
            'create_user_modal_heading' => 'Create New User',
            'user_created' => 'User ":name" created and attached successfully',
            'detach_user' => 'Detach',
            'detach_user_modal_heading' => 'Detach User',
            'detach_user_modal_description' => 'Are you sure you want to detach this user from the client? The user will not be deleted.',
            'user_detached' => 'User ":name" detached successfully',
        ],
    ],
];
