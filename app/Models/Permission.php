<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Models\Scopes\ExcludeInternalRolesScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 *
 * @method static Builder|static excludeInternalOnly()
 * @method static Builder|static onlyInternal()
 */
final class Permission extends SpatiePermission
{
    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            $registrar->pivotPermission,
            $registrar->pivotRole
        )->withoutGlobalScope(ExcludeInternalRolesScope::class);
    }

    /**
     * Scope to exclude internal-only permissions
     */
    public function scopeExcludeInternalOnly(Builder $query): Builder
    {
        $internalOnlyPermissions = collect(PermissionEnum::cases())
            ->filter(fn (PermissionEnum $permission): bool => $permission->isInternalOnly())
            ->map(fn (PermissionEnum $permission) => $permission->value)
            ->toArray();

        return $query->whereNotIn('name', $internalOnlyPermissions);
    }

    /**
     * Scope to get only internal-only permissions
     */
    public function scopeOnlyInternal(Builder $query): Builder
    {
        $internalOnlyPermissions = collect(PermissionEnum::cases())
            ->filter(fn (PermissionEnum $permission): bool => $permission->isInternalOnly())
            ->map(fn (PermissionEnum $permission) => $permission->value)
            ->toArray();

        return $query->whereIn('name', $internalOnlyPermissions);
    }

    /**
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(related: Activity::class, name: 'subject');
    }
}
