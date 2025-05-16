<?php

namespace App\Filament\Resources\RBAC\RoleResource\RelationManagers;

use App\Enums\RBAC\Permission as PermissionEnum;
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
use Override;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Relation manager for managing permissions associated with a role.
 */
class PermissionsRelationManager extends RelationManager
{
    /**
     * The name of the relationship.
     */
    protected static string $relationship = 'permissions';

    /**
     * The title for this relation manager.
     */
    protected static ?string $title = 'Permissies';

    /**
     * Label for the attachment button.
     */
    protected static ?string $inverseRelationshipName = 'rollen';

    /**
     * Defines the form for viewing a permission.
     */
    #[Override]
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

    /**
     * Defines the table for displaying permissions linked to a role.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                    ->sortable()
                    ->searchable(),
                IconColumn::make('linked_to_enum')
                    ->label('Gekoppeld aan enum')
                    ->boolean()
                    ->getStateUsing(fn (Permission $record): bool => collect(PermissionEnum::cases())->contains(fn ($case): bool => $case->value === $record->name))
                    ->tooltip('Geeft aan of deze permissie gekoppeld is aan een enum waarde'),
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
            ->headerActions([
                // Custom action to add permissions
                Action::make('addPermissions')
                    ->label('Permissies toevoegen')
                    ->modalHeading('Permissies toevoegen aan deze rol')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Select::make('permissions')
                            ->label('Permissies')
                            ->options(function () {
                                /** @var Role $role */
                                $role = $this->getOwnerRecord();
                                $existingPermissionIds = $role->permissions()->pluck('id')->toArray();

                                return Permission::whereNotIn('id', $existingPermissionIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecteer permissies...'),
                    ])
                    ->action(function (array $data): void {
                        if (empty($data['permissions'])) {
                            return;
                        }

                        /** @var Role $role */
                        $role = $this->getOwnerRecord();
                        $newPermissions = Permission::whereIn('id', $data['permissions'])->get();

                        foreach ($newPermissions as $permission) {
                            $role->givePermissionTo($permission);
                        }

                        Notification::make()
                            ->title(count($data['permissions']) > 1
                                ? 'Permissies toegevoegd'
                                : 'Permissie toegevoegd')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Custom detach action using syncPermissions
                Action::make('detachPermission')
                    ->label('Ontkoppelen')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permissie ontkoppelen')
                    ->modalDescription('Weet je zeker dat je deze permissie wilt ontkoppelen van de rol?')
                    ->action(function (Permission $record): void {
                        /** @var Role $role */
                        $role = $this->getOwnerRecord();

                        // Detach the permission from the role using revokePermissionTo
                        $role->revokePermissionTo($record);

                        Notification::make()
                            ->title('Permissie ontkoppeld')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
