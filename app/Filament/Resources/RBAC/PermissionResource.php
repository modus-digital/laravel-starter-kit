<?php

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Filament\Resources\RBAC\PermissionResource\Pages;
use App\Filament\Resources\RBAC\PermissionResource\Pages\ListPermissions;
use App\Filament\Resources\RBAC\PermissionResource\Pages\ViewPermission;
use App\Filament\Resources\RBAC\PermissionResource\RelationManagers\RolesRelationManager;
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
use Override;
use Spatie\Permission\Models\Permission;

/**
 * Resource for managing permissions in the RBAC system.
 */
class PermissionResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = Permission::class;

    /**
     * The icon of the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-key';

    /**
     * The navigation group name.
     */
    protected static ?string $navigationGroup = 'Toegangsbeheer';

    /**
     * The navigation sort order.
     */
    protected static ?int $navigationSort = 2;

    /**
     * The text for the navigation label.
     */
    protected static ?string $navigationLabel = 'Permissies';

    /**
     * The slug for the resource
     */
    protected static ?string $slug = '/rbac/permissions';

    /**
     * The label for this resource.
     */
    #[Override]
    public static function getModelLabel(): string
    {
        return 'Permissie';
    }

    /**
     * The plural label for this resource.
     */
    #[Override]
    public static function getPluralModelLabel(): string
    {
        return 'Permissies';
    }

    /**
     * Defines the form for viewing permission details.
     */
    #[Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('enum_key')
                                    ->label('Enum Key')
                                    ->formatStateUsing(
                                        fn (Permission $record): string => collect(PermissionEnum::cases())
                                            ->first(fn ($case): bool => $case->value === $record->name)->name ?? 'Unknown'
                                    )
                                    ->disabled(),
                                TextInput::make('name')
                                    ->label('Naam')
                                    ->disabled(),
                            ]),
                        Textarea::make('description')
                            ->label('Beschrijving')
                            ->disabled()
                            ->rows(3),
                    ]),
            ]);
    }

    /**
     * Defines the table for displaying permissions.
     */
    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enum_key')
                    ->label('Enum Key')
                    ->badge()
                    ->sortable(false) // Kan niet sorteren op een berekende kolom in de database
                    ->getStateUsing(function (Permission $record): ?string {
                        $permissionEnum = collect(PermissionEnum::cases())
                            ->first(fn ($case): bool => $case->value === $record->name);

                        return $permissionEnum ? $permissionEnum->name : null;
                    }),
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->boolean()
                    ->getStateUsing(fn (Permission $record): bool => self::isPermissionLinkedToEnum($record))
                    ->tooltip('Geeft aan of deze permissie gekoppeld is aan een enum waarde'),
                TextColumn::make('roles_count')
                    ->label('Aantal rollen')
                    ->counts('roles')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Aangemaakt op')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Laatst bijgewerkt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->options([
                        '1' => 'Ja',
                        '0' => 'Nee',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data): void {
                            $enumValues = collect(PermissionEnum::cases())->map(fn ($case) => $case->value)->toArray();

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
                    ->visible(fn (Permission $record): bool => ! self::isPermissionLinkedToEnum($record)),
            ])
            // No bulk actions needed
            ->headerActions([
                Action::make('sync-permissions')
                    ->label('Synchroniseren met enums')
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
                            ->title('Synchronisatie voltooid')
                            ->body($count . ' permissies zijn gesynchroniseerd.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Returns the related pages for the resource.
     */
    #[Override]
    public static function getRelations(): array
    {
        return [
            RolesRelationManager::class,
        ];
    }

    /**
     * Define the custom query for retrieving records.
     *
     * @return Builder<\Spatie\Permission\Models\Permission>
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('name');
    }

    /**
     * Returns the pages for the resource.
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }

    /**
     * Checks if a permission is linked to an enum value.
     *
     * @param  Permission  $permission  The permission to check
     * @return bool True if the permission is linked to an enum, false otherwise
     */
    protected static function isPermissionLinkedToEnum(Permission $permission): bool
    {
        return collect(PermissionEnum::cases())
            ->contains(fn ($case): bool => $case->value === $permission->name);
    }
}
