<?php

declare(strict_types=1);

use App\Filament\Resources\Core\Translations\Tables\TranslationsTable;
use App\Filament\Resources\Core\Translations\TranslationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use function config;

it('builds records with status and missing metadata', function () {
    File::shouldReceive('get')
        ->with(lang_path('en.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Login', 'register' => 'Register'],
            'common' => ['save' => 'Save', 'cancel' => 'Cancel', 'delete' => 'Delete'],
            'dashboard' => ['title' => 'Dashboard', 'welcome' => 'Welcome', 'stats' => 'Stats', 'chart' => 'Chart', 'table' => 'Table'],
        ]));

    File::shouldReceive('get')
        ->with(lang_path('fr.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Connexion', 'register' => 'S\'inscrire'],
            'common' => ['save' => 'Enregistrer', 'cancel' => 'Annuler'],
            'dashboard' => ['title' => 'Tableau de bord', 'welcome' => 'Bienvenue', 'stats' => 'Statistiques'],
        ]));

    File::shouldReceive('exists')
        ->andReturn(true);

    $service = new TranslationService;

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        targetLanguage: 'fr',
    );

    expect($records)
        ->toBeInstanceOf(Collection::class)
        ->and($records->count())->toBe(3)
        ->and($records->first())->toMatchArray([
            '__key' => 'auth',
            'key' => 'Auth',
            'status' => true,
            'missing' => 0,
            'total' => 2,
        ]);
});

it('does not include disabled module translations in progress calculations', function () {
    File::shouldReceive('get')
        ->with(lang_path('en.json'))
        ->andReturn(json_encode([
            'navigation' => [
                'labels' => [
                    'clients' => 'Clients',
                    'dashboard' => 'Dashboard',
                ],
            ],
            'admin' => [
                'clients' => [
                    'navigation_label' => 'Clients',
                ],
                'branding' => [
                    'sections' => [
                        'logo' => 'Logo',
                    ],
                ],
            ],
        ]));

    File::shouldReceive('get')
        ->with(lang_path('fr.json'))
        ->andReturn(json_encode([
            'navigation' => [
                'labels' => [
                    'dashboard' => 'Tableau de bord',
                ],
            ],
            'admin' => [
                'branding' => [
                    'sections' => [
                        'logo' => 'Logo',
                    ],
                ],
            ],
        ]));

    File::shouldReceive('exists')
        ->andReturn(true);

    config()->set('modules.clients.enabled', false);

    $service = new TranslationService;

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        targetLanguage: 'fr',
    );

    /** @var array{__key: string, key: string, status: bool, missing: int, total: int} $adminRecord */
    $adminRecord = $records->firstWhere('__key', 'admin');

    expect($adminRecord['total'])->toBe(1)
        ->and($adminRecord['missing'])->toBe(0);
});

it('applies module-based filtering dynamically from config modules', function () {
    File::shouldReceive('get')
        ->with(lang_path('en.json'))
        ->andReturn(json_encode([
            'navigation' => [
                'labels' => [
                    'registration' => 'Registration',
                    'dashboard' => 'Dashboard',
                ],
            ],
            'admin' => [
                'registration' => [
                    'navigation_label' => 'Registration',
                ],
                'branding' => [
                    'sections' => [
                        'logo' => 'Logo',
                    ],
                ],
            ],
        ]));

    File::shouldReceive('get')
        ->with(lang_path('fr.json'))
        ->andReturn(json_encode([
            'navigation' => [
                'labels' => [
                    'dashboard' => 'Tableau de bord',
                ],
            ],
            'admin' => [
                'branding' => [
                    'sections' => [
                        'logo' => 'Logo',
                    ],
                ],
            ],
        ]));

    File::shouldReceive('exists')
        ->andReturn(true);

    config()->set('modules.registration.enabled', false);

    $service = new TranslationService;

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        targetLanguage: 'fr',
    );

    /** @var array{__key: string, key: string, status: bool, missing: int, total: int} $adminRecord */
    $adminRecord = $records->firstWhere('__key', 'admin');

    expect($adminRecord['total'])->toBe(1)
        ->and($adminRecord['missing'])->toBe(0);
});

it('filters records when a search term is provided', function () {
    File::shouldReceive('get')
        ->with(lang_path('en.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Login'],
            'common' => ['save' => 'Save'],
            'dashboard' => ['title' => 'Dashboard'],
        ]));

    File::shouldReceive('get')
        ->with(lang_path('fr.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Connexion'],
            'common' => ['save' => 'Enregistrer'],
            'dashboard' => ['title' => 'Tableau de bord'],
        ]));

    File::shouldReceive('exists')
        ->andReturn(true);

    $service = new TranslationService;

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        search: 'dash',
        targetLanguage: 'fr',
    );

    expect($records->pluck('key')->all())->toEqual(['Dashboard']);
});

it('sorts records by key in descending order', function () {
    File::shouldReceive('get')
        ->with(lang_path('en.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Login'],
            'common' => ['save' => 'Save'],
            'dashboard' => ['title' => 'Dashboard'],
        ]));

    File::shouldReceive('get')
        ->with(lang_path('fr.json'))
        ->andReturn(json_encode([
            'auth' => ['login' => 'Connexion'],
            'common' => ['save' => 'Enregistrer'],
            'dashboard' => ['title' => 'Tableau de bord'],
        ]));

    File::shouldReceive('exists')
        ->andReturn(true);

    $service = new TranslationService;

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        sortColumn: 'key',
        sortDirection: 'desc',
        targetLanguage: 'fr',
    );

    expect($records->pluck('key')->all())->toEqual(['Dashboard', 'Common', 'Auth']);
});
