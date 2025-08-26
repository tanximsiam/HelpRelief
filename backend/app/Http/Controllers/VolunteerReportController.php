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
        $disasterId = $request->query('disaster_id');
        $reportType = $request->query('type', 'all'); // 'all' or 'tasks_only'

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        if ($reportType === 'tasks_only') {
            // Original logic - only volunteers with task assignments
            $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $disasterId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $disasterId) {
                        $subQuery->where('ngo_id', $ngoId)
                                ->where('disaster_id', $disasterId)
                                ->whereIn('status', ['approved', 'active']);
                    });
                });

            $totalVolunteers = $logs->distinct('volunteer_id')->count('volunteer_id');
            $tasksAssigned = $logs->count();
            $tasksCompleted = $logs->where('status', 'ended')->count();
            $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

            $totalHours = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $disasterId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $disasterId) {
                        $subQuery->where('ngo_id', $ngoId)
                                ->where('disaster_id', $disasterId)
                                ->whereIn('status', ['approved', 'active']);
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
            $volunteerStats = VolunteerRegistration::where('disaster_id', $disasterId)
                ->where('ngo_id', $ngoId)
                ->selectRaw('
                    COUNT(*) as total_registered,
                    SUM(CASE WHEN status IN ("approved", "active") THEN 1 ELSE 0 END) as active_volunteers,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_volunteers,
                    SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_volunteers,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_volunteers
                ')
                ->first();

            // Task-related statistics for volunteers with tasks
            $taskStats = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $disasterId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $disasterId) {
                        $subQuery->where('ngo_id', $ngoId)
                                ->where('disaster_id', $disasterId)
                                ->whereIn('status', ['approved', 'active']);
                    });
                });

            $volunteersWithTasks = $taskStats->distinct('volunteer_id')->count('volunteer_id');
            $tasksAssigned = $taskStats->count();
            $tasksCompleted = $taskStats->where('status', 'ended')->count();
            $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

            $totalHours = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $disasterId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $disasterId) {
                        $subQuery->where('ngo_id', $ngoId)
                                ->where('disaster_id', $disasterId)
                                ->whereIn('status', ['approved', 'active']);
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
                'ngo_id' => $ngoId,
                'report_type' => 'comprehensive',
                'volunteer_registrations' => [
                    'total_registered' => $volunteerStats->total_registered ?? 0,
                    'active_volunteers' => $volunteerStats->active_volunteers ?? 0,
                    'pending_volunteers' => $volunteerStats->pending_volunteers ?? 0,
                    'rejected_volunteers' => $volunteerStats->rejected_volunteers ?? 0,
                    'completed_volunteers' => $volunteerStats->completed_volunteers ?? 0,
                ],
                'task_assignments' => [
                    'volunteers_with_tasks' => $volunteersWithTasks,
                    'tasks_assigned' => $tasksAssigned,
                    'tasks_completed' => $tasksCompleted,
                    'completion_rate' => $completionRate,
                    'total_hours' => $totalHours
                ]
            ]);
        }
    }

    public function individual(Request $request)
    {
        $user = $request->user();
        $disasterId = $request->query('disaster_id');
        $reportType = $request->query('type', 'all'); // 'all' or 'tasks_only'

        // Check if user is NGO staff
        $staff = NgoStaff::where('user_id', $user->id)->first();
        if (!$staff) {
            return response()->json(['error' => 'Unauthorized - NGO staff only'], 403);
        }

        $ngoId = $staff->ngo_id;

        if ($reportType === 'tasks_only') {
            // Original logic - only volunteers with task assignments
            $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
                ->whereHas('volunteer', function($query) use ($ngoId, $disasterId) {
                    $query->whereHas('volunteerRegistrations', function($subQuery) use ($ngoId, $disasterId) {
                        $subQuery->where('ngo_id', $ngoId)
                                ->where('disaster_id', $disasterId)
                                ->whereIn('status', ['approved', 'active']);
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
                'ngo_id' => $ngoId,
                'report_type' => 'tasks_only',
                'volunteers' => $response
            ]);
        } else {
            // Enhanced logic - all registered volunteers with task data if available
            $volunteers = VolunteerRegistration::where('disaster_id', $disasterId)
                ->where('ngo_id', $ngoId)
                ->with(['user:id,name,email,phone'])
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
                'ngo_id' => $ngoId,
                'report_type' => 'comprehensive',
                'volunteers' => $volunteers
            ]);
        }
    }
}
