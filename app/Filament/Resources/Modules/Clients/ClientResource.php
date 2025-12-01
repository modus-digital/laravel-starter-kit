<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients;

use App\Filament\Resources\Modules\Clients\Pages\CreateClient;
use App\Filament\Resources\Modules\Clients\Pages\EditClient;
use App\Filament\Resources\Modules\Clients\Pages\ListClients;
use App\Filament\Resources\Modules\Clients\Pages\ViewClient;
use App\Filament\Resources\Modules\Clients\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Modules\Clients\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Modules\Clients\Schemas\ClientForm;
use App\Filament\Resources\Modules\Clients\Tables\ClientsTable;
use App\Models\Modules\Clients\Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'management/clients';

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.clients.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Client::count();

        if ($count > 100) {
            return '99+';
        }

        return $count > 0 ? (string) $count : null;
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
            'view' => ViewClient::route('/{record}'),
        ];
    }

    /**
     * @return Builder<Client>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
