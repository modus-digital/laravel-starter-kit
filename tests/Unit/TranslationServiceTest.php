<?php

declare(strict_types=1);

use App\Filament\Resources\Core\Translations\TranslationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->service = new TranslationService;
    $this->originalLangPath = app()->langPath();
    $this->temporaryLangPath = storage_path('framework/testing/lang');

    File::deleteDirectory($this->temporaryLangPath);
    File::ensureDirectoryExists($this->temporaryLangPath);
    File::put("{$this->temporaryLangPath}/en.json", "{}\n");
    File::put("{$this->temporaryLangPath}/fr.json", "{}\n");

    app()->useLangPath($this->temporaryLangPath);
    Session::forget('translations.target_language');
});

afterEach(function () {
    app()->useLangPath($this->originalLangPath);
    File::deleteDirectory($this->temporaryLangPath);
    Session::forget('translations.target_language');
});

it('creates an empty json object for a new language file', function () {
    $this->service->createLanguage('de');

    $filePath = "{$this->temporaryLangPath}/de.json";

    expect(File::exists($filePath))->toBeTrue()
        ->and(File::get($filePath))->toBe("{}\n");
});

it('returns the default target language when none is stored', function () {
    expect($this->service->getTargetLanguage())->toBe('fr');
});

it('persists the selected target language', function () {
    $this->service->setTargetLanguage('fr');

    expect(Session::get('translations.target_language'))->toBe('fr')
        ->and($this->service->getTargetLanguage())->toBe('fr');
});

it('rejects unavailable target languages', function () {
    expect(fn () => $this->service->setTargetLanguage('es'))
        ->toThrow(InvalidArgumentException::class);
});
