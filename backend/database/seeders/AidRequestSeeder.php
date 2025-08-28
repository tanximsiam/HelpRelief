<?php

namespace Database\Seeders;

use App\Models\AidRequest;
use App\Models\DisasterCampaignAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AidRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dynamically seed aid requests based on active campaigns (disaster_campaign_assignments)
        $campaigns = DisasterCampaignAssignment::with('disaster')
            ->where('status', 'active')
            ->get();
        if ($campaigns->isEmpty()) {
            return; // nothing active to seed against
        }

        $now = Carbon::now();
        $aidTypes = ['medical','financial','resource'];
        $requesterIds = [1,3]; // volunteer users from UserSeeder

        // Severity → target counts per urgency profile
        $severityProfiles = [
            'low' =>    ['critical' => 0, 'high' => 1, 'medium' => 3, 'low' => 2],
            'medium' => ['critical' => 1, 'high' => 3, 'medium' => 4, 'low' => 2],
            'high' =>   ['critical' => 3, 'high' => 4, 'medium' => 4, 'low' => 2],
        ];

        foreach ($campaigns as $campaign) {
            $disaster = $campaign->disaster; // related disaster for severity + location
            if (!$disaster) { continue; }
            $profile = $severityProfiles[$disaster->severity] ?? $severityProfiles['medium'];
            foreach ($profile as $urgency => $count) {
                for ($i = 0; $i < $count; $i++) {
                    AidRequest::create([
                        'campaign_id' => $campaign->id,
                        'requester_id' => $requesterIds[$i % count($requesterIds)],
                        'location' => $disaster->location, // raw location, mapping handled later
                        'aid_type' => $aidTypes[array_rand($aidTypes)],
                        'urgency' => $urgency,
                        'description' => ucfirst($urgency) . ' urgency request linked to ' . $disaster->name . ' #' . ($i+1),
                        'status' => 'pending',
                        'task_id' => null,
                        'ngo_remarks' => null,
                        'created_at' => $now->copy()->subMinutes(rand(0, 1440)),
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
