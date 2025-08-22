<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;
use App\Models\Disaster;
use Illuminate\Support\Facades\DB;
use App\Models\AidRequest;

class MapController extends Controller
{
    /**
     * Get campaign intensity data for map visualization by NGO
     */
    public function getCampaignIntensityByState(Request $request)
    {
        $user = $request->user();

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Bangladesh districts/states we're tracking
        $bangladeshStates = [
            'Dhaka',
            'Chittagong',
            'Rajshahi',
            'Khulna',
            'Barisal',
            'Sylhet',
            'Rangpur'
        ];

        // Get campaign counts by state for this NGO
        $campaignIntensity = DisasterCampaignAssignment::join('disasters', 'disaster_campaign_assignments.disaster_id', '=', 'disasters.id')
            ->where('disaster_campaign_assignments.ngo_id', $ngoId)
            ->where('disaster_campaign_assignments.status', 'active')
            ->select('disasters.location', DB::raw('count(*) as campaign_count'))
            ->groupBy('disasters.location')
            ->get()
            ->keyBy('location');

        // Prepare response data for all states
        $stateData = [];
        foreach ($bangladeshStates as $state) {
            $stateData[] = [
                'name' => $state,
                'campaign_count' => $campaignIntensity->get($state)?->campaign_count ?? 0,
                'intensity' => $this->calculateIntensity($campaignIntensity->get($state)?->campaign_count ?? 0)
            ];
        }

        return response()->json([
            'ngo_id' => $ngoId,
            'states' => $stateData,
            'max_campaigns' => $campaignIntensity->max('campaign_count') ?? 0
        ]);
    }

