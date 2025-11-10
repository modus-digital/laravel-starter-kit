<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC\Permissions;

use App\Filament\Resources\RBAC\Permissions\Pages\ListPermissions;
use App\Filament\Resources\RBAC\Permissions\Pages\ViewPermission;
use App\Filament\Resources\RBAC\Permissions\RelationManagers\RoleRelationManager;
use App\Filament\Resources\RBAC\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\RBAC\Permissions\Tables\PermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use UnitEnum;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationParentItem = 'Access Control';

    protected static ?string $navigationLabel = 'Permissions';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'rbac/permissions';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Permission::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getRelations(): array
    {
        return [
            RoleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }
}
