<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Permissions;

use App\Filament\Resources\Core\RBAC\Permissions\Pages\ListPermissions;
use App\Filament\Resources\Core\RBAC\Permissions\Pages\ViewPermission;
use App\Filament\Resources\Core\RBAC\Permissions\RelationManagers\RoleRelationManager;
use App\Filament\Resources\Core\RBAC\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\Core\RBAC\Permissions\Tables\PermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'system/access-control/permissions';

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
