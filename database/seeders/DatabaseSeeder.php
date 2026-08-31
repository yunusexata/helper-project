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
        // Seed roles, permissions, and daily routines

        // Create default administrator user and assign Super Admin role

        $this->call([
            RoleAndPermissionSeeder::class,
            HelperJobdeskRoutineSeeder::class,
            HelperJobdeskHistorySeeder::class,
            // HelperJobdeskDummyHistorySeeder::class,
        ]);
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123exata'),
        ]);

        $admin->assignRole('Super Admin');
    }
}
