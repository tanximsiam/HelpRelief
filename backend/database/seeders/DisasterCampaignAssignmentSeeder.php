<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DisasterCampaignAssignment;

class DisasterCampaignAssignmentSeeder extends Seeder
{
    public function run()
    {
        DisasterCampaignAssignment::create([
            'disaster_id' => 1,
            'ngo_id' => 6,
            'assigned_by' => 1,
            'status' => 'active',
            'help_needed' => 'high',
            'updated_by' => 1,
        ]);

        DisasterCampaignAssignment::create([
            'disaster_id' => 2,
            'ngo_id' => 6,
            'assigned_by' => 1,
            'status' => 'pending',
            'help_needed' => 'medium',
            'updated_by' => 1,
        ]);

        DisasterCampaignAssignment::create([
            'disaster_id' => 3,
            'ngo_id' => 7,
            'assigned_by' => 1,
            'status' => 'inactive',
            'help_needed' => 'low',
            'updated_by' => 1,
        ]);
    }
}
