<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Buyer;

class BuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create buyers if they don't already exist
        if (!Buyer::where('bp_code', 'BA001')->exists()) {
            Buyer::create([
                'bp_code' => 'BA001',
                'business_name' => 'ABC Textiles Pvt Ltd',
                'name' => 'Rajesh Kumar',
                'mobile' => '9876543210',
                'email' => 'rajesh@abctextiles.com',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'aadhar_no' => '123456789012',
            ]);
        }
        
        if (!Buyer::where('bp_code', 'BA002')->exists()) {
            Buyer::create([
                'bp_code' => 'BA002',
                'business_name' => 'XYZ Fashion House',
                'name' => 'Priya Sharma',
                'mobile' => '9876543211',
                'email' => 'priya@xyzfashion.com',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'aadhar_no' => '123456789013',
            ]);
        }
        
        if (!Buyer::where('bp_code', 'BA003')->exists()) {
            Buyer::create([
                'bp_code' => 'BA003',
                'business_name' => 'PQR Garments Co',
                'name' => 'Amit Patel',
                'mobile' => '9876543212',
                'email' => 'amit@pqrgarments.com',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'aadhar_no' => '123456789014',
            ]);
        }
    }
}