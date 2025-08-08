<?php

namespace Database\Seeders;

use App\Models\Ngo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NgoStaff;
use Carbon\Carbon;

class NgoStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NgoStaff::create([
            'user_id' => 6, // Replace with an existing user ID from the users table
            'ngo_id' => 1,   // Assuming ngo_id 1 corresponds to ngo1
            'designation' => 'Founder',
            'privilege_role' => 'ngo_admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        NgoStaff::create([
            'user_id' => 7,
            'ngo_id' => 2,
            'designation' => 'Executive Director',
            'privilege_role' => 'ngo_admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        NgoStaff::create([
            'user_id' => 8,
            'ngo_id' => 3,
            'designation' => 'Medical Director',
            'privilege_role' => 'ngo_admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}