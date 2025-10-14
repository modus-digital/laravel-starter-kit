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

        $this->relationship(name: 'roles', titleAttribute: 'name');

        $this->afterStateHydrated(static function (self $component): void {
            $relationship = $component->getRelationship();
            $role = $relationship->first();

            $component->state($role?->name);
        });

        /** @var Model $roleModel */
        $roleModel = config('permission.models.role');

        $this->options(
            options: static fn () => $roleModel::query()
                ->where('name', '!=', Role::SUPER_ADMIN->value)
                ->get()
                ->mapWithKeys(static function ($role) {
                    $label = collect(Role::cases())
                        ->first(fn (Role $enum) => $enum->value === $role->name)
                        ?->getLabel() ?? $role->name;

                    return [$role->name => $label];
                })
                ->all()
        );

        $this->saveRelationshipsUsing(
            callback: static function (Select $component, Model $record, $state) {
                if ($state === null) {
                    $component->getRelationship()->detach();

                    return;
                }

                $roleModel = config('permission.models.role');
                $role = $roleModel::where('name', $state)->first();

                if ($role) {
                    $component->getRelationship()->sync([$role->id]);
                }
            },
        );

        $this->native(false);
        $this->dehydrated(false);
    }
}
