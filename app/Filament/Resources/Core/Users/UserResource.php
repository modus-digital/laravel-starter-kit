<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users;

use App\Enums\RBAC\Permission;
use App\Filament\Resources\Core\Users\Pages\CreateUser;
use App\Filament\Resources\Core\Users\Pages\EditUser;
use App\Filament\Resources\Core\Users\Pages\ListUsers;
use App\Filament\Resources\Core\Users\Pages\ViewUser;
use App\Filament\Resources\Core\Users\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Core\Users\Schemas\UserForm;
use App\Filament\Resources\Core\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'system/users';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_USERS) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::CREATE_USERS) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::UPDATE_USERS) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::DELETE_USERS) ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::RESTORE_USERS) ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        // Force delete requires delete permission
        return auth()->user()?->hasPermissionTo(Permission::DELETE_USERS) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_USERS) ?? false;
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.users.navigation_label');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<User>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
