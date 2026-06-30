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
            'user_code' => 'UR0001',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'full_name' => 'Test User',
            'email_id' => 'test@example.com',
            'mobile_no' => '9876543210',
            'password' => bcrypt('password'),
        ]);
        
        $this->call([
            BuyerSeeder::class,
            KeyUserSeeder::class,
            SuperAdminSeeder::class,
            TestDesignSeeder::class,
            UpdateProcessOwnersRoleSeeder::class,
        ]);
    }
}