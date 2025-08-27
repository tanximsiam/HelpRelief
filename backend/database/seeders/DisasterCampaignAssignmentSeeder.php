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
            ['disaster_id' => 1, 'ngo_id' => 1, 'help_needed' => 'high', 'status' => 'active'],
            ['disaster_id' => 2, 'ngo_id' => 1, 'help_needed' => 'medium', 'status' => 'active'],
            ['disaster_id' => 3, 'ngo_id' => 1, 'help_needed' => 'medium', 'status' => 'inactive'],
        ];

        foreach ($rows as $r) {
            DisasterCampaignAssignment::create([
                'disaster_id' => $r['disaster_id'],
                'ngo_id' => $r['ngo_id'],
                'assigned_by' => 1,
                'status' => $r['status'],
                'help_needed' => $r['help_needed'],
                'updated_by' => 1,
            ]);
        }
    }
}
