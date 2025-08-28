<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;
use App\Models\Disaster;
use Illuminate\Support\Facades\DB;
use App\Models\AidRequest;
use App\Models\AidSupport;
use App\Models\VolunteerRegistration;

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
            'Rangpur',
            'Mymensingh'
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

        // Get disaster IDs for this state and NGO
        $disasterIds = DisasterCampaignAssignment::join('disasters', 'disaster_campaign_assignments.disaster_id', '=', 'disasters.id')
            ->where('disaster_campaign_assignments.ngo_id', $ngoId)
            ->where('disasters.location', $stateName)
            ->pluck('disaster_campaign_assignments.disaster_id');

        // Count active volunteers for disasters in this state for this NGO
        $activeVolunteers = VolunteerRegistration::whereIn('disaster_id', $disasterIds)
            ->where('ngo_id', $ngoId)
            ->whereIn('status', ['approved', 'active'])
            ->count();

        // Count aid distributed (received aid supports) for disasters in this state for this NGO
        $aidDistributed = AidSupport::whereIn('disaster_id', $disasterIds)
            ->where('ngo_id', $ngoId)
            ->where('status', 'received')
            ->count();

        // Count beneficiaries reached (completed aid requests) for disasters in this state
        $beneficiariesReached = AidRequest::whereHas('campaign', function($q) use ($disasterIds){ $q->whereIn('disaster_id',$disasterIds); })
            ->where('status','completed')
            ->count();

        // Get basic statistics
        $stats = [
            'total_campaigns' => $campaigns->count(),
            'active_volunteers' => $activeVolunteers,
            'aid_distributed' => $aidDistributed,
            'beneficiaries_reached' => $beneficiariesReached,
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
        // NOTE: We intentionally avoid joining volunteer_registrations directly because
        // multiple volunteer registrations for a single user (across disasters) would
        // duplicate each aid request row and inflate counts. Instead we filter by a
        // distinct subquery of eligible user_ids for the NGO.
        $eligibleUsersSub = DB::table('volunteer_registrations')
            ->select('user_id')
            ->where('ngo_id', $ngoId)
            ->distinct();

        $raw = AidRequest::whereIn('requester_id', $eligibleUsersSub)
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

        // Avoid duplicate rows caused by multiple volunteer_registrations per user
        // by filtering with a subquery instead of joining volunteer_registrations.
        $requests = AidRequest::with(['requester:id,name'])
            ->where('location', $match)
            ->whereIn('requester_id', function($q) use ($ngoId) {
                $q->select('user_id')->from('volunteer_registrations')->where('ngo_id', $ngoId);
            })
            ->orderByRaw("(urgency='critical') DESC, (urgency='high') DESC, (urgency='medium') DESC, (urgency='low') DESC")
            ->orderBy('created_at','desc')
            ->limit(200)
            ->get()
            ->map(function($r){ return [
                'id' => $r->id,
                'campaign_id' => $r->campaign_id,
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
     //* Get Aid Need Density by State for NGO staff
     //* Calculates aid need as: Aid Requests - Aid Support
     //* Returns prioritized deployment zones and volunteer task assignments
     //*/
    public function getAidNeedByState(Request $request)
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

        // Get aid requests by location for this NGO's volunteer area
        $aidRequests = \App\Models\AidRequest::join('users', 'aid_requests.requester_id', '=', 'users.id')
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

        // Get aid support (tasks) by location for this NGO
        $aidSupport = \App\Models\AidSupport::where('ngo_id', $ngoId)
            ->select(
                'disaster_id',
                DB::raw('COUNT(*) as support_count')
            )
            ->groupBy('disaster_id')
            ->get()
            ->keyBy('disaster_id');

        // Get disaster locations for aid support
        $disasterLocations = \App\Models\Disaster::whereIn('id', $aidSupport->keys())
            ->select('id', 'location')
            ->get()
            ->keyBy('id');

        // Aggregate aid requests by division
        $requestBuckets = [];
        foreach ($aidRequests as $row) {
            $original = $row->location;
            $division = $districtToDivision[$original] ?? $original;

            if (!in_array($division, $bangladeshStates, true)) {
                continue;
            }

            if (!isset($requestBuckets[$division])) {
                $requestBuckets[$division] = [
                    'request_count' => 0,
                    'low' => 0,
                    'medium' => 0,
                    'high' => 0,
                    'critical' => 0,
                ];
            }

            $requestBuckets[$division]['request_count'] += (int)$row->request_count;
            $requestBuckets[$division]['low'] += (int)$row->low_count;
            $requestBuckets[$division]['medium'] += (int)$row->medium_count;
            $requestBuckets[$division]['high'] += (int)$row->high_count;
            $requestBuckets[$division]['critical'] += (int)$row->critical_count;
        }

        // Aggregate aid support by division
        $supportBuckets = [];
        foreach ($aidSupport as $disasterId => $support) {
            $disaster = $disasterLocations->get($disasterId);
            if (!$disaster) continue;

            $original = $disaster->location;
            $division = $districtToDivision[$original] ?? $original;

            if (!in_array($division, $bangladeshStates, true)) {
                continue;
            }

            if (!isset($supportBuckets[$division])) {
                $supportBuckets[$division] = 0;
            }

            $supportBuckets[$division] += (int)$support->support_count;
        }

        // Calculate aid need and prepare state data
        $stateData = [];
        $deploymentZones = [];

        foreach ($bangladeshStates as $state) {
            $requestData = $requestBuckets[$state] ?? null;
            $requestCount = $requestData['request_count'] ?? 0;
            $supportCount = $supportBuckets[$state] ?? 0;
            $aidNeeded = max(0, $requestCount - $supportCount);

            // Calculate urgency-weighted need score for prioritization
            $urgencyScore = 0;
            if ($requestData) {
                $urgencyScore = ($requestData['low'] * 1) +
                               ($requestData['medium'] * 2) +
                               ($requestData['high'] * 3) +
                               ($requestData['critical'] * 4);
            }

            $stateData[] = [
                'name' => $state,
                'request_count' => $requestCount,
                'support_count' => $supportCount,
                'aid_needed' => $aidNeeded,
                'urgency_score' => $urgencyScore,
                'breakdown' => [
                    'low' => $requestData['low'] ?? 0,
                    'medium' => $requestData['medium'] ?? 0,
                    'high' => $requestData['high'] ?? 0,
                    'critical' => $requestData['critical'] ?? 0,
                ],
                'intensity' => $this->calculateAidNeedIntensity($aidNeeded, $urgencyScore),
            ];

            // Add to deployment zones if there's significant need
            if ($aidNeeded > 0) {
                $deploymentZones[] = [
                    'state' => $state,
                    'aid_needed' => $aidNeeded,
                    'urgency_score' => $urgencyScore,
                    'priority_level' => $this->getPriorityLevel($aidNeeded, $urgencyScore),
                    'recommended_volunteers' => $this->calculateRecommendedVolunteers($aidNeeded, $urgencyScore),
                ];
            }
        }

        // Sort deployment zones by priority
        usort($deploymentZones, function($a, $b) {
            return $b['urgency_score'] - $a['urgency_score'];
        });

        // Get volunteer task prioritization
        $volunteerTasks = $this->getVolunteerTaskPrioritization($ngoId, $deploymentZones);

        return response()->json([
            'ngo_id' => $ngoId,
            'states' => $stateData,
            'deployment_zones' => $deploymentZones,
            'volunteer_tasks' => $volunteerTasks,
            'total_aid_needed' => array_sum(array_column($stateData, 'aid_needed')),
            'total_requests' => array_sum(array_column($stateData, 'request_count')),
            'total_support' => array_sum(array_column($stateData, 'support_count')),
        ]);
    }

    /**
     * Test method for aid need calculation (REMOVE IN PRODUCTION)
     * This method provides randomized test data for frontend development
     */
    public function testAidNeed()
    {
        // Get actual aid request locations from database
        $actualLocations = AidRequest::select('location')
            ->distinct()
            ->get()
            ->pluck('location')
            ->toArray();

        // Only show data for regions that actually have aid requests
        $bangladeshStates = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur'];
        $statesWithData = array_intersect($bangladeshStates, $actualLocations);

        $stateData = [];
        $deploymentZones = [];

        foreach ($bangladeshStates as $state) {
            if (in_array($state, $statesWithData)) {
                // Generate realistic data for states with actual aid requests
                $requestCount = rand(5, 20);
                $supportCount = rand(0, min($requestCount, 10)); // Support can't exceed requests
                $aidNeeded = max(0, $requestCount - $supportCount);

                // Generate realistic urgency breakdown that sums to request count
                $remaining = $requestCount;
                $low = rand(0, min($remaining, 5));
                $remaining -= $low;
                $medium = rand(0, min($remaining, 5));
                $remaining -= $medium;
                $high = rand(0, min($remaining, 5));
                $remaining -= $high;
                $critical = $remaining; // Whatever is left

                // Calculate urgency score properly
                $urgencyScore = ($low * 1) + ($medium * 2) + ($high * 3) + ($critical * 4);

                $stateData[] = [
                    'name' => $state,
                    'request_count' => $requestCount,
                    'support_count' => $supportCount,
                    'aid_needed' => $aidNeeded,
                    'urgency_score' => $urgencyScore,
                    'breakdown' => [
                        'low' => $low,
                        'medium' => $medium,
                        'high' => $high,
                        'critical' => $critical,
                    ],
                    'intensity' => $this->calculateAidNeedIntensity($aidNeeded, $urgencyScore),
                ];

                if ($aidNeeded > 0) {
                    $deploymentZones[] = [
                        'state' => $state,
                        'aid_needed' => $aidNeeded,
                        'urgency_score' => $urgencyScore,
                        'priority_level' => $this->getPriorityLevel($aidNeeded, $urgencyScore),
                        'recommended_volunteers' => $this->calculateRecommendedVolunteers($aidNeeded, $urgencyScore),
                    ];
                }
            } else {
                // States without data show zero values
                $stateData[] = [
                    'name' => $state,
                    'request_count' => 0,
                    'support_count' => 0,
                    'aid_needed' => 0,
                    'urgency_score' => 0,
                    'breakdown' => [
                        'low' => 0,
                        'medium' => 0,
                        'high' => 0,
                        'critical' => 0,
                    ],
                    'intensity' => 0,
                ];
            }
        }

        // Sort deployment zones by priority
        usort($deploymentZones, function($a, $b) {
            return $b['urgency_score'] - $a['urgency_score'];
        });

        return response()->json([
            'message' => 'Test data for aid need visualization (only showing regions with actual data)',
            'states' => $stateData,
            'deployment_zones' => $deploymentZones,
            'total_aid_needed' => array_sum(array_column($stateData, 'aid_needed')),
            'total_requests' => array_sum(array_column($stateData, 'request_count')),
            'total_support' => array_sum(array_column($stateData, 'support_count')),
            'regions_with_data' => $statesWithData,
        ]);
    }

    /**
     * Calculate aid need intensity based on quantity and urgency
     */
    private function calculateAidNeedIntensity($aidNeeded, $urgencyScore)
    {
        if ($aidNeeded == 0) return 0;

        // Normalize by combining quantity and urgency
        $quantityFactor = min($aidNeeded / 10, 1.0); // Normalize to 0-1, assuming 10+ requests is max
        $urgencyFactor = min($urgencyScore / 20, 1.0); // Normalize to 0-1, assuming 20+ urgency score is max

        return round(($quantityFactor * 0.6 + $urgencyFactor * 0.4), 3);
    }

    /**
     * Get priority level for deployment zones
     */
    private function getPriorityLevel($aidNeeded, $urgencyScore)
    {
        $totalScore = $aidNeeded + ($urgencyScore * 0.5);

        if ($totalScore >= 15) return 'critical';
        if ($totalScore >= 10) return 'high';
        if ($totalScore >= 5) return 'medium';
        return 'low';
    }

    /**
     * Calculate recommended number of volunteers based on need
     */
    private function calculateRecommendedVolunteers($aidNeeded, $urgencyScore)
    {
        $baseVolunteers = max(1, ceil($aidNeeded / 3)); // 1 volunteer per 3 aid requests
        $urgencyBonus = ceil($urgencyScore / 8); // Additional volunteers for high urgency

        return min(20, $baseVolunteers + $urgencyBonus); // Cap at 20 volunteers
    }

    /**
     * Get volunteer task prioritization recommendations
     */
    private function getVolunteerTaskPrioritization($ngoId, $deploymentZones)
    {
        // Get available volunteers for this NGO
        $availableVolunteers = \App\Models\VolunteerRegistration::where('ngo_id', $ngoId)
            ->where('status', 'active')
            ->count();

        $taskRecommendations = [];

        foreach ($deploymentZones as $zone) {
            $recommendedVolunteers = $zone['recommended_volunteers'];
            $availableForZone = min($recommendedVolunteers, $availableVolunteers);

            if ($availableForZone > 0) {
                $taskRecommendations[] = [
                    'state' => $zone['state'],
                    'priority_level' => $zone['priority_level'],
                    'recommended_volunteers' => $availableForZone,
                    'task_types' => $this->getRecommendedTaskTypes($zone['priority_level']),
                    'estimated_duration' => $this->getEstimatedTaskDuration($zone['aid_needed']),
                ];

                $availableVolunteers -= $availableForZone;
            }
        }

        return $taskRecommendations;
    }

    /**
     * Get recommended task types based on priority level
     */
    private function getRecommendedTaskTypes($priorityLevel)
    {
        $taskTypes = [
            'critical' => ['emergency_response', 'medical_aid', 'food_distribution', 'evacuation_support'],
            'high' => ['food_distribution', 'medical_aid', 'shelter_setup', 'logistics'],
            'medium' => ['food_distribution', 'shelter_setup', 'logistics', 'community_outreach'],
            'low' => ['community_outreach', 'logistics', 'data_collection'],
        ];

        return $taskTypes[$priorityLevel] ?? ['general_support'];
    }

    /**
     * Get estimated task duration based on aid needed
     */
    private function getEstimatedTaskDuration($aidNeeded)
    {
        if ($aidNeeded >= 20) return '3-5 days';
        if ($aidNeeded >= 10) return '2-3 days';
        if ($aidNeeded >= 5) return '1-2 days';
        return '1 day';
    }
}
