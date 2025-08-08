<?php

namespace App\Http\Controllers;

use App\Models\VolunteerTaskLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VolunteerTaskLogController extends Controller
{
    //Admin check-in: Start a task
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
            ]);

            $task = Task::findOrFail($validated['task_id']);
            $adminUser = auth()->user();

            // Create new task log entry with default 'assigned' status and only check-in info
            $taskLog = VolunteerTaskLog::create([
                'task_id' => $validated['task_id'],
                'volunteer_id' => null,
                'disaster_id' => $task->disaster_id,
                'status' => 'assigned',
                'check_in' => now(),
                'check_out' => null,
                'expected_end' => null,
                'start_verified_by' => $adminUser->id,
                'end_verified_by' => null,
                'report' => 'normal',
            ]);

            return response()->json([
                'message' => 'Task checked in successfully',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    //Admin check-out: End a task  POST /api/task-log/checkout
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'report' => 'nullable|in:normal,early_exit,abandoned,no_show',
            ]);

            $taskLog = VolunteerTaskLog::where('task_id', $validated['task_id'])->firstOrFail();

            // Perform check-out
            $adminUser = auth()->user();
            $taskLog->update([
                'status' => 'ended',
                'check_out' => now(),
                'end_verified_by' => $adminUser->id,
                'report' => $validated['report'] ?? 'normal'
            ]);

            return response()->json([
                'message' => 'Task checked out successfully',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Volunteer status update: Accept or mark as ended
     * PATCH /api/task-log/status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'status' => 'required|in:accepted,ended',
            ]);

            $volunteer = auth()->user();
            // Volunteer accepting the task
            if ($validated['status'] === 'accepted') {
                $taskLog = VolunteerTaskLog::where('task_id', $validated['task_id'])
                    ->where('status', 'assigned')
                    ->whereNull('volunteer_id')
                    ->firstOrFail();
                // Volunteer assignment recorded in VolunteerTaskLog
                // Update log status and set volunteer_id
                $taskLog->update([
                    'status' => 'accepted',
                    'volunteer_id' => $volunteer->id
                ]);
                return response()->json([
                    'message' => 'Task accepted successfully',
                ], 200);
            }

            // Volunteer ending the task
            $taskLog = VolunteerTaskLog::where([
                'task_id' => $validated['task_id'],
                'volunteer_id' => $volunteer->id
            ])->firstOrFail();

            // Validate status transitions
            if ($validated['status'] === 'ended' && !in_array($taskLog->status, ['accepted', 'started'])) {
                return response()->json([
                    'message' => 'Task can only be marked as ended if it is accepted or started',
                    'current_status' => $taskLog->status
                ], 400);
            }

            // Update status
            $taskLog->update(['status' => 'ended']);

            return response()->json([
                'message' => 'Task status updated successfully',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
