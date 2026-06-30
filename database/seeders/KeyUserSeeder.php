<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\KeyUser;

class KeyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KeyUser::create([
            'user_code' => KeyUser::generateUserCode(),
            'bp_code' => 'BP0001',
            'full_name' => 'Test Key User',
            'email_id' => 'keyuser@example.com',
            'mobile_no' => '9876543210',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
    }
}