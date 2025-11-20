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
        foreach (RoleEnum::cases() as $role) {
            Role::create([
                'name' => $role->value,
                'guard_name' => 'web',
            ]);
        }

        foreach (PermissionEnum::cases() as $permission) {
            Permission::create([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }

        $superAdminRole = Role::where('name', RoleEnum::SUPER_ADMIN->value)->first();
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::where('name', RoleEnum::ADMIN->value)->first();
        $adminRole->givePermissionTo([
            PermissionEnum::ACCESS_CONTROL_PANEL->value,
            PermissionEnum::IMPERSONATE_USERS->value,
            PermissionEnum::ACCESS_ACTIVITY_LOGS->value,
            PermissionEnum::MANAGE_SETTINGS->value,
        ]);

        $userRole = Role::where('name', RoleEnum::USER->value)->first();
        $userRole->givePermissionTo([]);
    }
}
