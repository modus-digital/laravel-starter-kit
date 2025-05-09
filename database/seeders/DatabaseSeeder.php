<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Alex van Steenhoven',
            'email' => 'alex@modus-digital.com',
            'phone' => '+0621966941',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Thim van Amersfoort',
            'email' => 'thim@modus-digital.com',
            'phone' => null,
            'password' => 'password',
        ]);
    }
}
