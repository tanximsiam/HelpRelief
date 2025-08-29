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

        // (Optional) Reset for local/dev so you always see the same small demo dataset
        if (app()->environment(['local','development'])) {
            try { DB::statement('PRAGMA foreign_keys = OFF'); } catch (\Throwable $e) {}
            DB::table('tasks')->truncate();
            try { DB::statement('PRAGMA foreign_keys = ON'); } catch (\Throwable $e) {}
            echo "[TaskSeeder] Truncated tasks (local env).\n";
        }

    // Grab first and second campaign ids for demo coverage.
    $campaignId = DisasterCampaignAssignment::orderBy('id')->value('id');
    $campaignId4 = DisasterCampaignAssignment::orderBy('id')->skip(3)->value('id');
    if (!$campaignId) { echo "[TaskSeeder] No campaign found. Aborting.\n"; return; }

        /*
         |--------------------------------------------------------------------------
         | Simple, explicit demo tasks
         |--------------------------------------------------------------------------
         | Each block below creates one Task and an initial VolunteerTaskLog entry
         | in the 'assigned' state (no check-in yet). Adjust volunteer IDs to ones
         | that exist in your users table.
         */

        // Task 1: Medical aid (assigned to volunteer #9)
        $task1 = Task::create([
            'campaign_id' => $campaignId,
            'assigned_to' => 9,
            'created_by' => 1,
            'aid_request_id' => null,
            'location' => 'Chittagong City Hospital',
            'start_time' => $now->copy()->subMinutes(20),
            'end_time' => $now->copy()->addHour(),
            'aid_type' => 'medical',
            'urgency' => 'medium',
            'description' => 'Provide first aid at temporary clinic.',
            'status' => 'assigned',
            'ngo_remarks' => 'Bring medical kit.'
        ]);
        \App\Models\VolunteerTaskLog::create([
            'task_id' => $task1->id,
            'volunteer_id' => 9,
            'campaign_id' => $campaignId,
            'status' => 'assigned',
            'report' => 'normal'
        ]);

        // Task 2: Resource delivery (volunteer #10)
        $task2 = Task::create([
            'campaign_id' => $campaignId,
            'assigned_to' => 10,
            'created_by' => 1,
            'aid_request_id' => null,
            'location' => 'Chittagong Warehouse',
            'start_time' => $now->copy()->subMinutes(10),
            'end_time' => $now->copy()->addMinutes(90),
            'aid_type' => 'resource',
            'urgency' => 'high',
            'description' => 'Load and deliver water bottles.',
            'status' => 'assigned',
            'ngo_remarks' => 'Coordinate with logistics lead.'
        ]);
        \App\Models\VolunteerTaskLog::create([
            'task_id' => $task2->id,
            'volunteer_id' => 10,
            'campaign_id' => $campaignId,
            'status' => 'assigned',
            'report' => 'normal'
        ]);

        // Task 3: Financial assistance processing (volunteer #11)
        $task3 = Task::create([
            'campaign_id' => $campaignId,
            'assigned_to' => 11,
            'created_by' => 1,
            'aid_request_id' => null,
            'location' => 'Remote Coordination Center',
            'start_time' => $now->copy(),
            'end_time' => $now->copy()->addMinutes(120),
            'aid_type' => 'financial',
            'urgency' => 'low',
            'description' => 'Verify beneficiary payment details.',
            'status' => 'assigned',
            'ngo_remarks' => 'Double-check IDs.'
        ]);
        // Task 4: Additional medical follow-up (volunteer #12) campaign 1
        $task4 = Task::create([
            'campaign_id' => $campaignId,
            'assigned_to' => 12,
            'created_by' => 1,
            'aid_request_id' => null,
            'location' => 'Chittagong',
            'start_time' => $now->copy()->addMinutes(15),
            'end_time' => $now->copy()->addMinutes(90),
            'aid_type' => 'medical',
            'urgency' => 'medium',
            'description' => 'Prepare supplies for afternoon shift.',
            'status' => 'assigned',
            'ngo_remarks' => 'Check inventory list.'
        ]);
        \App\Models\VolunteerTaskLog::create([
            'task_id' => $task4->id,
            'volunteer_id' => 12,
            'campaign_id' => $campaignId,
            'status' => 'assigned',
            'report' => 'normal'
        ]);

        // Task 5: Resource staging (volunteer #13) campaign 1 (already started)
        $task5 = Task::create([
            'campaign_id' => $campaignId,
            'assigned_to' => 13,
            'created_by' => 1,
            'aid_request_id' => null,
            'location' => 'Chittagong',
            'start_time' => $now->copy()->subMinutes(40),
            'end_time' => $now->copy()->addMinutes(30),
            'aid_type' => 'resource',
            'urgency' => 'high',
            'description' => 'Sort and stage relief packages.',
            'status' => 'assigned',
            'ngo_remarks' => 'Prioritize water & medicine.'
        ]);
        \App\Models\VolunteerTaskLog::create([
            'task_id' => $task5->id,
            'volunteer_id' => 13,
            'campaign_id' => $campaignId,
            'status' => 'started',
            'check_in' => $now->copy()->subMinutes(35),
            'report' => 'normal'
        ]);

        // Campaign 2 tasks (if a second campaign exists)
        if ($campaignId4) {
            // Task 6: Medical triage (volunteer #14)
            $task6 = Task::create([
                'campaign_id' => $campaignId4,
                'assigned_to' => 14,
                'created_by' => 1,
                'aid_request_id' => null,
                'location' => 'Sylhet',
                'start_time' => $now->copy()->subMinutes(5),
                'end_time' => $now->copy()->addMinutes(110),
                'aid_type' => 'medical',
                'urgency' => 'high',
                'description' => 'Initial triage of incoming patients.',
                'status' => 'assigned',
                'ngo_remarks' => 'Focus on dehydration cases.'
            ]);
            \App\Models\VolunteerTaskLog::create([
                'task_id' => $task6->id,
                'volunteer_id' => 14,
                'campaign_id' => $campaignId4,
                'status' => 'assigned',
                'report' => 'normal'
            ]);

            // Task 7: Financial aid validation (volunteer #15)
            $task7 = Task::create([
                'campaign_id' => $campaignId4,
                'assigned_to' => 15,
                'created_by' => 1,
                'aid_request_id' => null,
                'location' => 'Sylhet',
                'start_time' => $now->copy()->addMinutes(10),
                'end_time' => $now->copy()->addMinutes(130),
                'aid_type' => 'financial',
                'urgency' => 'medium',
                'description' => 'Validate beneficiary documents.',
                'status' => 'assigned',
                'ngo_remarks' => 'Escalate anomalies.'
            ]);
            \App\Models\VolunteerTaskLog::create([
                'task_id' => $task7->id,
                'volunteer_id' => 15,
                'campaign_id' => $campaignId4,
                'status' => 'assigned',
                'report' => 'normal'
            ]);

            // Task 8: Resource distribution wrap-up (volunteer #16) already ended
            $task8 = Task::create([
                'campaign_id' => $campaignId4,
                'assigned_to' => 16,
                'created_by' => 1,
                'aid_request_id' => null,
                'location' => 'Sylhet',
                'start_time' => $now->copy()->subHours(2),
                'end_time' => $now->copy()->subMinutes(30),
                'aid_type' => 'resource',
                'urgency' => 'medium',
                'description' => 'Close out morning distribution shift.',
                'status' => 'assigned',
                'ngo_remarks' => 'Collect leftover inventory counts.'
            ]);
            \App\Models\VolunteerTaskLog::create([
                'task_id' => $task8->id,
                'volunteer_id' => 16,
                'campaign_id' => $campaignId4,
                'status' => 'ended',
                'check_in' => $now->copy()->subHours(1)->subMinutes(45),
                'check_out' => $now->copy()->subMinutes(35),
                'report' => 'normal'
            ]);
        }
    }
}
