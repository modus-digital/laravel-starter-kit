<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Str::random(16);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@modus-digital.com',
            'password' => Hash::make($password),
            'status' => ActivityStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole(Role::SUPER_ADMIN);

        $this->command->info("\n\n Super Admin created successfully with password: $password");
    }
}
