<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\VolunteerTaskLog;
use App\Models\AidRequest;


class VolunteerTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

    $tasks = Task::where('assigned_to', $user->id)
        ->with('campaign.disaster')
            ->get()
            ->map(function ($task) use ($user) {
                return [
                    'task_id' => $task->id,
            'disaster' => $task->campaign->disaster->name ?? '',
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
    public function assignAidRequest(Request $request, $id)
    {
        // Find aid request first
        $aidRequest = AidRequest::findOrFail($id);

        if ($aidRequest->status !== 'pending') {
            return response()->json(['error' => 'This aid request is not available for assignment'], 400);
        }

        // Check aid type
        if ($aidRequest->aid_type === 'financial') {
            // For financial aid, no task creation — directly accept
            $aidRequest->status = 'assigned';
            $aidRequest->save();

            return response()->json([
                'message' => 'Financial aid request accepted successfully!',
                'aid_request' => $aidRequest
            ]);
        }

        // For other aid types -> validate volunteer & task inputs
        $request->validate([
            'volunteer_id' => 'required|exists:users,id',
            'start_time'   => 'required|date',
            'end_time'     => 'required|date|after:start_time',
            'location'     => 'required|string',
            'campaign_id'  => 'required|exists:disaster_campaign_assignments,id'
        ]);

        if ($aidRequest->status === 'assigned') {
            return response()->json(['error' => 'Aid request already assigned'], 400);
        }

        // Create a new Task from the aid request
        $user = $request->user();
        $ngo_id = $user->ngoStaff->ngo_id;
    $task = Task::create([
            'campaign_id'    => $request->campaign_id,
            'aid_request_id' => $aidRequest->id,
            'assigned_to'    => $request->volunteer_id,
            'created_by'     => $ngo_id, // NGO user
            'aid_type'       => $aidRequest->aid_type,
            'location'       => $request->location,
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
            'urgency'        => $aidRequest->urgency,
            'description'    => $aidRequest->description,
            'status'         => 'assigned',
        ]);

        // Update aid request status
        $aidRequest->status = 'assigned';
        $aidRequest->save();

        return response()->json([
            'message' => 'Aid request assigned and task created successfully!',
            'task' => $task,
            'aid_request' => $aidRequest
        ]);
    }


    // Reject an aid request with remarks by NGO
    public function rejectAidRequest(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $aidRequest = AidRequest::findOrFail($id);

        $aidRequest->status = 'rejected';
        $aidRequest->ngo_remarks = $request->remarks;
        $aidRequest->save();

        return response()->json(['message' => 'Aid request rejected successfully!', 'aid_request' => $aidRequest]);
    }


    // Create a standalone task (independent of aid requests)
    public function createStandaloneTask(Request $request)
    {

        $request->validate([
            'campaign_id' => 'required|exists:disaster_campaign_assignments,id',
            'volunteer_id' => 'required|exists:users,id',
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
            'campaign_id' => $request->campaign_id,
            'assigned_to' => $request->volunteer_id,
            'created_by' => $ngo_id,
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
