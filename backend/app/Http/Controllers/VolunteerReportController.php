<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerTaskLog;
use App\Models\NgoStaff;
use App\Models\VolunteerRegistration;
use App\Models\DisasterCampaignAssignment; // Added missing import
use Illuminate\Support\Facades\Log;

class VolunteerReportController extends Controller
{
    public function aggregate(Request $request)
    {
        $user = $request->user();
        $campaignId = $request->query('campaign_id');

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Validate campaign_id is required
        if (!$campaignId) {
            return response()->json(['error' => 'campaign_id is required'], 400);
        }

        // Verify campaign belongs to this NGO
        $campaign = DisasterCampaignAssignment::where('id', $campaignId)
            ->where('ngo_id', $ngoId)
            ->first();

        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
        }

        // Get volunteer registration statistics for this campaign
        $volunteerStats = VolunteerRegistration::where('ngo_id', $ngoId)
            ->where('campaign_id', $campaignId)
            ->selectRaw('
                COUNT(*) as total_volunteers,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as active_volunteers,
                SUM(CASE WHEN status = "flagged" THEN 1 ELSE 0 END) as flagged_volunteers
            ')
            ->first();

        // Get task-related statistics for approved volunteers in this campaign
        $baseQuery = function() use ($campaignId, $ngoId) {
            return VolunteerTaskLog::where('campaign_id', $campaignId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId)
                            ->where('campaign_id', $campaignId);
                            // ->where('status', 'approved');
                    });
                });
        };

        $volunteersWithTasks = $baseQuery()->where('status', 'started')->distinct('volunteer_id')->count('volunteer_id');
        $tasksAssigned = $baseQuery()->distinct('volunteer_id')->count('volunteer_id');
        $tasksCompleted = $baseQuery()->where('status', 'ended')->count();
        $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

        $totalHours = $baseQuery()
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get()
            ->sum(function ($log) {
                if ($log->check_in && $log->check_out) {
                    $checkIn = \Carbon\Carbon::parse($log->check_in);
                    $checkOut = \Carbon\Carbon::parse($log->check_out);

                    // Ensure check_out is after check_in
                    if ($checkOut->gt($checkIn)) {
                        return round($checkIn->diffInHours($checkOut, true), 4);
                    }
                }
                return 0;
            });

        return response()->json([
            'campaign_id' => (int) $campaignId,
            'disaster_id' => (int) $campaign->disaster_id,
            'ngo_id' => $ngoId,
            'report_type' => 'comprehensive',
            'total_volunteers' => $volunteerStats->total_volunteers ?? 0,
            'active_volunteers' => $volunteerStats->active_volunteers ?? 0,
            'flagged_volunteers' => $volunteerStats->flagged_volunteers ?? 0,
            'volunteers_with_tasks' => $volunteersWithTasks,
            'tasks_assigned' => $tasksAssigned,
            'tasks_completed' => $tasksCompleted,
            'completion_rate' => $completionRate,
            'total_hours' => $totalHours
        ]);
    }

    public function individual(Request $request)
    {
        $user = $request->user();
        $campaignId = $request->query('campaign_id');

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Validate campaign_id is required
        if (!$campaignId) {
            return response()->json(['error' => 'campaign_id is required'], 400);
        }

        // Verify campaign belongs to this NGO
        $campaign = DisasterCampaignAssignment::where('id', $campaignId)
            ->where('ngo_id', $ngoId)
            ->first();

        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
        }

        // Get all volunteers for this NGO and campaign
        $volunteers = VolunteerRegistration::where('ngo_id', $ngoId)
            ->where('campaign_id', $campaignId)
            ->with(['user:id,name'])
            ->get()
            ->map(function ($registration) use ($campaignId) {
                // Get task logs for this volunteer in this campaign
                $taskLogs = VolunteerTaskLog::where('campaign_id', $campaignId)
                    ->where('volunteer_id', $registration->user_id)
                    ->get();

                // Calculate statistics
                $tasksAssigned = $taskLogs->whereIn('status', ['assigned', 'started'])->count();
                $tasksCompleted = $taskLogs->where('status', 'ended')->count();

                $totalHours = $taskLogs->sum(function ($log) {
                    if ($log->check_in && $log->check_out) {
                        $checkIn = \Carbon\Carbon::parse($log->check_in);
                        $checkOut = \Carbon\Carbon::parse($log->check_out);

                        // Ensure check_out is after check_in
                        if ($checkOut->gt($checkIn)) {
                            return round($checkIn->diffInHours($checkOut, true), 4);
                        }
                    }
                    return 0;
                });

                return [
                    'volunteer_id' => $registration->user_id,
                    'registration_id' => $registration->id,
                    'name' => $registration->user->name ?? 'Unknown',
                    'registration_status' => $registration->status,
                    'registered_at' => $registration->registered_at,
                    'tasks_assigned' => $tasksAssigned,
                    'tasks_completed' => $tasksCompleted,
                    'total_hours' => $totalHours
                ];
            });

        return response()->json([
            'campaign_id' => (int) $campaignId,
            'disaster_id' => (int) $campaign->disaster_id,
            'ngo_id' => $ngoId,
            'volunteers' => $volunteers
        ]);
    }

    public function flagVolunteer(Request $request)
    {
        $user = $request->user();
        $volunteerId = $request->input('volunteer_id');
        $campaignId = $request->input('campaign_id');

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Verify that this campaign belongs to the NGO
        $campaign = \App\Models\DisasterCampaignAssignment::where('id', $campaignId)
            ->where('ngo_id', $ngoId)
            ->first();

        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
        }

        // Find the volunteer registration
        $registration = VolunteerRegistration::where('user_id', $volunteerId)
            ->where('campaign_id', $campaignId)
            ->where('ngo_id', $ngoId)
            ->first();

        if (!$registration) {
            return response()->json(['error' => 'Volunteer registration not found'], 404);
        }

        // Flag the volunteer
        $registration->status = 'flagged';
        $registration->save();

        return response()->json([
            'success' => true,
            'message' => 'Volunteer has been flagged successfully',
            'volunteer_id' => $volunteerId,
            'campaign_id' => $campaignId,
            'new_status' => 'flagged'
        ]);
    }
}
