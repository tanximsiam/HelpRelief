<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;
use App\Models\Disaster;
use Illuminate\Support\Facades\DB;

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
}
