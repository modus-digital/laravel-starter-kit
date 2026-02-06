<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RBAC\Permission;
use App\Models\Modules\Tasks\Task;
use App\Models\User;

final class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewAnyTasks);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(Permission::ViewTasks);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreateTasks);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(Permission::UpdateTasks);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(Permission::DeleteTasks);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(Permission::RestoreTasks);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(Permission::ForceDeleteTasks);
    }
}
