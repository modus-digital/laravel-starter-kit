<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class ExcludeInternalRolesScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Filters out internal roles (super_admin, admin) by default.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('internal', false);
    }

    /**
     * Extend the query builder with helper methods.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withInternal', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyInternal', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where('internal', true);
        });
    }
}
