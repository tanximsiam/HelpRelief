<?php

namespace App\Http\Controllers;

use App\Models\Ngo;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function ngoReports()
    {
        $ngos = Ngo::withCount([
            'aidRequests',
            'aidSupports',
            'volunteers',
            'tasks',
        ])
        ->with([
            'disasterCampaignAssignments.disaster:id,title',
        ])
        ->get();

        $report = $ngos->map(function ($ngo) {
            $aidNeeded = $ngo->aid_requests_count - $ngo->aid_supports_count;

            return [
                'ngo_id' => $ngo->id,
                'ngo_name' => $ngo->name,
                'email' => $ngo->email,
                'phone' => $ngo->phone,
                'aid_requested' => $ngo->aid_requests_count,
                'aid_supplied' => $ngo->aid_supports_count,
                'aid_needed' => $aidNeeded,
                'volunteer_count' => $ngo->volunteers_count,
                'tasks_assigned' => $ngo->tasks_count,
                'disasters_engaged' => $ngo->disasterCampaignAssignments->pluck('disaster.title')->unique()->values(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }
}
