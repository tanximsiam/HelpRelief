<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerTaskLog;
use App\Models\NgoStaff;
use App\Models\VolunteerRegistration;


class VolunteerReportController extends Controller
{
    public function aggregate(Request $request)
    {
        $user = $request->user();
        $campaignId = $request->query('campaign_id');
        $disasterId = $request->query('disaster_id'); // Keep for backward compatibility
        $reportType = $request->query('type', 'all'); // 'all' or 'tasks_only'

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Prioritize campaign_id over disaster_id
        if ($campaignId) {
            // Verify that this campaign belongs to the NGO
            $campaign = \App\Models\DisasterCampaignAssignment::where('id', $campaignId)
                ->where('ngo_id', $ngoId)
                ->first();
            
            if (!$campaign) {
                return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
            }
            
            $disasterId = $campaign->disaster_id;
        } elseif (!$disasterId) {
            return response()->json(['error' => 'Either campaign_id or disaster_id is required'], 400);
        }

        if ($reportType === 'tasks_only') {
            // Original logic - only volunteers with task assignments
            $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId);
                        if ($campaignId) {
                            $subQuery->where('campaign_id', $campaignId);
                        }
                        $subQuery->where('status', 'approved');
                    });
                });

            $totalVolunteers = $logs->distinct('volunteer_id')->count('volunteer_id');
            $tasksAssigned = $logs->count();
            $tasksCompleted = $logs->where('status', 'ended')->count();
            $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

            $totalHours = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId);
                        if ($campaignId) {
                            $subQuery->where('campaign_id', $campaignId);
                        }
                        $subQuery->where('status', 'approved');
                    });
                })
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->get()
                ->sum(function ($log) {
                    return round(($log->check_in->diffInSeconds($log->check_out)) / 3600, 2);
                });

            return response()->json([
                'disaster_id' => (int) $disasterId,
                'campaign_id' => $campaignId ? (int) $campaignId : null,
                'ngo_id' => $ngoId,
                'report_type' => 'tasks_only',
                'total_volunteers' => $totalVolunteers,
                'tasks_assigned' => $tasksAssigned,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $completionRate,
                'total_hours' => $totalHours
            ]);
        } else {
            // Enhanced logic - all registered volunteers + task statistics
            $volunteerQuery = VolunteerRegistration::where('ngo_id', $ngoId);
            
            if ($campaignId) {
                $volunteerQuery->where('campaign_id', $campaignId);
            } else {
                // Fallback to disaster_id filter if no campaign_id
                $volunteerQuery->where('disaster_id', $disasterId);
            }
            
            $volunteerStats = $volunteerQuery->selectRaw('
                    COUNT(*) as total_volunteers,
                    SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as active_volunteers,
                    SUM(CASE WHEN status = "flagged" THEN 1 ELSE 0 END) as flagged_volunteers
                ')
                ->first();

            // Task-related statistics for volunteers with tasks
            $taskStats = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId);
                        if ($campaignId) {
                            $subQuery->where('campaign_id', $campaignId);
                        }
                        $subQuery->where('status', 'approved');
                    });
                });

            $volunteersWithTasks = $taskStats->distinct('volunteer_id')->count('volunteer_id');
            $tasksAssigned = $taskStats->count();
            $tasksCompleted = $taskStats->where('status', 'ended')->count();
            $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

            $totalHours = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId);
                        if ($campaignId) {
                            $subQuery->where('campaign_id', $campaignId);
                        }
                        $subQuery->where('status', 'approved');
                    });
                })
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->get()
                ->sum(function ($log) {
                    return round(($log->check_in->diffInSeconds($log->check_out)) / 3600, 2);
                });

            return response()->json([
                'disaster_id' => (int) $disasterId,
                'campaign_id' => $campaignId ? (int) $campaignId : null,
                'ngo_id' => $ngoId,
                'report_type' => 'comprehensive',
                'total_volunteers' => $volunteerStats->total_volunteers ?? 0,
                'active_volunteers' => $volunteerStats->active_volunteers ?? 0,
                'flagged_volunteers' => $volunteerStats->flagged_volunteers ?? 0,
                'pending_volunteers' => 0, // No longer used
                'rejected_volunteers' => 0, // No longer used  
                'completed_volunteers' => 0, // No longer used
                'volunteers_with_tasks' => $volunteersWithTasks,
                'tasks_assigned' => $tasksAssigned,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $completionRate,
                'total_hours' => $totalHours
            ]);
        }
    }

    public function individual(Request $request)
    {
        $user = $request->user();
        $campaignId = $request->query('campaign_id');
        $disasterId = $request->query('disaster_id'); // Keep for backward compatibility
        $reportType = $request->query('type', 'all'); // 'all' or 'tasks_only'

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        // Prioritize campaign_id over disaster_id
        if ($campaignId) {
            // Verify that this campaign belongs to the NGO
            $campaign = \App\Models\DisasterCampaignAssignment::where('id', $campaignId)
                ->where('ngo_id', $ngoId)
                ->first();
            
            if (!$campaign) {
                return response()->json(['error' => 'Campaign not found or unauthorized'], 404);
            }
            
            $disasterId = $campaign->disaster_id;
        } elseif (!$disasterId) {
            return response()->json(['error' => 'Either campaign_id or disaster_id is required'], 400);
        }

        if ($reportType === 'tasks_only') {
            // Original logic - only volunteers with task assignments
            $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $campaignId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $campaignId) {
                        $subQuery->where('ngo_id', $ngoId);
                        if ($campaignId) {
                            $subQuery->where('campaign_id', $campaignId);
                        }
                        $subQuery->where('status', 'approved');
                    });
                })
                ->with('volunteer')
                ->get()
                ->groupBy('volunteer_id');

            $response = $logs->map(function ($group) {
                $first = $group->first();

                $hours = $group->sum(function ($log) {
                    if ($log->check_in && $log->check_out) {
                        return round((($log->check_in)->diffInSeconds($log->check_out)) / 3600, 2);
                    }
                    return 0;
                });

                $attendanceDays = $group->pluck('check_in')->filter()->map(fn($d) => $d->toDateString())->unique()->count();

                return [
                    'volunteer_id' => $first->volunteer_id,
                    'name' => $first->volunteer->name,
                    'tasks_assigned' => $group->count(),
                    'tasks_completed' => $group->where('status', 'ended')->count(),
                    'attendance_days' => $attendanceDays,
                    'first_checkin' => $group->min('check_in'),
                    'last_checkout' => $group->max('check_out'),
                    'total_hours' => $hours,
                ];
            })->values();

            return response()->json([
                'disaster_id' => (int) $disasterId,
                'campaign_id' => $campaignId ? (int) $campaignId : null,
                'ngo_id' => $ngoId,
                'report_type' => 'tasks_only',
                'volunteers' => $response
            ]);
        } else {
            // Enhanced logic - all registered volunteers with task data if available
            $volunteerQuery = VolunteerRegistration::where('ngo_id', $ngoId);
            
            if ($campaignId) {
                $volunteerQuery->where('campaign_id', $campaignId);
            } else {
                // Fallback to disaster_id filter if no campaign_id
                $volunteerQuery->where('disaster_id', $disasterId);
            }
            
            $volunteers = $volunteerQuery->with(['user:id,name,email,phone'])
                ->get()
                ->map(function ($registration) use ($disasterId) {
                    // Get task statistics for this volunteer
                    $taskLogs = VolunteerTaskLog::where('disaster_id', $disasterId)
                        ->where('volunteer_id', $registration->user_id)
                        ->get();

                    $totalHours = $taskLogs->sum(function ($log) {
                        if ($log->check_in && $log->check_out) {
                            return round(($log->check_in->diffInSeconds($log->check_out)) / 3600, 2);
                        }
                        return 0;
                    });

                    $attendanceDays = $taskLogs->pluck('check_in')->filter()->map(fn($d) => $d->toDateString())->unique()->count();

                    return [
                        'volunteer_id' => $registration->user_id,
                        'registration_id' => $registration->id,
                        'name' => $registration->user->name ?? 'Unknown',
                        'email' => $registration->user->email ?? '',
                        'phone' => $registration->user->phone ?? '',
                        'registration_status' => $registration->status,
                        'skills' => $registration->skills,
                        'availability' => $registration->availability,
                        'registered_at' => $registration->registered_at,
                        'notes' => $registration->notes,
                        'task_statistics' => [
                            'tasks_assigned' => $taskLogs->count(),
                            'tasks_completed' => $taskLogs->where('status', 'ended')->count(),
                            'tasks_in_progress' => $taskLogs->whereIn('status', ['assigned', 'accepted', 'started'])->count(),
                            'attendance_days' => $attendanceDays,
                            'total_hours' => $totalHours,
                            'first_checkin' => $taskLogs->min('check_in'),
                            'last_checkout' => $taskLogs->max('check_out'),
                        ]
                    ];
                });

            return response()->json([
                'disaster_id' => (int) $disasterId,
                'campaign_id' => $campaignId ? (int) $campaignId : null,
                'ngo_id' => $ngoId,
                'report_type' => 'comprehensive',
                'volunteers' => $volunteers
            ]);
        }
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
