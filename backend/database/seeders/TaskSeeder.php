<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\DisasterCampaignAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// (Imports consolidated above)

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Clean slate for dev/local to avoid mismatches (only tasks; logs cleared earlier in DisasterCampaignAssignmentSeeder)
        if (app()->environment(['local','development'])) {
            try { DB::statement('PRAGMA foreign_keys = OFF'); } catch (\Throwable $e) {}
            DB::table('tasks')->truncate();
            try { DB::statement('PRAGMA foreign_keys = ON'); } catch (\Throwable $e) {}
            echo "[TaskSeeder] Truncated tasks (local env).\n";
        }

        $campaignIds = DisasterCampaignAssignment::orderBy('id')->pluck('id')->take(3)->values();
        if ($campaignIds->isEmpty()) {
            echo "[TaskSeeder] No campaigns found.\n";
            return;
        }
        // Fallback if <3 campaigns
        while ($campaignIds->count() < 3) { $campaignIds->push($campaignIds[0]); }

        $c1 = $campaignIds[0];
        $c2 = $campaignIds[1];
        $c3 = $campaignIds[2];

        // Explicit volunteer user IDs requested: 9-16 plus 1,2
        $volunteerIds = [9,10,11,12,13,14,15,16,1,2];
        $tasks = [];
        foreach ($volunteerIds as $i => $volunteerId) {
            $campaignId = 1;
            $tasks[] = [
                'campaign_id' => $campaignId,
                'assigned_to' => $volunteerId,
                'created_by' => 1,
                'aid_request_id' => null,
                'location' => 'Bangladesh',
                'start_time' => $now->copy()->subMinutes(30),
                'end_time' => $now->copy()->addMinutes(30),
                'aid_type' => 'medical',
                'urgency' => 'medium',
                'description' => 'Single active task for volunteer.',
                'status' => 'assigned',
                'ngo_remarks' => 'Seeder generated.'
            ];
        }
        foreach ($tasks as $row) {
            $task = Task::create($row);
            \App\Models\VolunteerTaskLog::create([
                'task_id' => $task->id,
                'volunteer_id' => $row['assigned_to'],
                'campaign_id' => $row['campaign_id'],
                'status' => 'assigned',
                'check_in' => null,
                'check_out' => null,
                'start_verified_by' => null,
                'end_verified_by' => null,
                'report' => 'normal',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

    echo "[TaskSeeder] Seeded " . count($tasks) . " tasks (one per volunteer, 30min before/after now).\n";
    }
}
