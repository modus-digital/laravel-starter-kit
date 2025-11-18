<?php

namespace App\Filament\Resources\Core\RBAC\Roles;

use App\Filament\Resources\Core\RBAC\Roles\Pages\ViewRole;
use App\Filament\Resources\Core\RBAC\Roles\Pages\ListRoles;
use App\Filament\Resources\Core\RBAC\Roles\Schemas\RoleForm;
use App\Filament\Resources\Core\RBAC\Roles\Tables\RolesTable;
use App\Filament\Resources\Core\RBAC\Roles\RelationManagers\PermissionRelationManager;

use Spatie\Permission\Models\Role;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.system');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('navigation.labels.access_control');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.roles');
    }

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'system/access-control/roles';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Role::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
