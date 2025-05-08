<?php

namespace App\Filament\Resources\RBAC\PermissionResource\RelationManagers;

use App\Enums\RBAC\Role as RoleEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Relation manager for managing roles associated with a permission.
 */
class RolesRelationManager extends RelationManager
{
  /**
   * The name of the relationship.
   *
   * @var string
   */
  protected static string $relationship = 'roles';

  /**
   * The title for this relation manager.
   *
   * @var string|null
   */
  protected static ?string $title = 'Rollen';

  /**
   * Label for the attachment button.
   *
   * @var string|null
   */
  protected static ?string $inverseRelationshipName = 'permissies';

  /**
   * Defines the form for viewing a role.
   *
   * @param Form $form
   * @return Form
   */
  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->label('Naam')
          ->disabled(),
        Forms\Components\Textarea::make('description')
          ->label('Beschrijving')
          ->rows(3)
          ->disabled(),
      ]);
  }

  /**
   * Defines the table for displaying roles linked to a permission.
   *
   * @param Table $table
   * @return Table
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('name')
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
          ->sortable()
          ->searchable(),
        Tables\Columns\IconColumn::make('linked_to_enum')
          ->label('Gekoppeld aan enum')
          ->boolean()
          ->getStateUsing(fn (Role $record): bool => 
            collect(RoleEnum::cases())->contains(fn ($case) => $case->value === $record->name))
          ->tooltip('Geeft aan of deze rol gekoppeld is aan een enum waarde'),
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
              } else {
                $query->whereNotIn('name', $enumValues);
              }
            });
          }),
      ])
      ->headerActions([
        // Custom action to add roles
        Tables\Actions\Action::make('addRoles')
          ->label('Rollen toevoegen')
          ->modalHeading('Rollen toevoegen aan deze permissie')
          ->icon('heroicon-o-plus')
          ->color('primary')
          ->form([
            Forms\Components\Select::make('roles')
              ->label('Rollen')
              ->options(function () {
                $permission = $this->getOwnerRecord();
                $existingRoleIds = $permission->roles()->pluck('id')->toArray();
                
                return Role::whereNotIn('id', $existingRoleIds)
                  ->orderBy('name')
                  ->pluck('name', 'id');
              })
              ->multiple()
              ->searchable()
              ->preload()
              ->placeholder('Selecteer rollen...')
          ])
          ->action(function (array $data): void {
            if (empty($data['roles'])) {
              return;
            }
            
            $permission = $this->getOwnerRecord();
            $newRoles = Role::whereIn('id', $data['roles'])->get();
            
            foreach ($newRoles as $role) {
              $permission->assignRole($role);
            }
            
            \Filament\Notifications\Notification::make()
              ->title(count($data['roles']) > 1 
                ? 'Rollen toegevoegd' 
                : 'Rol toegevoegd')
              ->success()
              ->send();
          })
      ])
      ->actions([
        // Custom detach action using syncRoles
        Tables\Actions\Action::make('detachRole')
          ->label('Ontkoppelen')
          ->icon('heroicon-o-trash')
          ->color('danger')
          ->requiresConfirmation()
          ->modalHeading('Rol ontkoppelen')
          ->modalDescription('Weet je zeker dat je deze rol wilt ontkoppelen van de permissie?')
          ->action(function (Role $record): void {
            $permission = $this->getOwnerRecord();
            
            // Detach the role from the permission using removeRole
            $permission->removeRole($record);
            
            \Filament\Notifications\Notification::make()
              ->title('Rol ontkoppeld')
              ->success()
              ->send();
          })
      ])
      ->bulkActions([]);
  }
}
