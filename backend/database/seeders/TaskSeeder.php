<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\DisasterCampaignAssignment;
use App\Models\User;
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

        // Choose some users (first few) for deterministic seeding
        $userIds = User::orderBy('id')->pluck('id')->take(8)->values();
        if ($userIds->count() < 3) {
            echo "[TaskSeeder] Not enough users to seed tasks.\n";
            return;
        }

        $u = fn(int $idx) => $userIds[min($idx, $userIds->count()-1)];

        $tasks = [
            [ 'campaign_id'=>$c1,'assigned_to'=>$u(1),'created_by'=>$u(5),'task_type'=>'delivery','aid_request_id'=>null,'location'=>'Dhaka, Bangladesh','start_time'=>$now->copy()->subHours(6),'end_time'=>null,'aid_type'=>'medical','urgency'=>'critical','description'=>'Deliver emergency medical kits to Ward 12 clinic.','status'=>'assigned','ngo_remarks'=>'Prioritize sterile supplies.' ],
            [ 'campaign_id'=>$c1,'assigned_to'=>$u(2),'created_by'=>$u(5),'task_type'=>'aid_request','aid_request_id'=>null,'location'=>'Dhaka, Bangladesh','start_time'=>$now->copy()->subHours(12),'end_time'=>$now->copy()->subHours(8),'aid_type'=>'resource','urgency'=>'high','description'=>'Coordinate pickup of blankets from storage depot.','status'=>'completed','ngo_remarks'=>'Confirm quantities logged.' ],
            [ 'campaign_id'=>$c2,'assigned_to'=>$u(3),'created_by'=>$u(6),'task_type'=>'aid_request','aid_request_id'=>null,'location'=>'Chattogram, Bangladesh','start_time'=>$now->copy()->subHours(30),'end_time'=>null,'aid_type'=>'financial','urgency'=>'medium','description'=>'Assess financial aid eligibility for 5 families.','status'=>'assigned','ngo_remarks'=>'Verification documents required.' ],
            [ 'campaign_id'=>$c2,'assigned_to'=>$u(4),'created_by'=>$u(6),'task_type'=>'delivery','aid_request_id'=>null,'location'=>'Chattogram, Bangladesh','start_time'=>$now->copy()->subHours(50),'end_time'=>$now->copy()->subHours(45),'aid_type'=>'resource','urgency'=>'low','description'=>'Deliver tarpaulins to temporary shelters.','status'=>'completed','ngo_remarks'=>'Delivery confirmed by onsite coordinator.' ],
            [ 'campaign_id'=>$c3,'assigned_to'=>$u(1),'created_by'=>$u(7),'task_type'=>'aid_request','aid_request_id'=>null,'location'=>'Sylhet, Bangladesh','start_time'=>$now->copy()->subHours(4),'end_time'=>null,'aid_type'=>'medical','urgency'=>'high','description'=>'Evaluate need for mobile clinic deployment.','status'=>'assigned','ngo_remarks'=>'Doctor confirmation pending.' ],
            [ 'campaign_id'=>$c3,'assigned_to'=>$u(2),'created_by'=>$u(7),'task_type'=>'aid_request','aid_request_id'=>null,'location'=>'Sylhet, Bangladesh','start_time'=>$now->copy()->subHours(10),'end_time'=>null,'aid_type'=>'financial','urgency'=>'low','description'=>'Prepare initial financial assistance ledger.','status'=>'assigned','ngo_remarks'=>'Awaiting approval signature.' ],
            [ 'campaign_id'=>$c1,'assigned_to'=>$u(3),'created_by'=>$u(5),'task_type'=>'delivery','aid_request_id'=>null,'location'=>'Dhaka, Bangladesh','start_time'=>$now->copy()->subHours(20),'end_time'=>$now->copy()->subHours(16),'aid_type'=>'medical','urgency'=>'critical','description'=>'Cold-chain insulin transport to zone B.','status'=>'completed','ngo_remarks'=>'Temperature log archived.' ],
            [ 'campaign_id'=>$c2,'assigned_to'=>$u(4),'created_by'=>$u(6),'task_type'=>'delivery','aid_request_id'=>null,'location'=>'Chattogram, Bangladesh','start_time'=>$now->copy()->subHours(2),'end_time'=>null,'aid_type'=>'resource','urgency'=>'medium','description'=>'Stage water purification units for dispatch.','status'=>'assigned','ngo_remarks'=>'QA checklist pending.' ],
        ];

        foreach ($tasks as $row) {
            Task::create($row);
        }

        echo "[TaskSeeder] Seeded " . count($tasks) . " tasks (campaigns: $c1,$c2,$c3).\n";
    }
}
