<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterCampaignAssignment;
use App\Models\NgoStaff;



class CampaignController extends Controller
{
    // List all active campaigns (for volunteers/general users)
    public function index()
    {
        $campaigns = DisasterCampaignAssignment::with('disaster', 'ngo')
            ->where('status', 'active')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'name' => $assignment->disaster->name . 'Campaign',
                    'disaster_id' => $assignment->disaster_id,
                    'ngo_name' => $assignment->ngo->name ?? 'Unknown NGO',
                ];
            });

        return response()->json($campaigns);
    }

    // List NGO's own active campaigns (for ngo_staff)
    public function myCampaigns(Request $request)
    {
        $user = $request->user();

        // Find the NGO staff record for the user
        $staff = NgoStaff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'UnAuthorized'], 403);
        }

        $ngoId = $staff->ngo_id;
        // if (True) {
        //     return response()->json($staff);
        // }

        $campaigns = DisasterCampaignAssignment::with('disaster', 'ngo')
            ->where('ngo_id', $ngoId)
            ->where('status', 'active')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'name' => $assignment->disaster->name . ' Campaign',
                    'disaster_id' => $assignment->disaster_id,
                    'ngo_name' => $assignment->ngo->name ?? 'Unknown NGO',
                ];
            });

        return response()->json($campaigns);
    }
}
