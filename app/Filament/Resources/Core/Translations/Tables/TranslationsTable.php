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
            ->heading(fn (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view(
                view: 'filament.resources.core.translations.tables.header-toolbar',
                data: [
                    'widget' => LanguageSelector::class,
                ],
            ))
            ->recordAction(null)
            ->recordUrl(
                fn (array $record): string => TranslationResource::getUrl('group', [
                    'group' => $record['__key'],
                ]),
            )
            ->paginated(false)
            ->records(
                fn (?string $search, ?string $sortColumn, ?string $sortDirection): Collection => self::buildKeyRecords(
                    translationService: $translationService,
                    search: $search,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection,
                    targetLanguage: $targetLanguage,
                ),
            )
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.translations.table.translation_key'))
                    ->sortable()
                    ->searchable(),

                IconColumn::make('status')
                    ->label(__('admin.translations.table.fully_translated'))
                    ->boolean()
                    ->sortable()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('missing')
                    ->label(__('admin.translations.table.missing'))
                    ->sortable()
                    ->formatStateUsing(
                        fn (int $state, array $record): string => "{$state} / {$record['total']}",
                    ),
            ])
            ->headerActions([
                Action::make('create_language')
                    ->label(__('admin.translations.table.create_language'))
                    ->icon('heroicon-o-plus')
                    ->schema([
                        TextInput::make('language_code')
                            ->label(__('admin.translations.table.language_code'))
                            ->required()
                            ->maxLength(2)
                            ->placeholder(__('admin.translations.table.language_code_placeholder'))
                            ->helperText(__('admin.translations.table.language_code_helper')),
                    ])
                    ->action(fn (array $data) => self::createLanguage(data: $data, service: $translationService)),
            ])
            ->filters([]);
    }

    /**
     * @return Collection<int, array{__key: string, key: string, status: bool, missing: int, total: int}>
     */
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
                $missing = $progress['missing'];
                $total = $progress['total'];

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
                fn (array $record): int => $record['missing'],
                descending: $descending,
            );
        } elseif ($sortColumn === 'status') {
            $records = $records->sortBy(
                fn (array $record): int => $record['status'] ? 1 : 0,
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

    /**
     * @param  array<string, mixed>  $data
     */
    private static function createLanguage(array $data, TranslationService $service): void
    {
        if ($service->languageExists($data['language_code'])) {
            Notification::make('language_exists')
                ->title(__('admin.translations.notifications.language_exists.title'))
                ->body(__('admin.translations.notifications.language_exists.body'))
                ->danger()
                ->send();

            return;
        }

        try {
            $service->createLanguage($data['language_code']);

            Notification::make('language_created')
                ->title(__('admin.translations.notifications.language_created.title'))
                ->body(__('admin.translations.notifications.language_created.body'))
                ->success()
                ->send();

            return;
        } catch (Exception) {
            Notification::make('language_creation_failed')
                ->title(__('admin.translations.notifications.language_creation_failed.title'))
                ->body(__('admin.translations.notifications.language_creation_failed.body'))
                ->danger()
                ->send();
        }

    }
}
