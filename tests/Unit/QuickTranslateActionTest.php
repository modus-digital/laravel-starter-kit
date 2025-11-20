<?php

use App\Filament\Resources\Core\Translations\TranslationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->originalLangPath = app()->langPath();
    $this->temporaryLangPath = storage_path('framework/testing/lang');

    File::deleteDirectory($this->temporaryLangPath);
    File::ensureDirectoryExists($this->temporaryLangPath);

    // Create test language files
    File::put("{$this->temporaryLangPath}/en.json", json_encode([
        'auth' => [
            'failed' => 'These credentials do not match our records.',
            'password' => 'The provided password is incorrect.',
            'throttle' => 'Too many login attempts.',
        ],
    ], JSON_PRETTY_PRINT)."\n");

    File::put("{$this->temporaryLangPath}/nl.json", json_encode([
        'auth' => [
            'failed' => 'Deze inloggegevens kloppen niet.',
        ],
    ], JSON_PRETTY_PRINT)."\n");

    app()->useLangPath($this->temporaryLangPath);
    Session::put('translations.target_language', 'nl');
});

afterEach(function () {
    app()->useLangPath($this->originalLangPath);
    File::deleteDirectory($this->temporaryLangPath);
    Session::forget('translations.target_language');
});

it('identifies missing translations for a group', function () {
    $service = app()->make(TranslationService::class);

    $missing = $service->getMissingTranslations('nl', 'auth');

    expect($missing)->toHaveKeys(['auth.password', 'auth.throttle'])
        ->and(count($missing))->toBe(2);
});

it('returns empty array when all translations are complete', function () {
    // Update nl.json to have all translations
    File::put("{$this->temporaryLangPath}/nl.json", json_encode([
        'auth' => [
            'failed' => 'Deze inloggegevens kloppen niet.',
            'password' => 'Het opgegeven wachtwoord is onjuist.',
            'throttle' => 'Te veel inlogpogingen.',
        ],
    ], JSON_PRETTY_PRINT)."\n");

    $service = app()->make(TranslationService::class);
    $missing = $service->getMissingTranslations('nl', 'auth');

    expect($missing)->toBeEmpty();
});

it('saves translation and cycles to next missing record', function () {
    $service = app()->make(TranslationService::class);

    // Get initial missing translations
    $initialMissing = $service->getMissingTranslations('nl', 'auth');
    expect(count($initialMissing))->toBe(2);

    // Save the first translation
    $firstKey = array_key_first($initialMissing);
    $service->setTranslation('nl', $firstKey, 'Test translation');

    // Check that one translation is now saved
    $afterSaveMissing = $service->getMissingTranslations('nl', 'auth');
    expect(count($afterSaveMissing))->toBe(1)
        ->and($afterSaveMissing)->not->toHaveKey($firstKey);
});

it('verifies translation is persisted to file', function () {
    $service = app()->make(TranslationService::class);

    $service->setTranslation('nl', 'auth.password', 'Het opgegeven wachtwoord is onjuist.');

    $nlData = $service->getLanguageFile('nl');

    expect($nlData['auth']['password'])->toBe('Het opgegeven wachtwoord is onjuist.');
});
