<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations;

use App\Enums\RBAC\Permission;
use App\Filament\Resources\Core\Translations\Pages\EditGroupTranslation;
use App\Filament\Resources\Core\Translations\Pages\ListTranslations;
use App\Filament\Resources\Core\Translations\Pages\QuickTranslate;
use App\Filament\Resources\Core\Translations\Tables\TranslationsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class TranslationResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 12;

    protected static ?string $slug = 'system/translations';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false;
    }

    public static function getNavigationGroup(): string
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
