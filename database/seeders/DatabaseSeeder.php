<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@2314'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Owner User',
            'email' => 'owner@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'status' => 'active',
        ]);
    }

}
