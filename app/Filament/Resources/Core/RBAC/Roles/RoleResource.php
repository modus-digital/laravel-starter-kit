<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles;

use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role as RBACRole;
use App\Filament\Resources\Core\RBAC\Roles\Pages\CreateRole;
use App\Filament\Resources\Core\RBAC\Roles\Pages\EditRole;
use App\Filament\Resources\Core\RBAC\Roles\Pages\ListRoles;
use App\Filament\Resources\Core\RBAC\Roles\Pages\ViewRole;
use App\Filament\Resources\Core\RBAC\Roles\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Core\RBAC\Roles\RelationManagers\PermissionRelationManager;
use App\Filament\Resources\Core\RBAC\Roles\Schemas\RoleForm;
use App\Filament\Resources\Core\RBAC\Roles\Tables\RolesTable;
use App\Models\Role;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'management/access-control/roles';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_ROLES) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        // System roles cannot be deleted
        if ($record instanceof Role) {
            $isSystemRole = collect(RBACRole::cases())
                ->contains(fn (RBACRole $enum): bool => $enum->value === $record->name);

            if ($isSystemRole) {
                return false;
            }
        }

        return auth()->user()?->hasPermissionTo(Permission::DELETE_ROLES) ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::RESTORE_ROLES) ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        // Force delete requires delete permission and cannot be system role
        if ($record instanceof Role) {
            $isSystemRole = collect(RBACRole::cases())
                ->contains(fn (RBACRole $enum): bool => $enum->value === $record->name);

            if ($isSystemRole) {
                return false;
            }
        }

        return auth()->user()?->hasPermissionTo(Permission::DELETE_ROLES) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_ROLES) ?? false;
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.management');
    }

    public static function getNavigationParentItem(): string
    {
        return __('navigation.labels.access_control');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.roles');
    }

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
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
