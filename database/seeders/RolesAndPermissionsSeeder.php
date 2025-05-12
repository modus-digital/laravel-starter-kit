<?php

namespace Database\Seeders;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Enums\RBAC\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed all roles
        foreach (RoleEnum::cases() as $role) {
            Role::create([
                'name' => $role->value,
                'description' => $role->getDescription(),
            ]);
        }

        // Seed all permissions
        foreach (PermissionEnum::cases() as $permission) {
            Permission::create([
                'name' => $permission->value,
                'description' => $permission->getDescription(),
            ]);
        }

        #region Assign permissions to roles

        $superAdminRole = Role::findByName(RoleEnum::SUPER_ADMIN->value);
        $superAdminRole->givePermissionTo(Permission::all());

        $userRole = Role::findByName(RoleEnum::USER->value);
        $userRole->givePermissionTo([
            // Add default user permissions here
        ]);

        #endregion
    }
}
