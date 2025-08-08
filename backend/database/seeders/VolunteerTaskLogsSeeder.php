<?php

namespace Database\Seeders;

use App\Models\VolunteerTaskLog;
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
        VolunteerTaskLog::create([
            'task_id' => 1,         
            'volunteer_id' => 1,    
            'disaster_id' => 1,     
            'status' => 'assigned',
            'check_in' => Carbon::now(), 
            'check_out' => null,    
            'start_verified_by' => null, 
            'end_verified_by' => null, 
            'report' => 'normal',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        VolunteerTaskLog::create([
            'task_id' => 2,         
            'volunteer_id' => 3,    
            'disaster_id' => 2,     
            'status' => 'assigned',
            'check_in' => Carbon::now(), 
            'check_out' => null,    
            'start_verified_by' => null, 
            'end_verified_by' => null, 
            'report' => 'normal',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
