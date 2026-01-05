<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Permissions\Tables;

use App\Enums\RBAC\Permission as RBACPermission;
use App\Models\Permission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;

final class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->columns([
                TextColumn::make('enum_key')
                    ->label(__('admin.rbac.permissions.table.enum_key'))
                    ->badge()
                    ->state(fn (Permission $record): ?string => self::isLinkedToEnum($record) ? $record->name : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? RBACPermission::from($state)->getLabel() : '-')
                    ->color(fn (Permission $record): string => self::isLinkedToEnum($record) ? RBACPermission::from($record->name)->getFilamentColor() : 'gray'),

                TextColumn::make('name')
                    ->label(__('admin.rbac.permissions.table.name'))
                    ->searchable()
                    ->sortable(),

                IconColumn::make('linked_to_enum')
                    ->label(__('admin.rbac.permissions.table.linked_to_enum.title'))
                    ->state(fn (Permission $record): bool => self::isLinkedToEnum($record))
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
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn (Permission $record): bool => ! self::isLinkedToEnum($record)),
            ])
            ->headerActions([
                Action::make('sync_permissions')
                    ->label(__('admin.rbac.permissions.table.sync_permissions.title'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (): void {
                        $beforeCount = Permission::count();
                        Artisan::call('permissions:sync');
                        $afterCount = Permission::count();
                        $syncedCount = $afterCount - $beforeCount;

                        Notification::make()
                            ->title(__('admin.rbac.permissions.table.sync_permissions.success.title'))
                            ->body(__('admin.rbac.permissions.table.sync_permissions.success.body', ['count' => $syncedCount]))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.rbac.permissions.table.sync_permissions.title'))
                    ->modalDescription(__('admin.rbac.permissions.table.sync_permissions.modal_description'))
                    ->modalSubmitActionLabel(__('common.actions.confirm')),
            ]);
    }

    private static function isLinkedToEnum(Permission $permission): bool
    {
        return collect(RBACPermission::cases())
            ->contains(fn (RBACPermission $enum): bool => $enum->value === $permission->name);
    }
}
