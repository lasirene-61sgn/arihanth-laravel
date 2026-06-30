<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProcessOwnersRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all existing process owners to have the 'process_owner' role
        DB::table('process_owners')
            ->whereNull('role')
            ->update(['role' => 'process_owner']);
    }
}