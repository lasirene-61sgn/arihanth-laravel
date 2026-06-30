<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProcessOwner;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProcessOwner::create([
            'full_name' => 'Super Admin',
            'email_id' => 'superadmin@example.com',
            'mobile_no' => '1234567890',
            'password' => bcrypt('password'),
            'user_code' => 'SA001',
            'role' => 'super_admin'
        ]);
    }
}