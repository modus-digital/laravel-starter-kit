<?php

namespace App\Filament\Resources\RBAC;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Filament\Resources\RBAC\PermissionResource\Pages;
use App\Filament\Resources\RBAC\PermissionResource\RelationManagers\RolesRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

/**
 * Resource for managing permissions in the RBAC system.
 */
class PermissionResource extends Resource
{
  /**
   * The model the resource corresponds to.
   *
   * @var string
   */
  protected static ?string $model = Permission::class;

  /**
   * The icon of the resource.
   *
   * @var string|null
   */
  protected static ?string $navigationIcon = 'heroicon-o-key';

  /**
   * The navigation group name.
   *
   * @var string|null
   */
  protected static ?string $navigationGroup = 'Toegangsbeheer';

  /**
   * The navigation sort order.
   * 
   * @var int|null
   */
  protected static ?int $navigationSort = 2;

  /**
   * The text for the navigation label.
   *
   * @var string|null
   */
  protected static ?string $navigationLabel = 'Permissies';

  /**
   * The slug for the resource
   * 
   * @var string|null
   */
  protected static ?string $slug = '/rbac/permissions';

  /**
   * The label for this resource.
   *
   * @return string
   */
  public static function getModelLabel(): string
  {
    return 'Permissie';
  }

  /**
   * The plural label for this resource.
   *
   * @return string
   */
  public static function getPluralModelLabel(): string
  {
    return 'Permissies';
  }

  /**
   * Defines the form for viewing permission details.
   *
   * @param Form $form
   * @return Form
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
                  ->formatStateUsing(fn (Permission $record): ?string => 
                    collect(PermissionEnum::cases())->first(fn ($case) => $case->value === $record->name)->name
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
   * Defines the table for displaying permissions.
   *
   * @param Table $table
   * @return Table
   */
  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('enum_key')
          ->label('Enum Key')
          ->badge()
          ->sortable(false) // Kan niet sorteren op een berekende kolom in de database
          ->getStateUsing(function (Permission $record): ?string {
            $permissionEnum = collect(PermissionEnum::cases())
              ->first(fn ($case) => $case->value === $record->name);
            return $permissionEnum ? $permissionEnum->name : null;
          }),
        Tables\Columns\TextColumn::make('name')
          ->label('Naam')
          ->searchable()
          ->sortable(),
        Tables\Columns\IconColumn::make('linked_to_enum')
          ->label('Gekoppeld aan enum')
          ->boolean()
          ->getStateUsing(fn (Permission $record): bool => 
            self::isPermissionLinkedToEnum($record))
          ->tooltip('Geeft aan of deze permissie gekoppeld is aan een enum waarde'),
        Tables\Columns\TextColumn::make('roles_count')
          ->label('Aantal rollen')
          ->counts('roles')
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
              $enumValues = collect(PermissionEnum::cases())->map(fn ($case) => $case->value)->toArray();
              
              if ($data['value'] === '1') {
                $query->whereIn('name', $enumValues);
              } else {
                $query->whereNotIn('name', $enumValues);
              }
            });
          }),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\DeleteAction::make()
          ->visible(fn (Permission $record): bool => 
            !self::isPermissionLinkedToEnum($record)),
      ])
      // No bulk actions needed
      ->headerActions([
        Tables\Actions\Action::make('sync-permissions')
          ->label('Synchroniseren met enums')
          ->icon('heroicon-o-arrow-path')
          ->color('primary')
          ->action(function () {
            $count = 0;
            
            // Create or update permissions from enum
            foreach (PermissionEnum::cases() as $permission) {
              Permission::updateOrCreate(
                ['name' => $permission->value],
                ['description' => $permission->getDescription()]
              );
              $count++;
            }
            
            \Filament\Notifications\Notification::make()
              ->title('Synchronisatie voltooid')
              ->body("$count permissies zijn gesynchroniseerd.")
              ->success()
              ->send();
          }),
      ]);
  }

  /**
   * Returns the related pages for the resource.
   *
   * @return array
   */
  public static function getRelations(): array
  {
    return [
      RolesRelationManager::class,
    ];
  }
  
  /**
   * Define the custom query for retrieving records.
   * 
   * @return Builder
   */
  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()->orderBy('name');
  }

  /**
   * Returns the pages for the resource.
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index' => Pages\ListPermissions::route('/'),
      'view' => Pages\ViewPermission::route('/{record}'),
    ];
  }
  
  /**
   * Checks if a permission is linked to an enum value.
   *
   * @param Permission $permission The permission to check
   * @return bool True if the permission is linked to an enum, false otherwise
   */
  protected static function isPermissionLinkedToEnum(Permission $permission): bool
  {
    return collect(PermissionEnum::cases())
      ->contains(fn ($case) => $case->value === $permission->name);
  }
}
