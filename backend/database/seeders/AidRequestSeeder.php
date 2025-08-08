<?php

namespace Database\Seeders;

use App\Models\AidRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AidRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AidRequest::create([
            'disaster_id' => 1,  
            'requester_id' => 1,  
            'location' => 'Dhaka, Bangladesh',
            'aid_type' => 'medical',
            'urgency' => 'critical',
            'description' => 'Urgent medical supplies needed for flood victims.',
            'status' => 'pending',
            'task_id' => 1,     
            'ngo_remarks' => null, 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        AidRequest::create([
            'disaster_id' => 2,
            'requester_id' => 3,
            'location' => 'Dhaka, Bangladesh',
            'aid_type' => 'financial',
            'urgency' => 'high',
            'description' => 'Financial assistance required for rebuilding efforts.',
            'status' => 'pending',
            'task_id' => 2,
            'ngo_remarks' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
