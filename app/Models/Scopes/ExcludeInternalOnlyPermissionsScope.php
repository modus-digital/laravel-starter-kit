<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\RBAC\Permission as PermissionEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class ExcludeInternalOnlyPermissionsScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Filters out internal-only permissions (access:*, manage:roles) by default.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $internalOnlyPermissions = collect(PermissionEnum::cases())
            ->filter(fn (PermissionEnum $permission) => $permission->isInternalOnly())
            ->map(fn (PermissionEnum $permission) => $permission->value)
            ->toArray();

        $builder->whereNotIn('name', $internalOnlyPermissions);
    }

    /**
     * Extend the query builder with helper methods.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withInternalPermissions', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyInternalPermissions', function (Builder $builder) {
            $internalOnlyPermissions = collect(PermissionEnum::cases())
                ->filter(fn (PermissionEnum $permission) => $permission->isInternalOnly())
                ->map(fn (PermissionEnum $permission) => $permission->value)
                ->toArray();

            return $builder->withoutGlobalScope($this)->whereIn('name', $internalOnlyPermissions);
        });
    }
}
