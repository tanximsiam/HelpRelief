<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DisasterCampaignAssignment;
use App\Models\Task;
use App\Models\VolunteerTaskLog;

class DisasterCampaignAssignmentSeeder extends Seeder
{
    public function run()
    {
    // Ensure a clean slate (delete dependent tasks first to satisfy FKs)
    VolunteerTaskLog::query()->delete();
    Task::query()->delete();
    DisasterCampaignAssignment::query()->delete();

        // One unique campaign per disaster (assign different NGOs if available)
        $rows = [
            ['disaster_id' => 1, 'ngo_id' => 1, 'help_needed' => 'high'],
            ['disaster_id' => 2, 'ngo_id' => 2, 'help_needed' => 'medium'],
            ['disaster_id' => 3, 'ngo_id' => 3, 'help_needed' => 'medium'],
        ];

        foreach ($rows as $r) {
            DisasterCampaignAssignment::create([
                'disaster_id' => $r['disaster_id'],
                'ngo_id' => $r['ngo_id'],
                'assigned_by' => 1,
                'status' => 'active',
                'help_needed' => $r['help_needed'],
                'updated_by' => 1,
            ]);
        }
    }
}
