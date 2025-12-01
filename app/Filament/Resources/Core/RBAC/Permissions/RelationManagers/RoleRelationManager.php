<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Permissions\RelationManagers;

use App\Enums\RBAC\Role as RBACRole;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.rbac.permissions.relation_managers.role.title');
    }

    public static function getInverseRelationship(): string
    {
        return __('admin.rbac.permissions.relation_managers.role.inverse_relationship');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.rbac.permissions.relation_managers.role.enum_key'))
                    ->badge()
                    ->state(fn (Role $role): ?string => $this->isLinkedToEnum($role) ? $role->name : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? RBACRole::from($state)->getLabel() : '-')
                    ->color(fn (Role $role): string => $this->isLinkedToEnum($role) ? RBACRole::from($role->name)->getFilamentColor() : 'gray'),

                TextColumn::make('name')
                    ->label(__('admin.rbac.permissions.relation_managers.role.name'))
                    ->searchable(),

                IconColumn::make('linked_to_enum')
                    ->label(__('admin.rbac.permissions.relation_managers.role.linked_to_enum.title'))
                    ->state(fn (Role $role): bool => $this->isLinkedToEnum($role))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.rbac.permissions.relation_managers.role.linked_to_enum.title'))
                    ->options([1 => __('admin.rbac.permissions.relation_managers.role.linked_to_enum.true'), 0 => __('admin.rbac.permissions.relation_managers.role.linked_to_enum.false')])
                    ->native(false)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($data): void {
                            $values = collect(RBACRole::cases())
                                ->map(fn (RBACRole $enum) => $enum->value)
                                ->toArray();

                            $data['value'] === 1
                                ? $query->whereIn('name', $values)
                                : $query->whereNotIn('name', $values);
                        });
                    }),
            ])
            ->headerActions([
                Action::make('addRoles')
                    ->label(__('admin.rbac.permissions.relation_managers.role.add_roles'))
                    ->modalHeading(__('admin.rbac.permissions.relation_managers.role.add_roles_modal_heading'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('primary')
                    ->schema([
                        Select::make('roles')
                            ->label(__('admin.rbac.permissions.relation_managers.role.roles'))
                            ->options(function () {
                                /** @var Permission $permission */
                                $permission = $this->getOwnerRecord();
                                $existingRoles = $permission->roles()->pluck('id')->toArray();

                                return Role::whereNotIn('id', $existingRoles)
                                    ->get()
                                    ->mapWithKeys(function ($role): array {
                                        $label = collect(RBACRole::cases())
                                            ->first(fn (RBACRole $enum): bool => $enum->value === $role->name)
                                            ?->getLabel() ?? $role->name;

                                        return [$role->name => $label];
                                    });
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(__('admin.rbac.permissions.relation_managers.role.roles_placeholder')),
                    ])
                    ->action(function (array $data): void {
                        if (empty($data['roles'])) {
                            return;
                        }

                        /** @var Permission $permission */
                        $permission = $this->getOwnerRecord();
                        $newRoles = Role::whereIn('name', $data['roles'])->get();

                        foreach ($newRoles as $role) {
                            $role->givePermissionTo($permission);
                        }

                        // Log activity for each role added
                        foreach ($newRoles as $role) {
                            ActivityFacade::inLog('administration')
                                ->event('rbac.role.permission.attached')
                                ->causedBy(Auth::user())
                                ->performedOn($role)
                                ->withProperties([
                                    'role' => [
                                        'id' => $role->id,
                                        'name' => $role->name,
                                    ],
                                    'permission' => [
                                        'id' => $permission->id,
                                        'name' => $permission->name,
                                    ],
                                ])
                                ->log('');
                        }

                        Notification::make()
                            ->title(__('admin.rbac.permissions.relation_managers.role.roles_added', ['count' => count($data['roles'])]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('detachRole')
                    ->label(__('admin.rbac.permissions.relation_managers.role.detach_role'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.rbac.permissions.relation_managers.role.detach_role_modal_heading'))
                    ->modalDescription(__('admin.rbac.permissions.relation_managers.role.detach_role_modal_description'))
                    ->action(function (Role $record): void {
                        /** @var Permission $permission */
                        $permission = $this->getOwnerRecord();

                        // Detach the role from the permission
                        $record->revokePermissionTo($permission);

                        // Log activity for role detached
                        ActivityFacade::inLog('administration')
                            ->event('rbac.role.permission.detached')
                            ->causedBy(Auth::user())
                            ->performedOn($record)
                            ->withProperties([
                                'role' => [
                                    'id' => $record->id,
                                    'name' => $record->name,
                                ],
                                'permission' => [
                                    'id' => $permission->id,
                                    'name' => $permission->name,
                                ],
                            ])
                            ->log('');

                        Notification::make()
                            ->title(__('admin.rbac.permissions.relation_managers.role.role_detached', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }

    private function isLinkedToEnum(Role $role): bool
    {
        return collect(RBACRole::cases())
            ->contains(fn (RBACRole $enum): bool => $enum->value === $role->name);
    }
}
