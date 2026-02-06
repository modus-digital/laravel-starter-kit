<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync all permissions from the Permission enum
        foreach (PermissionEnum::cases() as $permission) {
            if (! $permission->shouldSync()) {
                continue;
            }

            Permission::firstOrCreate(
                ['name' => $permission->value, 'guard_name' => 'web']
            );
        }

        // Set internal flags for super_admin and admin roles (bypass global scope)
        Role::withInternal()->where('name', Role::SUPER_ADMIN)->update(['internal' => true]);
        Role::withInternal()->where('name', Role::ADMIN)->update(['internal' => true]);

        // Ensure all other roles are marked as external
        Role::withInternal()->whereNotIn('name', [Role::SUPER_ADMIN, Role::ADMIN])
            ->update(['internal' => false]);

        // Update super admin to have all permissions (bypass global scopes)
        $superAdminRole = Role::withInternal()->where('name', Role::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
        }

        // Update admin to have all permissions except manage:roles
        $adminRole = Role::withInternal()->where('name', Role::ADMIN)->first();
        if ($adminRole) {
            $adminPermissions = collect(PermissionEnum::cases())
                ->filter(function (PermissionEnum $permission) {
                    // Admin gets access control panel and impersonate users but not manage roles
                    if ($permission === PermissionEnum::AccessControlPanel || $permission === PermissionEnum::ImpersonateUsers) {
                        return true;
                    }

                    // Admin does not get internal-only permissions
                    if ($permission->isInternalOnly()) {
                        return false;
                    }

                    return true;
                })
                ->map(fn (PermissionEnum $permission) => $permission->value)
                ->all();

            $adminRole->syncPermissions($adminPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new permissions that were added
        $newPermissions = [
            PermissionEnum::ManageRoles->value,
            PermissionEnum::ViewUsers->value,
            PermissionEnum::ViewAnyUsers->value,
            PermissionEnum::RestoreUsers->value,
            PermissionEnum::ForceDeleteUsers->value,
            PermissionEnum::ViewRoles->value,
            PermissionEnum::ViewAnyRoles->value,
            PermissionEnum::RestoreRoles->value,
            PermissionEnum::ForceDeleteRoles->value,
            PermissionEnum::ViewClients->value,
            PermissionEnum::ViewAnyClients->value,
            PermissionEnum::RestoreClients->value,
            PermissionEnum::ForceDeleteClients->value,
            PermissionEnum::CreateTasks->value,
            PermissionEnum::ViewTasks->value,
            PermissionEnum::UpdateTasks->value,
            PermissionEnum::DeleteTasks->value,
            PermissionEnum::ViewAnyTasks->value,
            PermissionEnum::RestoreTasks->value,
            PermissionEnum::ForceDeleteTasks->value,
        ];

        Permission::whereIn('name', $newPermissions)->delete();

        // Reset internal flags (optional - comment out if you want to keep them)
        // Role::whereIn('name', [Role::SUPER_ADMIN, Role::ADMIN])->update(['internal' => false]);
    }
};