    /**
     * Get detailed state information for a specific state
     */
    public function getStateDetails(Request $request, $stateName)
    {
        $user = $request->user();

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Get campaigns in this state for this NGO
        $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
            ->join('disasters', 'disaster_campaign_assignments.disaster_id', '=', 'disasters.id')
            ->where('disaster_campaign_assignments.ngo_id', $ngoId)
            ->where('disasters.location', $stateName)
            ->where('disaster_campaign_assignments.status', 'active')
            ->select('disaster_campaign_assignments.*')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'disaster_name' => $assignment->disaster->name,
                    'disaster_type' => $assignment->disaster->type,
                    'severity' => $assignment->disaster->severity,
                    'status' => $assignment->status,
                    'help_needed' => $assignment->help_needed,
                    'created_at' => $assignment->created_at->format('Y-m-d H:i:s')
                ];
            });

        // Get some basic statistics (dummy data for now as requested)
        $stats = [
            'total_campaigns' => $campaigns->count(),
            'active_volunteers' => rand(10, 50), // Dummy data
            'aid_distributed' => rand(100, 1000), // Dummy data
            'beneficiaries_reached' => rand(500, 5000), // Dummy data
        ];

        return response()->json([
            'state_name' => $stateName,
            'ngo_id' => $ngoId,
            'campaigns' => $campaigns,
            'statistics' => $stats
        ]);
    }

    /**
     * Calculate intensity level based on campaign count
     */
    private function calculateIntensity($campaignCount)
    {
        if ($campaignCount == 0) return 0;
        if ($campaignCount <= 2) return 0.3;
        if ($campaignCount <= 5) return 0.6;
        return 1.0;
    }

    /**
     * Aid Request Density (Heatmap) by state for NGO staff
     * Returns request counts + urgency breakdown + weighted intensity (0..1)
     */
    public function getAidRequestDensityByState(Request $request)
    {
        $user = $request->user();
        $staff = \App\Models\NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }
        $ngoId = $staff->ngo_id;

        $bangladeshStates = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur'];

        // Map district / city names to their division for normalization
        $districtToDivision = [
            "Cox’s Bazar" => 'Chittagong',
            "Cox's Bazar" => 'Chittagong',
            'Comilla' => 'Chittagong',
            'Feni' => 'Chittagong',
            'Noakhali' => 'Chittagong',
            'Brahmanbaria' => 'Chittagong',
            'Hatiya' => 'Chittagong',
            'Sirajganj' => 'Rajshahi',
            'Bogra' => 'Rajshahi',
            'Pabna' => 'Rajshahi',
            'Jessore' => 'Khulna',
            'Khustia' => 'Khulna',
            'Jhalokati' => 'Barisal',
            'Patuakhali' => 'Barisal',
            'Sunamganj' => 'Sylhet',
            'Moulvibazar' => 'Sylhet',
            'Gaibandha' => 'Rangpur',
            'Kurigram' => 'Rangpur',
        ];

    // Raw aggregation by original stored location (district OR division)
        $raw = AidRequest::join('users', 'aid_requests.requester_id', '=', 'users.id')
            ->join('volunteer_registrations', 'users.id', '=', 'volunteer_registrations.user_id')
            ->where('volunteer_registrations.ngo_id', $ngoId)
            ->select(
                'aid_requests.location',
                DB::raw('COUNT(*) as request_count'),
                DB::raw("SUM(CASE WHEN aid_requests.urgency='low' THEN 1 ELSE 0 END) as low_count"),
                DB::raw("SUM(CASE WHEN aid_requests.urgency='medium' THEN 1 ELSE 0 END) as medium_count"),
                DB::raw("SUM(CASE WHEN aid_requests.urgency='high' THEN 1 ELSE 0 END) as high_count"),
        DB::raw("SUM(CASE WHEN aid_requests.urgency='critical' THEN 1 ELSE 0 END) as critical_count")
            )
            ->groupBy('aid_requests.location')
            ->get();

        // Fold into division buckets
        $buckets = [];
        foreach ($raw as $row) {
            $original = $row->location;
            $division = $districtToDivision[$original] ?? $original; // if already a division keep it
            if (!in_array($division, $bangladeshStates, true)) {
                // Skip any location we cannot map
                continue;
            }
            if (!isset($buckets[$division])) {
                $buckets[$division] = [
                    'request_count' => 0,
                    'low' => 0,
                    'medium' => 0,
                    'high' => 0,
                    'critical' => 0,
                ];
            }
            $buckets[$division]['request_count'] += (int)$row->request_count;
            $buckets[$division]['low'] += (int)$row->low_count;
            $buckets[$division]['medium'] += (int)$row->medium_count;
            $buckets[$division]['high'] += (int)$row->high_count;
            $buckets[$division]['critical'] += (int)$row->critical_count;
        }

        // Determine maximum request count for normalization
        $maxCount = 0;
        foreach ($buckets as $data) {
            if ($data['request_count'] > $maxCount) $maxCount = $data['request_count'];
        }

        $stateData = [];
        foreach ($bangladeshStates as $state) {
            $data = $buckets[$state] ?? null;
            $count = $data['request_count'] ?? 0;
            $intensity = $maxCount > 0 ? round($count / $maxCount, 3) : 0; // normalized by raw count
            $stateData[] = [
                'name' => $state,
                'request_count' => $count,
                'breakdown' => [
                    'low' => $data['low'] ?? 0,
                    'medium' => $data['medium'] ?? 0,
                    'high' => $data['high'] ?? 0,
                    'critical' => $data['critical'] ?? 0,
                ],
                'intensity' => $intensity,
            ];
        }

        return response()->json([
            'ngo_id' => $ngoId,
            'states' => $stateData,
            'max_request_count' => $maxCount,
            'mapped' => true,
        ]);
    }

    /**
     * Detailed aid requests for a specific division/state (used by inline overlay)
     */
    public function getAidRequestsForState(Request $request, $stateName)
    {
        $user = $request->user();
        $staff = \App\Models\NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }
        $ngoId = $staff->ngo_id;

        $allowed = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur'];
        $match = null;
        foreach ($allowed as $a) { if (strcasecmp($a, $stateName) === 0) { $match = $a; break; } }
        if (!$match) {
            return response()->json(['error' => 'Invalid state name'], 422);
        }

        $requests = AidRequest::with(['requester:id,name'])
            ->join('users', 'aid_requests.requester_id', '=', 'users.id')
            ->join('volunteer_registrations', 'users.id', '=', 'volunteer_registrations.user_id')
            ->where('volunteer_registrations.ngo_id', $ngoId)
            ->where('aid_requests.location', $match)
            ->select('aid_requests.*')
            ->orderByRaw("(urgency='critical') DESC, (urgency='high') DESC, (urgency='medium') DESC, (urgency='low') DESC")
            ->orderBy('aid_requests.created_at','desc')
            ->limit(200)
            ->get()
            ->map(function($r){ return [
                'id' => $r->id,
                'disaster_id' => $r->disaster_id,
                'requester' => [ 'id' => $r->requester?->id, 'name' => $r->requester?->name ],
                'aid_type' => $r->aid_type,
                'urgency' => $r->urgency,
                'status' => $r->status,
                'description' => $r->description,
                'created_at' => $r->created_at?->toIso8601String(),
            ];});

        return response()->json([
            'state' => $match,
            'count' => $requests->count(),
            'requests' => $requests,
        ]);
    }
}
