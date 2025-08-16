<?php

namespace App\Http\Controllers;

use App\Models\VolunteerTaskLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VolunteerTaskLogController extends Controller
{
    // List task logs (with optional filtering) GET /api/task-logs
    public function index(Request $request): JsonResponse
    {
        $query = VolunteerTaskLog::with([
            'task:id,disaster_id,task_type,location',
            'volunteer:id,name',
            'startVerifiedBy:id,name',
            'endVerifiedBy:id,name'
        ])->orderByDesc('check_in');

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->integer('task_id'));
        }
        if ($request->filled('volunteer_id')) {
            $query->where('volunteer_id', $request->integer('volunteer_id'));
        }
        if ($request->filled('disaster_id')) {
            $query->where('disaster_id', $request->integer('disaster_id'));
        }

    $logs = $query->limit(200)->get();
    return response()->json($logs);
    }
    // Check-in (start) a task: creates or updates a volunteer task log
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
            ]);

            $task = Task::findOrFail($validated['task_id']);
            $user = auth()->user();
            $assignedVolunteerId = $task->assigned_to ?? $user->id;
            $taskLog = VolunteerTaskLog::where('task_id', $validated['task_id'])->first();
            if (!$taskLog) {
                $taskLog = VolunteerTaskLog::create([
                    'task_id' => $validated['task_id'],
                    'volunteer_id' => $assignedVolunteerId,
                    'disaster_id' => $task->disaster_id,
                    'status' => 'assigned',
                    'check_in' => now(),
                    'check_out' => null,
                    'start_verified_by' => $user->id,
                    'end_verified_by' => null,
                    'report' => 'normal',
                ]);
            } elseif (!$taskLog->check_in) {
                $taskLog->update([
                    'volunteer_id' => $assignedVolunteerId = $task->assigned_to ?? $user->id,
                    'status' => 'started',
                    'check_in' => now(),
                    'start_verified_by' => $user->id,
                ]);
            }
            $taskLog->load(['task:id,disaster_id,task_type,location','volunteer:id,name','startVerifiedBy:id,name','endVerifiedBy:id,name']);
            return response()->json(['log' => $taskLog], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    // Check-out: End a task  POST /api/task-log/checkout
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'report' => 'nullable|in:normal,early_exit,abandoned,no_show',
            ]);

            $taskLog = VolunteerTaskLog::where('task_id', $validated['task_id'])->firstOrFail();
            $adminUser = auth()->user();
            if (!$taskLog->check_out) {
                $taskLog->update([
                    'status' => 'ended',
                    'check_out' => now(),
                    'end_verified_by' => $adminUser->id,
                    'report' => $validated['report'] ?? 'normal'
                ]);
            }
            $taskLog->load(['task:id,disaster_id,task_type,location','volunteer:id,name','startVerifiedBy:id,name','endVerifiedBy:id,name']);
            return response()->json(['log' => $taskLog], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

}
