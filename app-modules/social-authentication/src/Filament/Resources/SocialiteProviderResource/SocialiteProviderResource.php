<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource;

use App\Enums\RBAC\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages\EditSocialiteProvider;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Pages\ListSocialiteProviders;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Schemas\SocialiteProviderForm;
use ModusDigital\SocialAuthentication\Filament\Resources\SocialiteProviderResource\Tables\SocialiteProvidersTable;
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;
use UnitEnum;

final class SocialiteProviderResource extends Resource
{
    protected static ?string $model = SocialiteProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Socialite Providers';

    protected static string|UnitEnum|null $navigationGroup = 'Modules';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'settings/auth/providers';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_OAUTH_PROVIDERS->value) ?? false;
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
            'edit' => EditSocialiteProvider::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('social-authentication::social-authentication.title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('social-authentication::social-authentication.group');
    }

    public static function getModelLabel(): string
    {
        return __('social-authentication::social-authentication.label.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('social-authentication::social-authentication.label.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = SocialiteProvider::enabled()->count();

        return $count > 0 ? $count.' '.__('social-authentication::social-authentication.navigation.badge_enabled') : null;
    }

    public static function canCreate(): bool
    {
        return false; // Providers are pre-configured
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Providers are pre-configured
    }
}
