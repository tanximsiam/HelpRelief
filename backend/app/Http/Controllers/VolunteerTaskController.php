<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\VolunteerTaskLog;


class VolunteerTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::where('assigned_to', $user->id)
            ->with('disaster')
            ->get()
            ->map(function ($task) use ($user) {
                return [
                    'task_id' => $task->id,
                    'disaster' => $task->disaster->name ?? '',
                    'location' => $task->location,
                    'aid_type' => $task->aid_type,
                    'urgency' => $task->urgency,
                    'start_time' => $task->start_time,
                    'status' => $task->status,
                    // 'status' => $task->logs->first()->status ?? 'unassigned', // Default to 'unassigned' if no logs exist

                ];
            });

        return response()->json($tasks);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,ended'
        ]);

        
        $volunteerId = $request->user()->id;

        // Fetch log entry for this volunteer's task
        $log = VolunteerTaskLog::where('task_id', $id)
            ->where('volunteer_id', $volunteerId)
            ->latest() // in case multiple logs exist
            ->first();

        if (!$log) {
            return response()->json(['error' => 'Task not assigned to this volunteer.'], 403);
        }

        $currentStatus = $log->status;
        $newStatus = $request->status;

        $allowedTransitions = [
            'assigned' => ['accepted'],
            'accepted' => ['ended'],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json(['error' => 'Invalid transition'], 403);
        }

        $log->status = $newStatus;

        // Optional: set timestamps
        if ($newStatus === 'accepted') {
            $log->check_in = now();
        } elseif ($newStatus === 'ended') {
            $log->check_out = now();
        }

        $log->save();

        return response()->json(['message' => 'Status updated']);
    }

}
