<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ngo;
use App\Models\Disaster;
use App\Models\AidSupport;
use App\Models\Volunteer_registrations;
use Illuminate\Http\Request;


class VolunteerRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'ngo_id' => 'required|exists:users,id',
            'availability' => 'nullable|string',
            'skills' => 'nullable|string'
        ]);

        $volunteer = Volunteer_registrations::create([
            'user_id' => $user->id,
            'disaster_id' => $request->disaster_id,
            'ngo_id' => $request->ngo_id,
            'status' => 'pending',
            'availability' => $request->availability,
            'skills' => $request->skills,
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'Volunteer request submitted successfully.',
            'status' => $volunteer->status
        ]);
    }
}