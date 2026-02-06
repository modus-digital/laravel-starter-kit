<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Models\Scopes\ExcludeInternalRolesScope;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property bool $internal
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 *
 * @method static Builder|static withInternal()
 * @method static Builder|static onlyInternal()
 */
final class Role extends SpatieRole
{
    use HasFactory;

    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    /**
     * Override findByName to bypass global scope for Spatie's internal methods
     */
    public static function findByName(string $name, ?string $guardName = null): \Spatie\Permission\Contracts\Role
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        return self::withInternal()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->firstOrFail();
    }

    /**
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(related: Activity::class, name: 'subject');
    }

    /**
     * Check if this is an internal role (super_admin or admin)
     */
    public function isInternal(): bool
    {
        return $this->internal === true;
    }

    /**
     * Check if this is an external (custom) role
     */
    public function isExternal(): bool
    {
        return $this->internal === false;
    }

    /**
     * Scope a query to only include internal roles
     */
    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('internal', true);
    }

    /**
     * Scope a query to only include external roles
     */
    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('internal', false);
    }

    /**
     * Override syncPermissions to prevent assigning internal-only permissions to external roles
     *
     * @param  \Spatie\Permission\Contracts\Permission|array<\Spatie\Permission\Contracts\Permission|string>|string  ...$permissions
     */
    public function syncPermissions(...$permissions): static
    {
        if ($this->isExternal()) {
            $permissions = collect($permissions)
                ->flatten()
                ->map(fn ($permission) => is_string($permission) ? $permission : $permission->name)
                ->filter(function (string $permissionName) {
                    $permissionEnum = PermissionEnum::tryFrom($permissionName);

                    return $permissionEnum === null || ! $permissionEnum->isInternalOnly();
                })
                ->all();
        }

        return parent::syncPermissions(...$permissions);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ExcludeInternalRolesScope);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'internal' => 'boolean',
        ];
    }
}
