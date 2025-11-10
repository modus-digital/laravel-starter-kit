<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC\Roles;

use App\Filament\Resources\RBAC\Roles\Pages\ListRoles;
use App\Filament\Resources\RBAC\Roles\Pages\ViewRole;
use App\Filament\Resources\RBAC\Roles\RelationManagers\PermissionRelationManager;
use App\Filament\Resources\RBAC\Roles\Schemas\RoleForm;
use App\Filament\Resources\RBAC\Roles\Tables\RolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use UnitEnum;

final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationParentItem = 'Access Control';

    protected static ?string $navigationLabel = 'Roles';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'rbac/roles';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Role::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getRelations(): array
    {
        return [
            PermissionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'view' => ViewRole::route('/{record}'),
        ];
    }
}
