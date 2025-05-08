<?php

namespace Database\Seeders;

use App\Enums\RBAC\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var \App\Models\User $superAdmin */
        $superAdmin = User::create([
            'name' => 'Modus Admin',
            'email' => 'admin@modus-digital.com',
            'password' => Hash::make('password'),
        ]);

        $superAdmin->assignRole(Role::SUPER_ADMIN);
    }
}
