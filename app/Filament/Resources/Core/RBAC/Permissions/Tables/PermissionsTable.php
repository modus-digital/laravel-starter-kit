<?php

namespace App\Filament\Resources\Core\RBAC\Permissions\Tables;

use App\Enums\RBAC\Permission as RBACPermission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.rbac.permissions.table.enum_key'))
                    ->badge()
                    ->state(fn (Permission $permission): ?string => self::isLinkedToEnum($permission) ? $permission->name : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? RBACPermission::from($state)->getLabel() : '-')
                    ->color(fn (Permission $permission): string => self::isLinkedToEnum($permission) ? RBACPermission::from($permission->name)->getFilamentColor() : 'gray'),

                TextColumn::make('name')
                    ->label(__('admin.rbac.permissions.table.name'))
                    ->searchable()
                    ->sortable(),

                IconColumn::make('linked_to_enum')
                    ->label(__('admin.rbac.permissions.table.linked_to_enum.title'))
                    ->state(fn (Permission $permission): bool => self::isLinkedToEnum($permission))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(__('admin.rbac.permissions.table.linked_to_enum.tooltip')),

                TextColumn::make('roles_count')
                    ->label(__('admin.rbac.permissions.table.roles_count'))
                    ->counts('roles')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.rbac.permissions.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.rbac.permissions.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.rbac.permissions.table.linked_to_enum.title'))
                    ->options([1 => __('admin.rbac.permissions.table.linked_to_enum.true'), 0 => __('admin.rbac.permissions.table.linked_to_enum.false')])
                    ->native(false)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($data) {
                            $values = collect(RBACPermission::cases())
                                ->map(fn (RBACPermission $enum) => $enum->value)
                                ->toArray();

                            $data['value'] === 1
                                ? $query->whereIn('name', $values)
                                : $query->whereNotIn('name', $values);
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn (Permission $record): bool => ! self::isLinkedToEnum($record)),
            ])
            ->toolbarActions([
                Action::make('sync_permissions')
                    ->label(__('admin.rbac.permissions.table.sync_permissions.title'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('primary')
                    ->action(function (): void {
                        $count = 0;

                        foreach (RBACPermission::cases() as $permission) {
                            Permission::updateOrCreate([
                                'name' => $permission->value,
                            ]);

                            $count++;
                        }

                        Notification::make()
                            ->title(__('admin.rbac.permissions.table.sync_permissions.success.title'))
                            ->body(__('admin.rbac.permissions.table.sync_permissions.success.body', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private static function isLinkedToEnum(Permission $permission): bool
    {
        return collect(RBACPermission::cases())
            ->contains(fn (RBACPermission $enum): bool => $enum->value === $permission->name);
    }
}
