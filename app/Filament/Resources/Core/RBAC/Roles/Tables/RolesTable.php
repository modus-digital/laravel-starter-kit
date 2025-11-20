<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Tables;

use App\Enums\RBAC\Role as RBACRole;
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
use Spatie\Permission\Models\Role;

final class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.rbac.roles.table.enum_key'))
                    ->badge()
                    ->state(fn (Role $role): ?string => self::isLinkedToEnum($role) ? $role->name : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? RBACRole::from($state)->getLabel() : '-')
                    ->color(fn (Role $role): string => self::isLinkedToEnum($role) ? RBACRole::from($role->name)->getFilamentColor() : 'gray'),

                TextColumn::make('name')
                    ->label(__('admin.rbac.roles.table.name'))
                    ->searchable()
                    ->sortable(),

                IconColumn::make('linked_to_enum')
                    ->label(__('admin.rbac.roles.table.linked_to_enum.title'))
                    ->state(fn (Role $role): bool => self::isLinkedToEnum($role))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(__('admin.rbac.roles.table.linked_to_enum.tooltip')),

                TextColumn::make('permissions_count')
                    ->label(__('admin.rbac.roles.table.permissions_count'))
                    ->counts('permissions')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.rbac.roles.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.rbac.roles.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('linked_to_enum')
                    ->label(__('admin.rbac.roles.table.linked_to_enum.title'))
                    ->options([1 => __('admin.rbac.roles.table.linked_to_enum.true'), 0 => __('admin.rbac.roles.table.linked_to_enum.false')])
                    ->native(false)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($data) {
                            $values = collect(RBACRole::cases())
                                ->map(fn (RBACRole $enum) => $enum->value)
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
                    ->visible(fn (Role $record): bool => ! self::isLinkedToEnum($record)),
            ])
            ->toolbarActions([
                Action::make('sync_roles')
                    ->label(__('admin.rbac.roles.table.sync_roles.title'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('primary')
                    ->action(function (): void {
                        $count = 0;

                        foreach (RBACRole::cases() as $role) {
                            Role::updateOrCreate([
                                'name' => $role->value,
                            ]);

                            $count++;
                        }

                        Notification::make()
                            ->title(__('admin.rbac.roles.table.sync_roles.success.title'))
                            ->body(__('admin.rbac.roles.table.sync_roles.success.body', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private static function isLinkedToEnum(Role $role): bool
    {
        return collect(RBACRole::cases())
            ->contains(fn (RBACRole $enum): bool => $enum->value === $role->name);
    }
}
