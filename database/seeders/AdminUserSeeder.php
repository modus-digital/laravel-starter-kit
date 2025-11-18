<?php

namespace Database\Seeders;

use App\Enums\RBAC\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Modus Admin',
            'email' => 'admin@modus-digital.com',
            'password' => Hash::make('W8chtW00rd01!'),
        ]);

        $user->assignRole(Role::SUPER_ADMIN);
    }
}
