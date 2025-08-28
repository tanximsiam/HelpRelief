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
        // Existing seeded data - keeping original
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
            'user_id' => 3, // general2
            'campaign_id' => 2,
            'ngo_id' => 2,
            'status' => 'approved',
            'registered_at' => now(),
            'availability' => true,
            'skills' => 'Medical treatment, counseling',
            'notes' => 'Experienced in handling disaster situations.',
        ]);

        // Additional volunteers for NGO 1 - using only general users

        // For Disaster 1 (Cyclone Remal in Chittagong) - NGO 1
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

        VolunteerRegistration::create([
            'user_id' => 11, // volunteer3
            'campaign_id' => 1,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Emergency response, crowd control',
            'notes' => 'Experienced in disaster management.',
        ]);

        // For Disaster 2 (Flood in Sylhet) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 12, // volunteer4
            'campaign_id' => 2,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Water rescue, boat operation',
            'notes' => 'Experienced in flood relief operations.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 13, // volunteer5
            'campaign_id' => 2,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subHours(12),
            'availability' => true,
            'skills' => 'Food distribution, logistics',
            'notes' => 'Waiting for approval to join flood relief.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 14, // volunteer6
            'campaign_id' => 2,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(5),
            'availability' => false,
            'skills' => 'Medical aid, evacuation assistance',
            'notes' => 'Successfully completed flood relief assignment.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 1, // general1 (cross-registering for multiple disasters)
            'campaign_id' => 2,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(2),
            'availability' => true,
            'skills' => 'Leadership, coordination',
            'notes' => 'Experienced volunteer helping with flood relief.',
        ]);

        // For Disaster 3 (Earthquake in Dhaka) - NGO 1
        VolunteerRegistration::create([
            'user_id' => 15, // volunteer7
            'campaign_id' => 3,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(1),
            'availability' => true,
            'skills' => 'Structural assessment, debris removal',
            'notes' => 'Ready for earthquake response activities.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 16, // volunteer8
            'campaign_id' => 3,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subHours(18),
            'availability' => true,
            'skills' => 'Psychological support, crowd management',
            'notes' => 'Available for post-earthquake trauma support.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 9, // volunteer1 (cross-registering)
            'campaign_id' => 3,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(2),
            'availability' => false,
            'skills' => 'Basic assistance',
            'notes' => 'Already committed to cyclone relief, cannot take on earthquake response.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 3, // general2 (cross-registering from NGO 2 to NGO 1)
            'campaign_id' => 3,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subDays(4),
            'availability' => false,
            'skills' => 'Emergency coordination, resource management',
            'notes' => 'Successfully completed initial earthquake response phase.',
        ]);

        VolunteerRegistration::create([
            'user_id' => 11, // volunteer3 (cross-registering)
            'campaign_id' => 3,
            'ngo_id' => 1,
            'status' => 'approved',
            'registered_at' => now()->subHours(6),
            'availability' => true,
            'skills' => 'Emergency response, medical assistance',
            'notes' => 'Requesting to help with earthquake relief as well.',
        ]);
    }
}
