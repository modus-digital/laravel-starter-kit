<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Users;

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
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'system/users';

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
