<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;
use App\Models\Disaster;

class CampaignController extends Controller
{
    /**
     * List all active campaigns (for volunteers/general users)
     */
    public function index()
    {
        try {
            $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
                ->where('status', 'active')
                ->get()
                ->map(function ($assignment) {
                    return $this->formatCampaignData($assignment);
                });

            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch campaigns'], 500);
        }
    }

    /**
     * List NGO's own active campaigns (for ngo_staff)
     */
    public function myCampaigns(Request $request)
    {
        try {
            $user = $request->user();

            // Find the NGO staff record for the user
            $staff = NgoStaff::where('user_id', $user->id)->first();

            if (!$staff) {
                return response()->json(['error' => 'Unauthorized. User is not an NGO staff member.'], 403);
            }

            $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
                ->where('ngo_id', $staff->ngo_id)
                ->where('status', 'active')
                ->get()
                ->map(function ($assignment) {
                    return $this->formatCampaignData($assignment);
                });

            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch NGO campaigns'], 500);
        }
    }

    /**
     * Format campaign data for consistent response structure
     */
    private function formatCampaignData($assignment)
    {
        return [
            'id' => $assignment->id,
            'name' => $assignment->disaster->name . ' Campaign',
            'disaster_id' => $assignment->disaster_id,
            'disaster_name' => $assignment->disaster->name ?? 'Unknown Disaster',
            'disaster_type' => $assignment->disaster->type ?? 'Unknown Type',
            'disaster_location' => $assignment->disaster->location ?? 'Unknown Location',
            'ngo_id' => $assignment->ngo_id,
            'ngo_name' => $assignment->ngo->name ?? 'Unknown NGO',
            'status' => $assignment->status,
            'help_needed' => $assignment->help_needed,
            'created_at' => $assignment->created_at,
            'updated_at' => $assignment->updated_at,
        ];
    }

    /**
     * Get a specific campaign by ID
     */
    public function show($id)
    {
        try {
            $campaign = DisasterCampaignAssignment::with(['disaster', 'ngo', 'assignedBy', 'updatedBy'])
                ->findOrFail($id);

            return response()->json($this->formatCampaignData($campaign));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
    }

    /**
     * Create a new campaign assignment (NGO staff) linking an NGO to a disaster.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized. User is not NGO staff.'], 403);
        }

        $validated = $request->validate([
            'disaster_id' => ['required','integer','exists:disasters,id'],
            'help_needed' => ['required','in:low,medium,high'],
            'status' => ['nullable','in:active,inactive,pending']
        ]);

        // Ensure disaster exists
        $disaster = Disaster::find($validated['disaster_id']);
        if (!$disaster) {
            return response()->json(['error' => 'Disaster not found'], 404);
        }

        // Prevent duplicate campaign registration for same NGO & disaster
        $already = DisasterCampaignAssignment::where('disaster_id', $validated['disaster_id'])
            ->where('ngo_id', $staff->ngo_id)
            ->first();
        if ($already) {
            return response()->json([
                'errors' => [
                    'disaster_id' => ['A campaign for this disaster is already registered by your NGO.']
                ]
            ], 422);
        }

        $assignment = DisasterCampaignAssignment::create([
            'disaster_id' => $validated['disaster_id'],
            'ngo_id' => $staff->ngo_id,
            'assigned_by' => $user->id,
            'status' => $validated['status'] ?? 'active',
            'help_needed' => $validated['help_needed'],
            'updated_by' => $user->id,
        ]);

        $assignment->load(['disaster','ngo']);

        return response()->json($this->formatCampaignData($assignment), 201);
    }
}
