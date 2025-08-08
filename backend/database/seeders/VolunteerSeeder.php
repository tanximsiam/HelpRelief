<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Volunteer_registrations;

class VolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Volunteer_registrations::create([
            'user_id' => 1, 
            'disaster_id' => 1, 
            'ngo_id' => 1, 
            'status' => 'approved',
            'registered_at' => now(),
            'availability' => 'full-time',
            'skills' => 'First aid, logistics coordination',
            'notes' => 'Ready to assist with disaster relief.',
        ]);

        Volunteer_registrations::create([
            'user_id' => 3, 
            'disaster_id' => 2, 
            'ngo_id' => 2, 
            'status' => 'approved',
            'registered_at' => now(),
            'availability' => 'part-time',
            'skills' => 'Medical treatment, counseling',
            'notes' => 'Experienced in handling disaster situations.',
        ]);

    }
}
