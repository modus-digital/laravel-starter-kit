<?php

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Role as RoleEnum;
use App\Filament\Resources\RBAC\RoleResource\Pages;
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
use Override;
use Spatie\Permission\Models\Role;

/**
 * Resource for managing roles in the RBAC system.
 */
class RoleResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    protected static ?string $model = Role::class;

    /**
     * The icon of the resource.
     */
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    /**
     * The navigation sort order.
     */
    protected static ?int $navigationSort = 1;

    /**
     * The navigation group name.
     */
    protected static ?string $navigationGroup = 'Toegangsbeheer';

    /**
     * The text for the navigation label.
     */
    protected static ?string $navigationLabel = 'Rollen';

    /**
     * The slug for the resource
     */
    protected static ?string $slug = '/rbac/roles';

    /**
     * The label for this resource.
     */
    #[Override]
    public static function getModelLabel(): string
    {
        return 'Rol';
    }

    /**
     * The plural label for this resource.
     */
    #[Override]
    public static function getPluralModelLabel(): string
    {
        return 'Rollen';
    }

    /**
     * Defines the form for viewing role details.
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
                                        fn (Role $record): string => collect(RoleEnum::cases())
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
     * Defines the table for displaying roles.
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
                    ->getStateUsing(function (Role $record): ?string {
                        $roleEnum = collect(RoleEnum::cases())
                            ->first(fn ($case): bool => $case->value === $record->name);

                        return $roleEnum ? $roleEnum->name : null;
                    }),
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->boolean()
                    ->getStateUsing(fn (Role $record): bool => self::isRoleLinkedToEnum($record))
                    ->tooltip('Geeft aan of deze rol gekoppeld is aan een enum waarde'),
                TextColumn::make('permissions_count')
                    ->label('Aantal permissies')
                    ->counts('permissions')
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
            // No bulk actions needed
            ->headerActions([
                Action::make('sync-roles')
                    ->label('Synchroniseren met enums')
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
                            ->title('Synchronisatie voltooid')
                            ->body($count . ' rollen zijn gesynchroniseerd.')
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
            PermissionsRelationManager::class,
        ];
    }

    /**
     * Define the custom query for retrieving records.
     *
     * @return Builder<\Spatie\Permission\Models\Role>
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
            'index' => ListRoles::route('/'),
            'view' => ViewRole::route('/{record}'),
        ];
    }

    /**
     * Checks if a role is linked to an enum value.
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
