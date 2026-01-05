<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Tables;

use App\Enums\RBAC\Role as RBACRole;
use App\Models\Role;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label(__('admin.rbac.roles.table.label'))
                    ->getStateUsing(function (Role $record): string {
                        $enum = RBACRole::tryFrom($record->name);

                        return $enum?->getLabel() ?? ucwords(str_replace('_', ' ', $record->name));
                    })
                    ->icon(function (Role $record): ?string {
                        $enum = RBACRole::tryFrom($record->name);

                        return $enum?->getIcon() ?? ($record->icon ?? null);
                    })
                    ->color(function (Role $record): string {
                        $enum = RBACRole::tryFrom($record->name);

                        return $enum?->getFilamentColor() ?? ($record->color ?? 'info');
                    })
                    ->badge()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('name', 'like', "%{$search}%"))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('name', $direction)),

                TextColumn::make('name')
                    ->label(__('admin.rbac.roles.table.name'))
                    ->searchable()
                    ->sortable(),

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
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (Role $record): bool => ! self::isLinkedToEnum($record)),
                    DeleteAction::make()
                        ->visible(fn (Role $record): bool => ! self::isLinkedToEnum($record)),
                ]),
            ]);
    }

    private static function isLinkedToEnum(Role $role): bool
    {
        return collect(RBACRole::cases())
            ->contains(fn (RBACRole $enum): bool => $enum->value === $role->name);
    }
}
