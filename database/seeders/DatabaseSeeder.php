<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'Customer',
        ]);

        User::firstOrCreate(
            ['email' => 'admin@buhatilaundry.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@buhatilaundry.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password123'),
                'role' => 'Staff',
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@buhatilaundry.com'],
            [
                'name' => 'Sample Customer',
                'password' => Hash::make('password123'),
                'role' => 'Customer',
            ]
        );
    }
}