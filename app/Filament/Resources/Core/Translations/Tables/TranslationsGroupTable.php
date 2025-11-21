<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations\Tables;

use App\Enums\Language;
use App\Filament\Resources\Core\Translations\TranslationResource;
use App\Filament\Resources\Core\Translations\TranslationService;
use App\Filament\Resources\Core\Translations\Widgets\LanguageSelector;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class TranslationsGroupTable extends TableWidget
{
    public static function configure(Table $table, string $group): Table
    {
        $service = app()->make(TranslationService::class);
        $targetLanguage = $service->getTargetLanguage();

        return $table
            ->heading(fn (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view(
                view: 'filament.resources.core.translations.tables.header-toolbar',
                data: [
                    'widget' => LanguageSelector::class,
                ],
            ))
            ->paginated(false)
            ->records(fn (): Collection => self::buildRecords($service, $targetLanguage, $group))
            ->columns([
                TextColumn::make('english')
                    ->label('Base')
                    ->description(fn (array $record) => $record['full_key'])
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (array $record) => $record['translation'])
                    ->grow(false),
                TextColumn::make('translation')
                    ->label(Str::upper($targetLanguage))
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->headerActions([
                Action::make('quick-translate')
                    ->label('Quick Translate')
                    ->icon(Heroicon::OutlinedBolt)
                    ->color(Color::Green)
                    ->url(fn (): string => TranslationResource::getUrl('quick-translate', ['group' => $group])),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencil)
                    ->schema([
                        Section::make('Base')
                            ->schema([
                                Textarea::make('english')
                                    ->default(fn (array $record) => $record['english'])
                                    ->disabled()
                                    ->rows(2)
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                        Section::make(Language::from($targetLanguage)->label())
                            ->schema([
                                Textarea::make('translation')
                                    ->hiddenLabel()
                                    ->rows(2)
                                    ->required()
                                    ->default(fn (array $record) => $record['translation'] ?? '')
                                    ->extraInputAttributes(['class' => 'resize-none', 'autofocus' => true])
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ])
                    ->action(fn (array $record, array $data) => self::saveTranslation($record, $data)),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function buildRecords(TranslationService $service, string $targetLanguage, string $group): Collection
    {
        $englishGroup = data_get($service->getLanguageFile('en'), $group, []);
        $targetGroup = data_get($service->getLanguageFile($targetLanguage), $group, []);

        $englishFlat = $service->flattenTranslations(is_array($englishGroup) ? $englishGroup : []);
        $targetFlat = $service->flattenTranslations(is_array($targetGroup) ? $targetGroup : []);

        return collect($englishFlat)
            ->map(fn (mixed $value, string $key): array => [
                '__key' => $key,
                'key' => $key,
                'english' => $value,
                'translation' => $targetFlat[$key] ?? '',
                'full_key' => ($group !== '' && $group !== '0' ? $group.'.' : '').$key,
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $data
     */
    private static function saveTranslation(array $record, array $data): void
    {
        $service = app()->make(TranslationService::class);

        $service->setTranslation(
            $service->getTargetLanguage(),
            $record['full_key'],
            $data['translation'] ?? ''
        );
    }
}
