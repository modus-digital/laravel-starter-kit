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
];
