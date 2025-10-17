<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Filament\Resources\ClientResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ModusDigital\Clients\Filament\Resources\ClientResource\Pages\CreateClient;
use ModusDigital\Clients\Filament\Resources\ClientResource\Pages\EditClient;
use ModusDigital\Clients\Filament\Resources\ClientResource\Pages\ListClients;
use ModusDigital\Clients\Filament\Resources\ClientResource\Schemas\ClientForm;
use ModusDigital\Clients\Filament\Resources\ClientResource\Tables\ClientsTable;
use ModusDigital\Clients\Models\Client;
use UnitEnum;

final class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Modules';

    protected static ?string $navigationLabel = 'Clients';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'clients';

    public static function getNavigationLabel(): string
    {
        return __('clients::clients.title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('clients::clients.group');
    }

    public static function getModelLabel(): string
    {
        return __('clients::clients.label.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('clients::clients.label.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
