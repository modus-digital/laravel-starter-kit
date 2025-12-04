<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Enums\RBAC\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sync permissions from enum
        foreach (PermissionEnum::cases() as $permission) {
            if (! $permission->shouldSync()) {
                continue;
            }

            Permission::firstOrCreate(
                ['name' => $permission->value, 'guard_name' => 'web']
            );
        }

        // Sync system roles from enum
        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate(
                ['name' => $role->value, 'guard_name' => 'web']
            );
        }

        // Assign permissions to Super Admin (all permissions)
        $superAdminRole = Role::where('name', RoleEnum::SUPER_ADMIN->value)->first();
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to Admin
        $adminRole = Role::where('name', RoleEnum::ADMIN->value)->first();
        $adminRole->syncPermissions([
            PermissionEnum::ACCESS_CONTROL_PANEL->value,
            PermissionEnum::IMPERSONATE_USERS->value,
            PermissionEnum::MANAGE_SETTINGS->value,
            PermissionEnum::CREATE_USERS->value,
            PermissionEnum::READ_USERS->value,
            PermissionEnum::UPDATE_USERS->value,
            PermissionEnum::DELETE_USERS->value,
            PermissionEnum::RESTORE_USERS->value,
            PermissionEnum::READ_ROLES->value,
        ]);

        // Check if API module is enabled and add API permissions to Admin
        if (config('modules.api.enabled', false)) {
            $adminRole->givePermissionTo([
                PermissionEnum::HAS_API_ACCESS->value,
                PermissionEnum::CREATE_API_TOKENS->value,
                PermissionEnum::READ_API_TOKENS->value,
                PermissionEnum::UPDATE_API_TOKENS->value,
                PermissionEnum::DELETE_API_TOKENS->value,
            ]);
        }

        // Assign minimal permissions to User role
        $userRole = Role::where('name', RoleEnum::USER->value)->first();
        $userPermissions = [];

        if (config('modules.api.enabled', false)) {
            $userPermissions[] = PermissionEnum::HAS_API_ACCESS->value;
        }

        $userRole->syncPermissions($userPermissions);
    }
}
