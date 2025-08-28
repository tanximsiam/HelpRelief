<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\DisasterCampaignAssignment;

class TaskController extends Controller
{
    /**
     * List tasks for a given campaign (NGO staff scope)
     */
    public function campaignTasks(Request $request, $campaignId)
    {
    // Attempt to load campaign; if absent but tasks exist we still proceed (helps when seed data references deleted assignments)
    $campaign = DisasterCampaignAssignment::find($campaignId);

        $query = Task::with(['assignedTo:id,name'])
            ->where('campaign_id', $campaignId)
            ->orderBy('start_time', 'asc');

        $tasks = $query->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'status' => $task->status,
                'urgency' => $task->urgency,
                'aid_type' => $task->aid_type,
                'location' => $task->location,
                'start_time' => $task->start_time,
                'end_time' => $task->end_time,
                'assigned_to' => $task->assigned_to,
                'assigned_to_name' => optional($task->assignedTo)->name,
            ];
        });

        if (!$campaign && $tasks->isEmpty()) {
            return response()->json(['error' => 'Campaign and tasks not found'], 404);
        }

        return response()->json([
            'campaign_found' => (bool)$campaign,
            'count' => $tasks->count(),
            'tasks' => $tasks,
        ]);
    }
}
