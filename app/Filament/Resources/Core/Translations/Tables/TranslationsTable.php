<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations\Tables;

use App\Filament\Resources\Core\Translations\TranslationResource;
use App\Filament\Resources\Core\Translations\TranslationService;
use App\Filament\Resources\Core\Translations\Widgets\LanguageSelector;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class TranslationsTable
{
    public static function configure(Table $table): Table
    {
        $translationService = app()->make(TranslationService::class);
        $targetLanguage = $translationService->getTargetLanguage();

        return $table
            ->heading(fn () => view(
                view: 'filament.resources.core.translations.tables.header-toolbar',
                data: [
                    'widget' => LanguageSelector::class,
                ],
            ))
            ->recordAction(null)
            ->recordUrl(
                fn (array $record) => TranslationResource::getUrl('group', [
                    'group' => $record['__key'],
                ]),
            )
            ->paginated(false)
            ->records(
                fn (?string $search, ?string $sortColumn, ?string $sortDirection) => self::buildKeyRecords(
                    translationService: $translationService,
                    search: $search,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection,
                    targetLanguage: $targetLanguage,
                ),
            )
            ->columns([
                TextColumn::make('key')
                    ->label('Translation Key')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('status')
                    ->label('Fully Translated')
                    ->boolean()
                    ->sortable()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('missing')
                    ->label('Missing')
                    ->sortable()
                    ->formatStateUsing(
                        fn (int $state, array $record): string => "{$state} / {$record['total']}",
                    ),
            ])
            ->headerActions([
                Action::make('create_language')
                    ->label('Create Language')
                    ->icon('heroicon-o-plus')
                    ->schema([
                        TextInput::make('language_code')
                            ->label('Language Code')
                            ->required()
                            ->maxLength(2)
                            ->placeholder('e.g. en, fr, es, etc.')
                            ->helperText('The code for the language you want to create.'),
                    ])
                    ->action(fn (array $data) => self::createLanguage(data: $data, service: $translationService)),
            ])
            ->filters([]);
    }

    public static function buildKeyRecords(
        TranslationService $translationService,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
        ?string $targetLanguage = null,
    ): Collection {
        $targetLanguage ??= $translationService->getTargetLanguage();

        $groups = collect($translationService->getRootGroups());

        if (filled($search)) {
            $groups = $groups->filter(
                fn (string $group) => Str::contains($group, $search, ignoreCase: true),
            );
        }

        $records = $groups
            ->map(function (string $group) use ($translationService, $targetLanguage): array {
                $progress = $translationService->getTranslationProgress($targetLanguage, $group);
                $missing = $progress['missing'] ?? 0;
                $total = $progress['total'] ?? 0;

                return [
                    '__key' => $group,
                    'key' => ucfirst($group),
                    'status' => $total > 0 && $missing === 0,
                    'missing' => $missing,
                    'total' => $total,
                ];
            });

        $descending = mb_strtolower($sortDirection ?? 'asc') === 'desc';

        if ($sortColumn === 'missing') {
            $records = $records->sortBy(
                fn (array $record) => $record['missing'],
                descending: $descending,
            );
        } elseif ($sortColumn === 'status') {
            $records = $records->sortBy(
                fn (array $record) => $record['status'] ? 1 : 0,
                descending: $descending,
            );
        } else {
            $records = $records->sortBy(
                fn (array $record) => Str::lower($record['key']),
                descending: $descending,
            );
        }

        return $records->values();
    }

    private static function createLanguage(array $data, TranslationService $service): void
    {
        if ($service->languageExists($data['language_code'])) {
            Notification::make('language_exists')
                ->title('Language already exists')
                ->body('The language you are trying to create already exists.')
                ->danger()
                ->send();

            return;
        }

        try {
            $service->createLanguage($data['language_code']);

            Notification::make('language_created')
                ->title('Language created')
                ->body('The language you are trying to create has been created successfully.')
                ->success()
                ->send();

            return;
        } catch (Exception $e) {
            Notification::make('language_creation_failed')
                ->title('Language creation failed')
                ->body('The language you are trying to create failed to be created.')
                ->danger()
                ->send();
        }

    }
}
