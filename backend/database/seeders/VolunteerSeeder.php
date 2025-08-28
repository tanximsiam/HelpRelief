<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VolunteerRegistration;

class VolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Each volunteer can only be registered to ONE campaign
        
        // For Campaign 1 (Cyclone Remal - High Priority) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 1, // general1
            'campaign_id' => 1,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now(),
            'availability' => true,
            'skills' => 'First aid, logistics coordination',
            'notes' => 'Ready to assist with disaster relief.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 9, // volunteer1
            'campaign_id' => 1,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(2),
            'availability' => true,
            'skills' => 'Search and rescue, emergency communication',
            'notes' => 'Available for cyclone relief operations.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 10, // volunteer2
            'campaign_id' => 1,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(3),
            'availability' => false,
            'skills' => 'Basic first aid',
            'notes' => 'Not available during required timeframe.',
        ]);

        // For Campaign 2 (Cyclone Remal - Medium Priority) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 11, // volunteer3
            'campaign_id' => 2,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Emergency response, crowd control',
            'notes' => 'Experienced in disaster management.',
        ]);

        // For Campaign 4 (Flood in Sylhet - High Priority) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 12, // volunteer4
            'campaign_id' => 4,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Water rescue, boat operation',
            'notes' => 'Experienced in flood relief operations.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 13, // volunteer5
            'campaign_id' => 4,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subHours(12),
            'availability' => true,
            'skills' => 'Food distribution, logistics',
            'notes' => 'Waiting for approval to join flood relief.',
        ]);

        // For Campaign 5 (Flood in Sylhet - Medium Priority) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 14, // volunteer6
            'campaign_id' => 5,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(5),
            'availability' => false,
            'skills' => 'Medical aid, evacuation assistance',
            'notes' => 'Successfully completed flood relief assignment.',
        ]);

        // For Campaign 6 (Flood in Sylhet) - NGO 2
        VolunteerRegistration::create([
            'user_id' => 3, // general2
            'campaign_id' => 6,
            'ngo_id' => 2,
            'status' => 'approved',
            'registered_at' => now(),
            'availability' => true,
            'skills' => 'Medical treatment, counseling',
            'notes' => 'Experienced in handling disaster situations.',
        ]);

        // For Campaign 7 (Earthquake in Dhaka - High Priority) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 15, // volunteer7
            'campaign_id' => 7,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Structural assessment, debris removal',
            'notes' => 'Ready for earthquake response activities.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 16, // volunteer8
            'campaign_id' => 7,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subHours(18),
            'availability' => true,
            'skills' => 'Psychological support, crowd management',
            'notes' => 'Available for post-earthquake trauma support.',
        ]);

        // For Campaign 8 (Earthquake in Dhaka - Medium Priority) - NGO 1
        // No volunteers assigned yet - available for new registrations

        // For Campaign 9 (Earthquake in Dhaka - Low Priority) - NGO 1
        // No volunteers assigned yet - available for new registrations
    }
}
