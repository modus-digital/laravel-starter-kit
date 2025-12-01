<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\RelationManagers;

use App\Enums\RBAC\Permission as RBACPermission;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

final class PermissionRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.rbac.roles.relation_managers.permission.title');
    }

    public static function getInverseRelationship(): string
    {
        return __('admin.rbac.roles.relation_managers.permission.inverse_relationship');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.rbac.roles.relation_managers.permission.name'))
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.rbac.roles.relation_managers.permission.enum_key'))
                    ->badge()
                    ->state(fn (Permission $permission): ?string => $this->isLinkedToEnum($permission) ? $permission->name : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? RBACPermission::from($state)->getLabel() : '-')
                    ->color(fn (Permission $permission): string => $this->isLinkedToEnum($permission) ? RBACPermission::from($permission->name)->getFilamentColor() : 'gray'),

                TextColumn::make('name')
                    ->label(__('admin.rbac.roles.relation_managers.permission.name'))
                    ->searchable(),

                IconColumn::make('linked_to_enum')
                    ->label(__('admin.rbac.roles.relation_managers.permission.linked_to_enum.title'))
                    ->state(fn (Permission $permission): bool => $this->isLinkedToEnum($permission))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.rbac.roles.relation_managers.permission.linked_to_enum.title'))
                    ->options([1 => __('admin.rbac.roles.relation_managers.permission.linked_to_enum.true'), 0 => __('admin.rbac.roles.relation_managers.permission.linked_to_enum.false')])
                    ->native(false)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($data): void {
                            $values = collect(RBACPermission::cases())
                                ->map(fn (RBACPermission $enum) => $enum->value)
                                ->toArray();

                            $data['value'] === 1
                                ? $query->whereIn('name', $values)
                                : $query->whereNotIn('name', $values);
                        });
                    }),
            ])
            ->headerActions([
                Action::make('addPermissions')
                    ->label(__('admin.rbac.roles.relation_managers.permission.add_permissions'))
                    ->modalHeading(__('admin.rbac.roles.relation_managers.permission.add_permissions_modal_heading'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('primary')
                    ->schema([
                        Select::make('permissions')
                            ->label(__('admin.rbac.roles.relation_managers.permission.permissions'))
                            ->options(function () {
                                /** @var Role $role */
                                $role = $this->getOwnerRecord();
                                $existingPermissions = $role->permissions()->pluck('id')->toArray();

                                return Permission::whereNotIn('id', $existingPermissions)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function (Permission $permission): array {
                                        $label = $this->isLinkedToEnum($permission)
                                            ? RBACPermission::from($permission->name)->getLabel()
                                            : $permission->name;

                                        return [$permission->name => $label];
                                    });
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(__('admin.rbac.roles.relation_managers.permission.permissions_placeholder')),
                    ])
                    ->action(function (array $data): void {
                        if (empty($data['permissions'])) {
                            return;
                        }

                        /** @var Role $role */
                        $role = $this->getOwnerRecord();
                        $newPermissions = Permission::whereIn('name', $data['permissions'])->get();

                        foreach ($newPermissions as $permission) {
                            $role->givePermissionTo($permission);
                        }

                        // Log activity for each permission added
                        foreach ($newPermissions as $permission) {
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
                            ->title(__('admin.rbac.roles.relation_managers.permission.permissions_added', ['count' => count($data['permissions'])]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('detachPermission')
                    ->label(__('admin.rbac.roles.relation_managers.permission.detach_permission'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.rbac.roles.relation_managers.permission.detach_permission_modal_heading'))
                    ->modalDescription(__('admin.rbac.roles.relation_managers.permission.detach_permission_modal_description'))
                    ->action(function (Permission $record): void {
                        /** @var Role $role */
                        $role = $this->getOwnerRecord();

                        // Detach the permission from the role using revokePermissionTo
                        $role->revokePermissionTo($record);

                        // Log activity for permission detached
                        ActivityFacade::inLog('administration')
                            ->event('rbac.role.permission.detached')
                            ->causedBy(Auth::user())
                            ->performedOn($role)
                            ->withProperties([
                                'role' => [
                                    'id' => $role->id,
                                    'name' => $role->name,
                                ],
                                'permission' => [
                                    'id' => $record->id,
                                    'name' => $record->name,
                                ],
                            ])
                            ->log('');

                        Notification::make()
                            ->title(__('admin.rbac.roles.relation_managers.permission.permission_detached', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }

    private function isLinkedToEnum(Permission $permission): bool
    {
        return collect(RBACPermission::cases())
            ->contains(fn (RBACPermission $enum): bool => $enum->value === $permission->name);
    }
}
