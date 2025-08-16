<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ngo;
use App\Models\NgoStaff;


class NgoController extends Controller
{
    //
    public function index()
    {
        return Ngo::all();
    }

    public function updateNgo(Request $request, $ngoId)
    {
        $user = $request->user();

        // Futher deployment note: Where only admin from NGO_staff can update NGO profile
        $staff = NgoStaff::where('user_id', $user->id)
            ->where('ngo_id', $ngoId)
            // ->where('priviledged_role', 'admin')
            ->first();
            
        if (! $staff) {
            return response()->json([
                'error' => 'Unauthorized - only NGO staffs can update NGO profile'
            ], 403);
        }

        // Validate allowed NGO fields
        $data = $request->validate([
            'name' => 'string|sometimes',
            'description' => 'string|sometimes',
            'phone' => 'string|sometimes',
            'based_in' => 'string|sometimes',
            'cause_focus' => 'string|sometimes',
            'website' => 'nullable|url',

            'registration_no' => 'string|sometimes',
            'established_year' => 'integer|sometimes|min:1800|max:' . date('Y'),
            'director_name' => 'string|sometimes',
            'director_phone' => 'string|sometimes',
            'num_employees' => 'integer|sometimes|min:0',
            'logo_url' => 'nullable|url',

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
}
