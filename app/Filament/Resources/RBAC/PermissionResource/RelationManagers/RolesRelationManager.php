<?php

namespace App\Filament\Resources\RBAC\PermissionResource\RelationManagers;

use App\Enums\RBAC\Role as RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Relation manager for managing roles associated with a permission.
class RolesRelationManager extends RelationManager
{
    // The name of the relationship.
    protected static string $relationship = 'roles';

    // Label for the attachment button.
    protected static ?string $inverseRelationshipName = 'permissies';

    // Defines the form for viewing a role.
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Naam')
                    ->disabled(),
                Textarea::make('description')
                    ->label('Beschrijving')
                    ->rows(3)
                    ->disabled(),
            ]);
    }

    // Defines the table for displaying roles linked to a permission.
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('enum_key')
                    ->label('Enum Key')
                    ->badge()
                    ->sortable(false) // Cannot sort on a calculated column in the database
                    ->getStateUsing(function (Role $record): ?string {
                        $roleEnum = collect(RoleEnum::cases())
                            ->first(fn ($case): bool => $case->value === $record->name);

                        return $roleEnum ? $roleEnum->name : null;
                    }),
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->boolean()
                    ->getStateUsing(fn (Role $record): bool => collect(RoleEnum::cases())->contains(fn ($case): bool => $case->value === $record->name))
                    ->tooltip('Geeft aan of deze rol gekoppeld is aan een enum waarde'),
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
            ->headerActions([
                // Custom action to add roles
                Action::make('addRoles')
                    ->label('Rollen toevoegen')
                    ->modalHeading('Rollen toevoegen aan deze permissie')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Select::make('roles')
                            ->label('Rollen')
                            ->options(function () {
                                /** @var Permission $permission */
                                $permission = $this->getOwnerRecord();
                                $existingRoleIds = $permission->roles()->pluck('id')->toArray();

                                return Role::whereNotIn('id', $existingRoleIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecteer rollen...'),
                    ])
                    ->action(function (array $data): void {
                        if (empty($data['roles'])) {
                            return;
                        }

                        /** @var Permission $permission */
                        $permission = $this->getOwnerRecord();
                        $newRoles = Role::whereIn('id', $data['roles'])->get();

                        foreach ($newRoles as $role) {
                            $permission->assignRole($role);
                        }

                        Notification::make()
                            ->title(count($data['roles']) > 1
                                ? 'Rollen toegevoegd'
                                : 'Rol toegevoegd')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Custom detach action using syncRoles
                Action::make('detachRole')
                    ->label('Ontkoppelen')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rol ontkoppelen')
                    ->modalDescription('Weet je zeker dat je deze rol wilt ontkoppelen van de permissie?')
                    ->action(function (Role $record): void {
                        /** @var Permission $permission */
                        $permission = $this->getOwnerRecord();

                        // Detach the role from the permission using removeRole
                        $permission->removeRole($record);

                        Notification::make()
                            ->title('Rol ontkoppeld')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
