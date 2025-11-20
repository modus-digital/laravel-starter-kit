<?php

declare(strict_types=1);

namespace App\Filament\Overrides;

use App\Enums\RBAC\Role;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

final class RoleSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        // After state hydrated, set the state to the first role of the record
        $this->afterStateHydrated(callback: static function (self $component): void {
            /** @var ?Model $record */
            $record = $component->getRecord();
            $role = $record?->roles->first();

            $component->state(state: $role?->name);
        });

        // Setting up the options for the select component to display the roles
        /** @var Model $roleModel */
        $roleModel = config(key: 'permission.models.role');
        $this->options(
            options: static fn (): array => $roleModel::query()
                ->where(column: 'name', operator: '!=', value: Role::SUPER_ADMIN->value)
                ->get()
                ->mapWithKeys(callback: static function ($role): array {
                    $enum = collect(value: Role::cases())
                        ->first(callback: static fn (Role $enum): bool => $enum->value === $role->name);

                    $label = $enum?->getLabel() ?? $role->name;

                    return [$role->name => $label];
                })
                ->all()
        );

        // Setting up the option label using the enum label
        $this->getOptionLabelUsing(
            callback: static function ($value): ?string {
                $enum = collect(value: Role::cases())
                    ->first(callback: static fn (Role $enum): bool => $enum->value === $value);

                return $enum?->getLabel() ?? $value;
            }
        );

        // Setting up the save relationships using the role ids
        $this->saveRelationshipsUsing(
            callback: static function (self $component, Model $record, mixed $state): void {
                if ($state === null) {
                    $record->roles()->detach();

                    return;
                }

                $roleModel = config(key: 'permission.models.role');
                $selectedNames = is_array(value: $state) ? $state : [$state];

                $roleIds = $roleModel::query()
                    ->whereIn('name', $selectedNames)
                    ->pluck('id')
                    ->all();

                $record->roles()->sync($roleIds);
            }
        );

        // Setting up the native and dehydrated conditions to false
        $this->native(condition: false);
        $this->dehydrated(condition: false);
    }
}
