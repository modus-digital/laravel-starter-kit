<?php

use Pest\Mutate\Mutators\Sets\LaravelSet;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withBootstrapFiles([__DIR__ . '/vendor/larastan/larastan/bootstrap.php'])
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withPhpSets(php84: true)
    ->withImportNames(importNames: true, importDocBlockNames: true)
    ->withSets([
        // General Sets
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,

        // Laravel Specific Sets
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION
    ])
    ->withSkip([
        ArgumentAdderRector::class => [
            __DIR__ . '/app/Http/Middleware/Filament/Authenticate.php'
        ]
    ])
    ->withPaths(paths: [
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/public',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/tests',
    ]);
