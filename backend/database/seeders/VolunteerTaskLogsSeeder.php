<?php

namespace Database\Seeders;

use App\Models\VolunteerTaskLog;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VolunteerTaskLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Create one log for first two tasks (if they exist) using actual campaign IDs to keep referential integrity after fresh migrate
        $task1 = Task::find(1);
        if ($task1) {
            VolunteerTaskLog::create([
                'task_id' => $task1->id,
                'volunteer_id' => 1,
                'campaign_id' => $task1->campaign_id,
                'status' => 'assigned',
                'check_in' => $now->copy()->subHour(),
                'check_out' => null,
                'start_verified_by' => null,
                'end_verified_by' => null,
                'report' => 'normal',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Pick a second distinct campaign task if available (ID 3 often belongs to another campaign in seeder)
        $task2 = Task::find(3) ?: Task::where('campaign_id','!=',$task1?->campaign_id)->first();
        if ($task2) {
            VolunteerTaskLog::create([
                'task_id' => $task2->id,
                'volunteer_id' => 2,
                'campaign_id' => $task2->campaign_id,
                'status' => 'assigned',
                'check_in' => $now->copy()->subHours(2),
                'check_out' => null,
                'start_verified_by' => null,
                'end_verified_by' => null,
                'report' => 'normal',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
