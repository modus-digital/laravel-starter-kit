<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Filament\Resources\RBAC\PermissionResource\Pages\{ListPermissions, ViewPermission};
use App\Filament\Resources\RBAC\PermissionResource\RelationManagers\RolesRelationManager;
use Filament\Forms\Components\{Grid, Section, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{Action, DeleteAction, ViewAction};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

/**
 * Resource for managing permissions in the RBAC (Role-Based Access Control) system.
 *
 * This resource provides CRUD operations for permissions, including:
 * - Viewing permission details and associated roles
 * - Synchronizing permissions with enum definitions
 * - Managing permission-role relationships
 *
 * @since 1.0.0
 */
class PermissionResource extends Resource
{
    /**
     * The Eloquent model that this resource corresponds to.
     */
    protected static ?string $model = Permission::class;

    /**
     * The navigation icon displayed in the admin panel.
     */
    protected static ?string $navigationIcon = 'heroicon-o-key';

    /**
     * The sort order for navigation items.
     */
    protected static ?int $navigationSort = 2;

    /**
     * The URL slug for this resource.
     */
    protected static ?string $slug = '/rbac/permissions';

    /**
     * Get the singular label for this resource.
     *
     * @return string The singular model label
     */
    public static function getModelLabel(): string
    {
        return __('admin.resources.rbac.permissions.label.singular');
    }

    /**
     * Get the navigation label displayed in the admin panel.
     *
     * @return string The navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.resources.rbac.permissions.label.plural');
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
        return __('admin.resources.rbac.permissions.label.plural');
    }

    /**
     * Define the form schema for viewing permission details.
     *
     * @param Form $form The form instance
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
                                    ->label(__('admin.resources.rbac.permissions.form.enum_key'))
                                    ->formatStateUsing(
                                        fn(Permission $record): string => collect(PermissionEnum::cases())
                                            ->first(fn($case): bool => $case->value === $record->name)->name ?? 'Unknown'
                                    )
                                    ->disabled(),
                                TextInput::make('name')
                                    ->label(__('admin.resources.rbac.permissions.form.name'))
                                    ->disabled(),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.resources.rbac.permissions.form.description'))
                            ->disabled()
                            ->rows(3),
                    ]),
            ]);
    }

    /**
     * Define the table schema for displaying permissions.
     *
     * @param Table $table The table instance
     * @return Table The configured table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.resources.rbac.permissions.table.enum_key'))
                    ->badge()
                    ->sortable(false) // Cannot sort on computed column in database
                    ->getStateUsing(function (Permission $record): ?string {
                        $permissionEnum = collect(PermissionEnum::cases())
                            ->first(fn($case): bool => $case->value === $record->name);

                        return $permissionEnum ? $permissionEnum->name : null;
                    }),
                TextColumn::make('name')
                    ->label(__('admin.resources.rbac.permissions.table.name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('linked_to_enum')
                    ->label(__('admin.resources.rbac.permissions.table.linked_to_enum'))
                    ->boolean()
                    ->getStateUsing(fn(Permission $record): bool => self::isPermissionLinkedToEnum($record))
                    ->tooltip('Indicates whether this permission is linked to an enum value'),
                TextColumn::make('roles_count')
                    ->label(__('admin.resources.rbac.permissions.table.roles_count'))
                    ->counts('roles')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.rbac.permissions.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.rbac.permissions.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.resources.rbac.permissions.table.linked_to_enum'))
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data): void {
                            $enumValues = collect(PermissionEnum::cases())->map(fn($case) => $case->value)->toArray();

                            if ($data['value'] === '1') {
                                $query->whereIn('name', $enumValues);
                            } else {
                                $query->whereNotIn('name', $enumValues);
                            }
                        });
                    }),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn(Permission $record): bool => ! self::isPermissionLinkedToEnum($record)),
            ])
            ->headerActions([
                Action::make('sync-permissions')
                    ->label(__('admin.resources.rbac.permissions.actions.sync'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function (): void {
                        $count = 0;

                        // Create or update permissions from enum
                        foreach (PermissionEnum::cases() as $permission) {
                            Permission::updateOrCreate(
                                ['name' => $permission->value],
                                ['description' => $permission->getDescription()]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title(__('admin.resources.rbac.permissions.notifications.sync_success.title'))
                            ->body(__('admin.resources.rbac.permissions.notifications.sync_success.message', ['count' => $count]))
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
            RolesRelationManager::class,
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
            'index' => ListPermissions::route('/'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }

    /**
     * Check if a permission is linked to an enum value.
     *
     * @param Permission $permission The permission to check
     * @return bool True if the permission is linked to an enum, false otherwise
     */
    protected static function isPermissionLinkedToEnum(Permission $permission): bool
    {
        return collect(PermissionEnum::cases())
            ->contains(fn($case): bool => $case->value === $permission->name);
    }
}
