<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ngo;
use App\Models\VolunteerRegistration;
use Illuminate\Http\Request;

class VolunteerRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'campaign_id' => 'required|exists:disaster_campaign_assignments,id',
            'ngo_id' => 'required|exists:ngos,id',
            'availability' => 'nullable|boolean',
            'skills' => 'nullable|string'
        ]);

        $volunteer = VolunteerRegistration::create([
            'user_id' => $user->id,
            'campaign_id' => $request->campaign_id,
            'ngo_id' => $request->ngo_id,
            'status' => 'pending',
            'availability' => $request->availability ?? true,
            'skills' => $request->skills,
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'Volunteer request submitted successfully.',
            'status' => $volunteer->status
        ]);
    }

    public function index()
    {
        $volunteers = VolunteerRegistration::with('user')
            ->where('status', 'approved')
            ->where('availability', true)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->user->id,
                    'name' => $v->user->name,
                    'email' => $v->user->email,
                    'skills' => $v->skills,
                ];
            });

        return response()->json($volunteers);
    }
}
