<?php

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Role as RoleEnum;
use App\Filament\Resources\RBAC\RoleResource\Pages;
use App\Filament\Resources\RBAC\RoleResource\RelationManagers\PermissionsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
    public static function getModelLabel(): string
    {
        return 'Rol';
    }

    /**
     * The plural label for this resource.
     */
    public static function getPluralModelLabel(): string
    {
        return 'Rollen';
    }

    /**
     * Defines the form for viewing role details.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('enum_key')
                                    ->label('Enum Key')
                                    ->formatStateUsing(fn (Role $record): ?string => collect(RoleEnum::cases())->first(fn ($case) => $case->value === $record->name)->name
                                    )
                                    ->disabled(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Naam')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Beschrijving')
                            ->disabled()
                            ->rows(3),
                    ]),
            ]);
    }

    /**
     * Defines the table for displaying roles.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enum_key')
                    ->label('Enum Key')
                    ->badge()
                    ->sortable(false) // Kan niet sorteren op een berekende kolom in de database
                    ->getStateUsing(function (Role $record): ?string {
                        $roleEnum = collect(RoleEnum::cases())
                            ->first(fn ($case) => $case->value === $record->name);

                        return $roleEnum ? $roleEnum->name : null;
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->boolean()
                    ->getStateUsing(fn (Role $record): bool => self::isRoleLinkedToEnum($record))
                    ->tooltip('Geeft aan of deze rol gekoppeld is aan een enum waarde'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Aantal permissies')
                    ->counts('permissions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt op')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Laatst bijgewerkt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->options([
                        '1' => 'Ja',
                        '0' => 'Nee',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data) {
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
            Tables\Actions\ViewAction::make(),
            Tables\Actions\DeleteAction::make()
                    ->visible(fn (Role $record): bool => ! self::isRoleLinkedToEnum($record)),
        ])
          // No bulk actions needed
            ->headerActions([
            Tables\Actions\Action::make('sync-roles')
                    ->label('Synchroniseren met enums')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function () {
                        $count = 0;

                        // Create or update roles from enum
                        foreach (RoleEnum::cases() as $role) {
                            Role::updateOrCreate(
                                ['name' => $role->value],
                                ['description' => $role->getDescription()]
                            );
                            $count++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Synchronisatie voltooid')
                            ->body("$count rollen zijn gesynchroniseerd.")
                            ->success()
                            ->send();
                    }),
        ]);
    }

    /**
     * Returns the related pages for the resource.
     */
    public static function getRelations(): array
    {
        return [
            PermissionsRelationManager::class,
        ];
    }

    /**
     * Define the custom query for retrieving records.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('name');
    }

    /**
     * Returns the pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'view' => Pages\ViewRole::route('/{record}'),
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
            ->contains(fn ($case) => $case->value === $role->name);
    }
}
