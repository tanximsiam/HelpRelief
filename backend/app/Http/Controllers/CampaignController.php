<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;
use App\Models\Disaster;
use App\Models\VolunteerRegistration;

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
     * List NGO's own active campaigns (for ngo_staff) with volunteer counts
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

            $ngoId = $staff->ngo_id;

            $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
                ->where('ngo_id', $ngoId)
                ->where('status', 'active')
                ->get()
                ->map(function ($assignment) use ($ngoId) {
                    // Get volunteer count for this campaign
                    $activeVolunteersCount = VolunteerRegistration::where('disaster_id', $assignment->disaster_id)
                        ->where('ngo_id', $ngoId)
                        ->whereIn('status', ['approved', 'active'])
                        ->count();

                    $totalVolunteersCount = VolunteerRegistration::where('disaster_id', $assignment->disaster_id)
                        ->where('ngo_id', $ngoId)
                        ->count();

                    $campaignData = $this->formatCampaignData($assignment);
                    $campaignData['active_volunteers'] = $activeVolunteersCount;
                    $campaignData['total_volunteers'] = $totalVolunteersCount;

                    return $campaignData;
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

    /**
     * Get campaign statistics with volunteer counts (for reports)
     */
    public function campaignStats(Request $request)
    {
        try {
            $user = $request->user();

            // Check if user is NGO staff
            $staff = NgoStaff::where('user_id', $user->id)->first();
            if (!$staff) {
                return response()->json(['error' => 'Unauthorized. User is not an NGO staff member.'], 403);
            }

            $ngoId = $staff->ngo_id;

            // Get all campaigns for this NGO
            $campaigns = DisasterCampaignAssignment::with(['disaster', 'ngo'])
                ->where('ngo_id', $ngoId)
                ->where('status', 'active')
                ->get()
                ->map(function ($assignment) use ($ngoId) {
                    // Count volunteers for this specific campaign
                    $volunteerStats = VolunteerRegistration::where('disaster_id', $assignment->disaster_id)
                        ->where('ngo_id', $ngoId)
                        ->selectRaw('
                            COUNT(*) as total_volunteers,
                            SUM(CASE WHEN status IN ("approved", "active") THEN 1 ELSE 0 END) as active_volunteers,
                            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_volunteers,
                            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_volunteers,
                            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_volunteers
                        ')
                        ->first();

                    $campaignData = $this->formatCampaignData($assignment);
                    $campaignData['volunteer_stats'] = [
                        'total_volunteers' => $volunteerStats->total_volunteers ?? 0,
                        'active_volunteers' => $volunteerStats->active_volunteers ?? 0,
                        'pending_volunteers' => $volunteerStats->pending_volunteers ?? 0,
                        'rejected_volunteers' => $volunteerStats->rejected_volunteers ?? 0,
                        'completed_volunteers' => $volunteerStats->completed_volunteers ?? 0,
                    ];

                    return $campaignData;
                });

            return response()->json([
                'ngo_id' => $ngoId,
                'campaigns' => $campaigns
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch campaign statistics', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get volunteer counts for a specific campaign
     */
    public function campaignVolunteers(Request $request, $campaignId)
    {
        try {
            $user = $request->user();

            // Check if user is NGO staff
            $staff = NgoStaff::where('user_id', $user->id)->first();
            if (!$staff) {
                return response()->json(['error' => 'Unauthorized. User is not an NGO staff member.'], 403);
            }

            $ngoId = $staff->ngo_id;

            // Get the campaign and verify it belongs to this NGO
            $campaign = DisasterCampaignAssignment::with(['disaster', 'ngo'])
                ->where('id', $campaignId)
                ->where('ngo_id', $ngoId)
                ->first();

            if (!$campaign) {
                return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
            }

            // Get volunteer registrations for this campaign
            $volunteers = VolunteerRegistration::with(['user:id,name,email,phone'])
                ->where('disaster_id', $campaign->disaster_id)
                ->where('ngo_id', $ngoId)
                ->get()
                ->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'user_id' => $registration->user_id,
                        'user_name' => $registration->user->name ?? 'Unknown',
                        'user_email' => $registration->user->email ?? '',
                        'user_phone' => $registration->user->phone ?? '',
                        'status' => $registration->status,
                        'registered_at' => $registration->registered_at,
                        'availability' => $registration->availability,
                        'skills' => $registration->skills,
                        'notes' => $registration->notes,
                        'created_at' => $registration->created_at,
                    ];
                });

            return response()->json([
                'campaign' => $this->formatCampaignData($campaign),
                'volunteers' => $volunteers,
                'summary' => [
                    'total_volunteers' => $volunteers->count(),
                    'active_volunteers' => $volunteers->where('status', 'approved')->count() + $volunteers->where('status', 'active')->count(),
                    'pending_volunteers' => $volunteers->where('status', 'pending')->count(),
                    'rejected_volunteers' => $volunteers->where('status', 'rejected')->count(),
                    'completed_volunteers' => $volunteers->where('status', 'completed')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch campaign volunteers', 'message' => $e->getMessage()], 500);
        }
    }
}
