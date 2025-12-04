<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Permissions;

use App\Enums\RBAC\Permission as RBACPermission;
use App\Filament\Resources\Core\RBAC\Permissions\Pages\ListPermissions;
use App\Filament\Resources\Core\RBAC\Permissions\Pages\ViewPermission;
use App\Filament\Resources\Core\RBAC\Permissions\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Core\RBAC\Permissions\RelationManagers\RoleRelationManager;
use App\Filament\Resources\Core\RBAC\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\Core\RBAC\Permissions\Tables\PermissionsTable;
use App\Models\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'management/access-control/permissions';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(RBACPermission::READ_ROLES) ?? false;
    }

    public static function canCreate(): bool
    {
        // Permissions are managed via enum sync, not created manually
        return false;
    }

    public static function canEdit($record): bool
    {
        // Permissions are managed via enum sync, not edited manually
        return false;
    }

    public static function canDelete($record): bool
    {
        // Only allow deleting permissions that are NOT linked to enum
        if ($record instanceof Permission) {
            $isLinkedToEnum = collect(RBACPermission::cases())
                ->contains(fn (RBACPermission $enum): bool => $enum->value === $record->name);

            return ! $isLinkedToEnum;
        }

        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(RBACPermission::READ_ROLES) ?? false;
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
        return __('navigation.labels.permissions');
    }

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
            ActivitiesRelationManager::class,
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
