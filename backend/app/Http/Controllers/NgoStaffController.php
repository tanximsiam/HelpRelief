<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgoStaff;

class NgoStaffController extends Controller
{
    /**
     * Get all NGO staff records (admin use)
     */
    public function index()
    {
        return NgoStaff::with(['user', 'ngo'])->get();
    }

    /**
     * Get current user's NGO staff information
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $staff = NgoStaff::with(['user', 'ngo'])
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
            return response()->json(['ngo_id' => null, 'role' => null], 200);
        }

        // Get the current employee count for this NGO
        $employeeCount = NgoStaff::where('ngo_id', $staff->ngo_id)->count();

        // Add employee count to the NGO data
        $ngoData = $staff->ngo->toArray();
        $ngoData['current_employee_count'] = $employeeCount;

        return response()->json([
            'ngo_id' => $staff->ngo_id,
            'role' => 'ngo_staff',
            'designation' => $staff->designation,
            'privilege_role' => $staff->privilege_role,
            'ngo' => $ngoData
        ]);
    }

    /**
     * Get privilege_role for logged-in NGO staff
     */
    public function privilegeRole(Request $request)
    {
        $user = $request->user();
        $ngoStaff = NgoStaff::where('user_id', $user->id)->first();
        if (!$ngoStaff) {
            return response()->json(['error' => 'Not an NGO staff'], 404);
        }
        return response()->json([
            'privilege_role' => $ngoStaff->privilege_role,
            'ngo_id' => $ngoStaff->ngo_id,
        ]);
    }

    public function destroy($id)
    {
        NgoStaff::findOrFail($id)->delete();
        return response()->json(['message' => 'Staff removed']);
    }
}
