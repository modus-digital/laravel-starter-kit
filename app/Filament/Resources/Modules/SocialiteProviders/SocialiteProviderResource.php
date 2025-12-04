<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders;

use App\Enums\RBAC\Permission;
use App\Filament\Resources\Modules\SocialiteProviders\Pages\EditSocialiteProvider;
use App\Filament\Resources\Modules\SocialiteProviders\Pages\ListSocialiteProviders;
use App\Filament\Resources\Modules\SocialiteProviders\Pages\ViewSocialiteProvider;
use App\Filament\Resources\Modules\SocialiteProviders\Schemas\SocialiteProviderForm;
use App\Filament\Resources\Modules\SocialiteProviders\Tables\SocialiteProvidersTable;
use App\Models\Modules\SocialiteProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class SocialiteProviderResource extends Resource
{
    protected static ?string $model = SocialiteProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'system/auth-providers';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::UPDATE_SOCIALITE_PROVIDERS) ?? false;
    }

    public static function canCreate(): bool
    {
        // Providers are predefined, not created manually
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::UPDATE_SOCIALITE_PROVIDERS) ?? false;
    }

    public static function canDelete($record): bool
    {
        // Providers should not be deleted
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only show if module is enabled and user has permission
        return config('modules.socialite.enabled', false)
            && (auth()->user()?->hasPermissionTo(Permission::UPDATE_SOCIALITE_PROVIDERS) ?? false);
    }

    public static function getModelLabel(): string
    {
        return __('admin.socialite_providers.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.socialite_providers.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.socialite_providers.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.system');
    }

    public static function form(Schema $schema): Schema
    {
        return SocialiteProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialiteProvidersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialiteProviders::route('/'),
            'view' => ViewSocialiteProvider::route('/{record}'),
            'edit' => EditSocialiteProvider::route('/{record}/edit'),
        ];
    }
}
