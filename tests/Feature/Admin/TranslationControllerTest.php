<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission as PermissionModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create all permissions from enum
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            PermissionModel::firstOrCreate(
                ['name' => $permission->value, 'guard_name' => 'web']
            );
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::MANAGE_SETTINGS);

    $this->originalLangPath = app()->langPath();
    $this->temporaryLangPath = storage_path('framework/testing/lang');

    File::deleteDirectory($this->temporaryLangPath);
    File::ensureDirectoryExists($this->temporaryLangPath);
    File::put("{$this->temporaryLangPath}/en.json", json_encode([
        'navigation' => [
            'labels' => [
                'translation_manager' => 'Translation Manager',
                'users' => 'Users',
            ],
        ],
        'admin' => [
            'translations' => [
                'title' => 'Translations',
            ],
        ],
    ])."\n");
    File::put("{$this->temporaryLangPath}/fr.json", json_encode([
        'navigation' => [
            'labels' => [
                'translation_manager' => 'Gestionnaire de traductions',
            ],
        ],
    ])."\n");

    app()->useLangPath($this->temporaryLangPath);
    Session::forget('translations.target_language');
});

afterEach(function () {
    app()->useLangPath($this->originalLangPath);
    File::deleteDirectory($this->temporaryLangPath);
    Session::forget('translations.target_language');
});

it('can list translation groups', function () {
    $response = $this->actingAs($this->user)->get('/admin/translations');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/translations/index')
            ->has('groups', 2)
            ->has('availableLanguages')
            ->has('targetLanguage')
        );
});

it('can show a translation group', function () {
    $response = $this->actingAs($this->user)->get('/admin/translations/navigation');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/translations/[group]/index')
            ->where('group', 'navigation')
            ->has('translations')
            ->has('progress')
        );
});

it('can update a translation', function () {
    $response = $this->actingAs($this->user)->put('/admin/translations/navigation', [
        'key' => 'navigation.labels.users',
        'translation' => 'Utilisateurs',
    ]);

    $response->assertRedirect();

    $service = app(TranslationService::class);
    expect($service->getTranslation('fr', 'navigation.labels.users'))->toBe('Utilisateurs');
});

it('can show quick translate page', function () {
    $response = $this->actingAs($this->user)->get('/admin/translations/navigation/quick-translate');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/translations/[group]/quick-translate')
            ->where('group', 'navigation')
            ->has('currentKey')
            ->has('currentEnglish')
            ->has('remainingCount')
        );
});

it('can save quick translate and advance to next', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/navigation/quick-translate', [
        'translation_key' => 'navigation.labels.users',
        'translation' => 'Utilisateurs',
    ]);

    $response->assertRedirect();

    $service = app(TranslationService::class);
    expect($service->getTranslation('fr', 'navigation.labels.users'))->toBe('Utilisateurs');
});

it('redirects to group page when all translations are complete', function () {
    // First, translate the missing one
    $service = app(TranslationService::class);
    $service->setTranslation('fr', 'navigation.labels.users', 'Utilisateurs');

    $response = $this->actingAs($this->user)->post('/admin/translations/navigation/quick-translate', [
        'translation_key' => 'navigation.labels.users',
        'translation' => 'Utilisateurs',
    ]);

    $response->assertRedirect('/admin/translations/navigation');
});

it('can create a new language', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/language', [
        'language_code' => 'es',
    ]);

    $response->assertRedirect();

    expect(File::exists("{$this->temporaryLangPath}/es.json"))->toBeTrue();
});

it('validates language code format', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/language', [
        'language_code' => 'invalid',
    ]);

    $response->assertSessionHasErrors('language_code');
});

it('prevents creating duplicate languages', function () {
    File::put("{$this->temporaryLangPath}/es.json", "{}\n");

    $response = $this->actingAs($this->user)->post('/admin/translations/language', [
        'language_code' => 'es',
    ]);

    $response->assertSessionHasErrors('language_code');
});

it('can set target language', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/target-language', [
        'language' => 'fr',
    ]);

    $response->assertRedirect();

    expect(Session::get('translations.target_language'))->toBe('fr');
});

it('validates target language exists', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/target-language', [
        'language' => 'xx',
    ]);

    $response->assertSessionHasErrors('language');
});

it('requires manage settings permission to access translations', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/translations');

    $response->assertForbidden();
});

it('requires manage settings permission to update translations', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/admin/translations/navigation', [
        'key' => 'navigation.labels.users',
        'translation' => 'Utilisateurs',
    ]);

    $response->assertForbidden();
});

it('validates translation update request', function () {
    $response = $this->actingAs($this->user)->put('/admin/translations/navigation', [
        'key' => '',
        'translation' => '',
    ]);

    $response->assertSessionHasErrors(['key', 'translation']);
});

it('validates quick translate request', function () {
    $response = $this->actingAs($this->user)->post('/admin/translations/navigation/quick-translate', [
        'translation_key' => '',
        'translation' => '',
    ]);

    $response->assertSessionHasErrors(['translation_key', 'translation']);
});
