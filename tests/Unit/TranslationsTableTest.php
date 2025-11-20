<?php

use App\Filament\Resources\Core\Translations\Tables\TranslationsTable;
use Illuminate\Support\Collection;

it('builds records with status and missing metadata', function () {
    $service = new class extends \App\Filament\Resources\Core\Translations\TranslationService
    {
        public function __construct(private array $groups = [
            'auth' => ['missing' => 0, 'total' => 2],
            'common' => ['missing' => 1, 'total' => 3],
            'dashboard' => ['missing' => 2, 'total' => 5],
        ]) {}

        public function getRootGroups(): array
        {
            return array_keys($this->groups);
        }

        public function getTranslationProgress(string $lang, string $group): array
        {
            $progress = $this->groups[$group];

            return [
                'missing' => $progress['missing'],
                'total' => $progress['total'],
                'translated' => $progress['total'] - $progress['missing'],
            ];
        }
    };

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

it('filters records when a search term is provided', function () {
    $service = new class extends \App\Filament\Resources\Core\Translations\TranslationService
    {
        public function getRootGroups(): array
        {
            return ['auth', 'common', 'dashboard'];
        }

        public function getTranslationProgress(string $lang, string $group): array
        {
            return ['missing' => 0, 'total' => 1, 'translated' => 1];
        }
    };

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        search: 'dash',
        targetLanguage: 'fr',
    );

    expect($records->pluck('key')->all())->toEqual(['Dashboard']);
});

it('sorts records by key in descending order', function () {
    $service = new class extends \App\Filament\Resources\Core\Translations\TranslationService
    {
        public function getRootGroups(): array
        {
            return ['auth', 'common', 'dashboard'];
        }

        public function getTranslationProgress(string $lang, string $group): array
        {
            return ['missing' => 0, 'total' => 1, 'translated' => 1];
        }
    };

    $records = TranslationsTable::buildKeyRecords(
        translationService: $service,
        sortColumn: 'key',
        sortDirection: 'desc',
        targetLanguage: 'fr',
    );

    expect($records->pluck('key')->all())->toEqual(['Dashboard', 'Common', 'Auth']);
});
