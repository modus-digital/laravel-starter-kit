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

    protected static string|UnitEnum|null $navigationGroup = 'Authentication';

    protected static ?int $navigationSort = 10;

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

    public static function canCreate(): bool
    {
        return false; // Providers are pre-configured
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Providers are pre-configured
    }
}
