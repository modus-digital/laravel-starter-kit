<?php

declare(strict_types=1);

namespace App\Filament\CustomFields;

use App\Enums\RBAC\Role;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

final class RoleSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (self $component): void {
            /** @var Model|null $record */
            $record = $component->getRecord();
            $role = $record?->roles()->first();

            $component->state($role?->name);
        });

        /** @var Model $roleModel */
        $roleModel = config('permission.models.role');

        $this->options(
            options: static fn () => $roleModel::query()
                ->where('name', '!=', Role::SUPER_ADMIN->value)
                ->get()
                ->mapWithKeys(static function ($role) {
                    $enum = collect(Role::cases())
                        ->first(fn (Role $enum) => $enum->value === $role->name);

                    $label = $enum ? $enum->getLabel() : $role->name;

                    return [$role->name => $label];
                })
                ->all()
        );

        // Ensure the selected value displays the enum label instead of the raw key
        $this->getOptionLabelUsing(static function ($value): ?string {
            $enum = collect(Role::cases())
                ->first(fn (Role $enum) => $enum->value === $value);

            return $enum ? $enum->getLabel() : ($value ?? null);
        });

        $this->saveRelationshipsUsing(
            callback: static function (Select $component, Model $record, $state) {
                if ($state === null) {
                    $record->roles()->detach();

                    return;
                }

                $roleModel = config('permission.models.role');
                $selectedNames = is_array($state) ? $state : [$state];

                $roleIds = $roleModel::query()
                    ->whereIn('name', $selectedNames)
                    ->pluck('id')
                    ->all();

                $record->roles()->sync($roleIds);
            },
        );

        $this->native(false);
        $this->dehydrated(false);
    }
}
