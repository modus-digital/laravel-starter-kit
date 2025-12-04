<?php

declare(strict_types=1);

namespace App\Filament\Resources\Modules\Clients;

use App\Enums\RBAC\Permission;
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

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_CLIENTS) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::CREATE_CLIENTS) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::UPDATE_CLIENTS) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::DELETE_CLIENTS) ?? false;
    }

    public static function canRestore($record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::RESTORE_CLIENTS) ?? false;
    }

    public static function canForceDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::DELETE_CLIENTS) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only show if module is enabled and user has permission
        return config('modules.clients.enabled', false)
            && (auth()->user()?->hasPermissionTo(Permission::READ_CLIENTS) ?? false);
    }

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
