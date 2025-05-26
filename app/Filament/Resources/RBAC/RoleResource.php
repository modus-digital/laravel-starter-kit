<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Role as RoleEnum;
use App\Filament\Resources\RBAC\RoleResource\Pages\ListRoles;
use App\Filament\Resources\RBAC\RoleResource\Pages\ViewRole;
use App\Filament\Resources\RBAC\RoleResource\RelationManagers\PermissionsRelationManager;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

/**
 * Resource for managing roles in the RBAC (Role-Based Access Control) system.
 *
 * This resource provides CRUD operations for roles, including:
 * - Viewing role details and permissions
 * - Synchronizing roles with enum definitions
 * - Managing role-permission relationships
 *
 * @since 1.0.0
 */
class RoleResource extends Resource
{
    /**
     * The Eloquent model that this resource corresponds to.
     */
    protected static ?string $model = Role::class;

    /**
     * The navigation icon displayed in the admin panel.
     */
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    /**
     * The sort order for navigation items.
     */
    protected static ?int $navigationSort = 1;

    /**
     * The URL slug for this resource.
     */
    protected static ?string $slug = '/rbac/roles';

    /**
     * Get the singular label for this resource.
     *
     * @return string The singular model label
     */
    public static function getModelLabel(): string
    {
        return __('admin.resources.rbac.roles.label.singular');
    }

    /**
     * Get the navigation label displayed in the admin panel.
     *
     * @return string The navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.resources.rbac.roles.label.plural');
    }

    /**
     * Get the navigation group this resource belongs to.
     *
     * @return string|null The navigation group name
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.toegangsbeheer');
    }

    /**
     * Get the plural label for this resource.
     *
     * @return string The plural model label
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.rbac.roles.label.plural');
    }

    /**
     * Define the form schema for viewing role details.
     *
     * @param  Form  $form  The form instance
     * @return Form The configured form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('enum_key')
                                    ->label(__('admin.resources.rbac.roles.form.enum_key'))
                                    ->formatStateUsing(
                                        fn (Role $record): string => collect(RoleEnum::cases())
                                            ->first(fn ($case): bool => $case->value === $record->name)->name ?? 'Unknown'
                                    )
                                    ->disabled(),
                                TextInput::make('name')
                                    ->label(__('admin.resources.rbac.roles.form.name'))
                                    ->disabled(),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.resources.rbac.roles.form.description'))
                            ->disabled()
                            ->rows(3),
                    ]),
            ]);
    }

    /**
     * Define the table schema for displaying roles.
     *
     * @param  Table  $table  The table instance
     * @return Table The configured table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.resources.rbac.roles.form.enum_key'))
                    ->badge()
                    ->sortable(false) // Cannot sort on computed column in database
                    ->getStateUsing(function (Role $record): ?string {
                        $roleEnum = collect(RoleEnum::cases())
                            ->first(fn ($case): bool => $case->value === $record->name);

                        return $roleEnum ? $roleEnum->name : null;
                    }),
                TextColumn::make('name')
                    ->label(__('admin.resources.rbac.roles.form.name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('linked_to_enum')
                    ->label(__('admin.resources.rbac.roles.table.linked_to_enum'))
                    ->boolean()
                    ->getStateUsing(fn (Role $record): bool => self::isRoleLinkedToEnum($record))
                    ->tooltip('Indicates whether this role is linked to an enum value'),
                TextColumn::make('permissions_count')
                    ->label(__('admin.resources.rbac.roles.table.permissions_count'))
                    ->counts('permissions')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.rbac.roles.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.rbac.roles.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.resources.rbac.roles.table.linked_to_enum'))
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data): void {
                            $enumValues = collect(RoleEnum::cases())->map(fn ($case) => $case->value)->toArray();

                            if ($data['value'] === '1') {
                                $query->whereIn('name', $enumValues);
                            }
                            else {
                                $query->whereNotIn('name', $enumValues);
                            }
                        });
                    }),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn (Role $record): bool => ! self::isRoleLinkedToEnum($record)),
            ])
            ->headerActions([
                Action::make('sync-roles')
                    ->label(__('admin.resources.rbac.roles.actions.sync'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function (): void {
                        $count = 0;

                        // Create or update roles from enum
                        foreach (RoleEnum::cases() as $role) {
                            Role::updateOrCreate(
                                ['name' => $role->value],
                                ['description' => $role->getDescription()]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title(__('admin.resources.rbac.roles.notifications.sync_success.title'))
                            ->body(__('admin.resources.rbac.roles.notifications.sync_success.message', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Get the relation managers for this resource.
     *
     * @return array<string> Array of relation manager classes
     */
    public static function getRelations(): array
    {
        return [
            PermissionsRelationManager::class,
        ];
    }

    /**
     * Define custom query for retrieving records.
     *
     * @return Builder The eloquent query builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('name');
    }

    /**
     * Get the pages associated with this resource.
     *
     * @return array<string, mixed> Array of page routes
     */
    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'view' => ViewRole::route('/{record}'),
        ];
    }

    /**
     * Check if a role is linked to an enum value.
     *
     * @param  Role  $role  The role to check
     * @return bool True if the role is linked to an enum, false otherwise
     */
    protected static function isRoleLinkedToEnum(Role $role): bool
    {
        return collect(RoleEnum::cases())
            ->contains(fn ($case): bool => $case->value === $role->name);
    }
}
