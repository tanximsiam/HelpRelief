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
        
        return response()->json([
            'ngo_id' => $staff->ngo_id,
            'role' => 'ngo_staff',
            'designation' => $staff->designation,
            'privilege_role' => $staff->privilege_role,
            'ngo' => $staff->ngo
        ]);
    }

    public function destroy($id)
    {
        NgoStaff::findOrFail($id)->delete();
        return response()->json(['message' => 'Staff removed']);
    }
}
