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

    // Assign a task to a volunteer by NGO
    public function assignTask(Request $request, $id)
    {
        $request->validate([
            'volunteer_id' => 'required|exists:users,id',
        ]);

        $task = Task::findOrFail($id);

        if ($task->status === 'assigned') {
            return response()->json(['error' => 'Task is already assigned'], 400);
        }

        $task->assigned_to = $request->volunteer_id;
        $task->status = 'assigned';
        $task->save();

        return response()->json(['message' => 'Task assigned successfully!', 'task' => $task]);
    }

    // Reject a task with remarks by NGO
    public function rejectTask(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $task = Task::findOrFail($id);

        $task->status = 'rejected';
        $task->ngo_remarks = $request->remarks;
        $task->save();

        return response()->json(['message' => 'Task rejected successfully!', 'task' => $task]);
    }

    // Create a standalone task (independent of aid requests)
    public function createStandaloneTask(Request $request)
    {

        $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'volunteer_id' => 'required|exists:users,id',
            'task_type' => 'required|in:aid_request,delivery', // Can be aid request or delivery
            'location' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'aid_type' => 'required|in:financial,medical,resource',
            'urgency' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
        ]);

        $user = $request->user();
        $ngo_id = $user->ngoStaff->ngo_id;
        $task = Task::create([
            'disaster_id' => $request->disaster_id,
            'assigned_to' => $request->volunteer_id,
            'created_by' => $ngo_id,
            'task_type' => $request->task_type,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'aid_type' => $request->aid_type,
            'urgency' => $request->urgency,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Standalone task created successfully!',
            'task' => $task,
        ]);
    }
}
