<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ngo;
use App\Models\VolunteerRegistration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VolunteerRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'ngo_id'      => ['required', 'exists:ngos,id'],
            'campaign_id' => [
                'required',
                // Only allow campaign IDs that are assigned to this NGO
                Rule::exists('disaster_campaign_assignments', 'id')
                    ->where(fn ($q) => $q->where('ngo_id', $request->ngo_id)),
            ],
            'skills'       => ['nullable', 'string'],
        ]);

        $volunteer = VolunteerRegistration::create([
            'user_id'      => $user->id,
            'campaign_id'  => $data['campaign_id'],
            'ngo_id'       => $data['ngo_id'],
            'status'       => 'approved',
            'availability' => true,
            'skills'       => $request->skills,
            'registered_at'=> now(),
        ]);

        $user->volunteer = true;
        $user->save();

        return response()->json([
            'message' => 'Volunteer request submitted successfully.',
            'status' => $volunteer->status
        ]);
    }

    public function index()
    {
        $campaignId = request()->get('campaign_id');
        $allRegs = VolunteerRegistration::with('user')
            ->where('status', 'approved')
            ->where('availability', true)
            ->orderByDesc('created_at')
            ->get();

        // Group by user, pick latest registration
        $latestRegs = $allRegs->groupBy('user_id')->map(function($group) {
            return $group->first();
        });

        $volunteers = $latestRegs->filter(function ($v) use ($campaignId) {
            // Only include if latest registration is for this campaign
            if ($campaignId && $v->campaign_id != $campaignId) return false;
            // Check for active tasks
            $hasActiveTask = \App\Models\VolunteerTaskLog::where('volunteer_id', $v->user_id)
                ->whereIn('status', ['assigned', 'started'])
                ->exists();
            return !$hasActiveTask;
        });

        $volunteers = $volunteers->map(function ($v) {
            return [
                'id' => $v->user->id,
                'name' => $v->user->name,
                'email' => $v->user->email,
                'skills' => $v->skills,
            ];
        })->values();

        // Handle empty result gracefully
        if ($volunteers->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($volunteers);
    }

    public function resign(Request $request)
    {
        $user = $request->user();

        $volunteer = VolunteerRegistration::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        if (!$volunteer) {
            return response()->json(['message' => 'No active volunteer registration found.'], 404);
        }

        // Check for active, not checked out, and not expired tasks
        $activeTasks = \App\Models\VolunteerTaskLog::where('volunteer_id', $user->id)
            ->where(function($q) {
                $q->whereNull('check_out')
                  ->where('task_end_time', '>', now());
            })
            ->count();

        if ($activeTasks > 0) {
            return response()->json(['error' => 'Cannot resign while you have active tasks. Please contact with your NGO task validator.'], 403);
        }

        // Only set user.volunteer to false, do not change registration status
        $user->volunteer = false;
        $user->save();

        return response()->json(['message' => 'You have successfully resigned from volunteering.']);
    }
}
