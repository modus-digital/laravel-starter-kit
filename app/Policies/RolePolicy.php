<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RBAC\Permission;
use App\Models\Role;
use App\Models\User;

final class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewAnyRoles);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(Permission::ViewRoles);
    }

    /**
     * Determine whether the user can create models.
     * Only internal users can create roles.
     */
    public function create(User $user): bool
    {
        if (! $user->hasPermissionTo(Permission::CreateRoles)) {
            return false;
        }

        // Only users with internal roles can create roles
        return $user->roles()
            ->withInternal()
            ->where('internal', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     * Internal roles cannot be modified except by internal users.
     */
    public function update(User $user, Role $role): bool
    {
        if (! $user->hasPermissionTo(Permission::UpdateRoles)) {
            return false;
        }

        // Internal roles can only be modified by users with internal roles
        if ($role->isInternal()) {
            return $user->roles()
                ->withInternal()
                ->where('internal', true)
                ->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * Internal roles cannot be deleted.
     */
    public function delete(User $user, Role $role): bool
    {
        if (! $user->hasPermissionTo(Permission::DeleteRoles)) {
            return false;
        }

        // Internal roles cannot be deleted
        return ! $role->isInternal();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(Permission::RestoreRoles);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Internal roles cannot be force deleted.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        if (! $user->hasPermissionTo(Permission::ForceDeleteRoles)) {
            return false;
        }

        // Internal roles cannot be force deleted
        return ! $role->isInternal();
    }
}
