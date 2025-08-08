<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'disaster_id' => 1,  
            'assigned_to' => 1,   
            'created_by' => 6,    
            'task_type' => 'aid_request',
            'aid_request_id' => 1,  
            'location' => 'Dhaka, Bangladesh',
            'start_time' => Carbon::now(),
            'end_time' => null,
            'aid_type' => 'medical',
            'urgency' => 'critical',
            'description' => 'Urgent medical supplies required for flood victims.',
            'status' => 'assigned',
            'ngo_remarks' => 'Ensure all medical supplies are delivered on time.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ]);
        Task::create([
            'disaster_id' => 2,
            'assigned_to' => 3,
            'created_by' => 6,
            'task_type' => 'aid_request',
            'aid_request_id' => 2,
            'location' => 'Dhaka, Bangladesh',
            'start_time' => Carbon::now(),
            'end_time' => null,
            'aid_type' => 'financial',
            'urgency' => 'high',
            'description' => 'Financial assistance required for rebuilding efforts.',
            'status' => 'assigned',
            'ngo_remarks' => 'Ensure timely disbursement of funds.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        Task::create([
            'disaster_id' => 3,
            'assigned_to' => 1,
            'created_by' => 7,
            'task_type' => 'aid_request',
            'aid_request_id' => null,
            'location' => 'Dhaka, Bangladesh',
            'start_time' => Carbon::now(),
            'end_time' => null,
            'aid_type' => 'financial',
            'urgency' => 'high',
            'description' => 'Financial assistance required for rebuilding efforts.',
            'status' => 'assigned',
            'ngo_remarks' => 'Ensure timely disbursement of funds.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
