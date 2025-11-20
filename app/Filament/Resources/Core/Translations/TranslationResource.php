<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations;

use App\Filament\Resources\Core\Translations\Pages\EditGroupTranslation;
use App\Filament\Resources\Core\Translations\Pages\ListTranslations;
use App\Filament\Resources\Core\Translations\Pages\QuickTranslate;
use App\Filament\Resources\Core\Translations\Tables\TranslationsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class TranslationResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'system/translations';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.translation_manager');
    }

    public static function table(Table $table): Table
    {
        return TranslationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslations::route('/'),
            'group' => EditGroupTranslation::route('/{group}'),
            'quick-translate' => QuickTranslate::route('{group}/quick-translate'),
        ];
    }
}
