<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerTaskLog;


class VolunteerReportController extends Controller
{
    public function aggregate(Request $request)
    {
        $disasterId = $request->query('disaster_id');

        $logs = VolunteerTaskLog::where('disaster_id', $disasterId);

        $totalVolunteers = $logs->distinct('volunteer_id')->count('volunteer_id');
        $tasksAssigned = $logs->count();
        $tasksCompleted = $logs->where('status', 'ended')->count();
        $completionRate = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100, 2) : 0;

        $totalHours = VolunteerTaskLog::where('disaster_id', $disasterId)
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get()
            ->sum(function ($log) {
                return round(($log->check_out->diffInSeconds($log->check_in)) / 3600, 2);
            });

        return response()->json([
            'disaster_id' => (int) $disasterId,
            'total_volunteers' => $totalVolunteers,
            'tasks_assigned' => $tasksAssigned,
            'tasks_completed' => $tasksCompleted,
            'completion_rate' => $completionRate,
            'total_hours' => $totalHours
        ]);
    }

    public function individual(Request $request)
    {
        $disasterId = $request->query('disaster_id');

        $logs = VolunteerTaskLog::where('disaster_id', $disasterId)
            ->with('volunteer')
            ->get()
            ->groupBy('volunteer_id');

        $response = $logs->map(function ($group) {
            $first = $group->first();

            $hours = $group->sum(function ($log) {
                if ($log->check_in && $log->check_out) {
                    return round(($log->check_out->diffInSeconds($log->check_in)) / 3600, 2);
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

        return response()->json($response);
    }
}
