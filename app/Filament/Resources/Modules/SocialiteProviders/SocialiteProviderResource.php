<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\SocialiteProviders;

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

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'modules/auth/providers';

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
        return __('navigation.groups.modules');
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
