<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ngo;
use App\Models\NgoStaff;
use Illuminate\Support\Facades\Log;



class NgoController extends Controller
{
    //
    public function index()
    {
        return Ngo::all();
    }

    public function updateNgo(Request $request, $ngoId)
    {
        // Get current user
        $currentUserId = $request->user()->id;

        // Check if user is staff of this NGO
        $staff = NgoStaff::where('user_id', $currentUserId)
                         ->where('ngo_id', $ngoId)
                         ->first();

        if (!$staff) {
            return response()->json([
                'error' => 'Unauthorized - only NGO staffs can update NGO profile'
            ], 403);
        }

        // Validate current password
        $staffUser = \App\Models\User::find($staff->user_id);
        if (! $staffUser || !isset($staffUser->password)) {
            return response()->json([
                'error' => 'Staff authentication error.'
            ], 403);
        }
        if (!\Illuminate\Support\Facades\Hash::check($request->input('current_password'), $staffUser->password)) {
            return response()->json([
                'error' => 'Current password is incorrect.'
            ], 403);
        }

        // Validate other NGO fields
        $data = $request->validate([
            'name' => 'string|sometimes',
            'description' => 'string|sometimes',
            'phone' => 'string|sometimes',
            'based_in' => 'string|sometimes',
            'website' => 'url|sometimes',
            'director_name' => 'string|sometimes',
            'director_phone' => 'string|sometimes',
        ]);

        // If no data provided at all
        if (empty($data)) {
            return response()->json([
                'message' => 'No changes provided.'
            ], 200);
        }

        // Find NGO profile
        $ngoProfile = Ngo::findOrFail($ngoId);

        // Update and save directly
        $ngoProfile->update($data);

        return response()->json([
            'message'     => 'NGO profile updated successfully',
            'ngo_profile' => $ngoProfile->fresh(),
        ]);
    }

    public function show($ngoId)
    {
        $ngo = Ngo::findOrFail($ngoId);

        // Additional authorization check (e.g., only NGO staff for their NGO)
        // Example: if (auth()->user()->ngo_id !== $ngoId) { abort(403); }

        return response()->json([
            'id' => $ngo->id,
            'name' => $ngo->name,
            'description' => $ngo->description,
            'phone' => $ngo->phone,
            'based_in' => $ngo->based_in,
            'cause_focus' => $ngo->cause_focus,
            'website' => $ngo->website,
            'registration_no' => $ngo->registration_no,
            'established_year' => $ngo->established_year,
            'director_name' => $ngo->director_name,
            'director_phone' => $ngo->director_phone,
            'num_employees' => $ngo->num_employees,
        ]);
    }
}
