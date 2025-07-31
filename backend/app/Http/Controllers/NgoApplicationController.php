<?php

namespace App\Http\Controllers;
use App\Models\NgoApplication;
use App\Models\Ngo;
use App\Models\NgoInviteLink;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

class NgoApplicationController extends Controller
{
    //
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'organization' => 'required|string',
            'contact_person' => 'required|string',
            'designation' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'description' => 'required|string',
            'based_in' => 'required|string',
        ]);

        $application = NgoApplication::create($validated);

        return response()->json([
            'message' => 'Application submitted successfully',
            'data' => $application,
        ], 201);
    }

    // Admin listing
    public function index()
    {
        return NgoApplication::orderBy('created_at', 'desc')->get();
    }

    // Admin approves application
    public function approve($id)
    {
        $application = NgoApplication::findOrFail($id);

        $application->status = 'approved';
        $application->save();

        // Create NGO from application
        $ngo = Ngo::create([
            'name' => $application->organization,
            'description' => $application->description,
            'email' => $application->email,
            'phone' => $application->phone,
            'based_in' => $application->based_in,
            'approved' => true,
        ]);

        $invite = NgoInviteLink::create([
            'ngo_id' => $ngo->id,
            'token' => Str::random(32),
            'privilege_role' => 'ngo_admin',
            'is_primary' => true,
        ]);

        return response()->json([
            'message' => 'NGO Application approved and NGO created',
            'ngo_id' => $ngo->id,
            'invite_link' => url('/auth/redirect?token=' . $invite->token)
        ]);
    }

    // Admin rejects application
    public function reject($id)
    {
        $application = NgoApplication::findOrFail($id);
        $application->status = 'rejected';
        $application->save();

        return response()->json(['message' => 'Application rejected']);
    }
}
