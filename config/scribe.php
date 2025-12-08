<?php

declare(strict_types=1);

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

use function Knuckles\Scribe\Config\configureStrategy;

return [
    'title' => config('app.name').' API Documentation',
    'description' => 'Modus API - A comprehensive Laravel starter kit API',
    'intro_text' => <<<'INTRO'
        This documentation aims to provide all the information you need to work with our API.

        <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
        You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>
    INTRO,
    'base_url' => config('app.url'),
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [
                '/api/docs',
                '/api/openapi.json',
            ],
        ],
    ],
    'type' => 'laravel',
    'theme' => 'default',
    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => false,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],
    'external' => [
        'html_attributes' => [],
    ],
    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],
    'auth' => [
        'enabled' => true,
        'default' => true,
        'in' => AuthIn::BEARER->value,
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => 'Bearer {YOUR_TOKEN}',
        'extra_info' => 'All API requests require authentication using a Bearer token obtained through the authentication endpoints.',
    ],
    'example_languages' => [
        'bash',
        'javascript',
    ],
    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],
    'openapi' => [
        'enabled' => true,
        'version' => '3.0.3',
        'overrides' => [],
        'generators' => [],
    ],
    'groups' => [
        'default' => 'Ungrouped',
        'order' => [
            'Ungrouped',
            'Admin | Users',
            'Admin | Clients',
            'Admin | RBAC',
            'Admin | Activity Logs',
        ],
    ],
    'logo' => false,
    'last_updated' => 'Last updated: {date:F j, Y}',
    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],
    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: ['GET *'],
                config: [
                    'app.debug' => false,
                ]
            )
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ],
    ],
    'database_connections_to_transact' => [config('database.default')],
    'fractal' => [
        'serializer' => null,
    ],
];
