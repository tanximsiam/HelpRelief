<?php

namespace App\Http\Controllers;

use App\Models\Ngo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    
    public function myNgoReport(Request $request)
    {
    $user = $request->user(); // or Auth::user()

    // Get NGO ID through ngo_staff (assuming relationship exists)
    $ngoId = $user->ngoStaff->ngo_id ?? null;

    if (!$ngoId) {
        return response()->json([
            'status' => 'error',
            'message' => 'You are not associated with any NGO.',
        ], 403);
    }
    
    // Fetch that NGO with required relationships
    $ngo = Ngo::withCount([
            'aidSupports',
            'volunteers',
            'tasks',
        ])
        ->with('disasterCampaignAssignments.disaster:id,title')
        ->findOrFail($ngoId);

    // Manually calculate aid_requested
    $aidRequested = 0;
    foreach ($ngo->volunteers as $volunteer) {
        if (method_exists($volunteer, 'aidRequests')) {
            $aidRequested += $volunteer->aidRequests()->count();
        }
    }
    
    // Return single NGO report
    return response()->json([
        'status' => 'success',
        'data' => [
            'ngo_id'           => $ngo->id,
            'ngo_name'         => $ngo->name,
            'email'            => $ngo->email,
            'phone'            => $ngo->phone,
            'website'          => $ngo->website,
            'description'      => $ngo->description,
            'based_in'         => $ngo->based_in,
            'registration_no'  => $ngo->registration_no,
            'established_year' => $ngo->established_year,
            'director_name'    => $ngo->director_name,
            'director_phone'   => $ngo->director_phone,
            'num_employees'    => $ngo->num_employees,
            'logo_url'         => $ngo->logo_url,
            'approved'         => $ngo->approved,
            'aid_requested'    => $aidRequested,
            'aid_supplied'     => $ngo->aid_supports_count,
            'volunteer_count'  => $ngo->volunteers_count,
            'tasks_assigned'   => $ngo->tasks_count,
            'disasters_engaged'=> $ngo->disasterCampaignAssignments->pluck('disaster.id')->unique()->values(),
        ]
    ]);
    }
    public function ngoReports()
    {
        // Load all NGOs with basic counts
        $ngos = Ngo::withCount([
            'aidSupports',
            'volunteers',
            'tasks',
        ])
        ->with([
            'disasterCampaignAssignments.disaster:id,title',
        ])
        ->get();

        // Manually calculate aid_requested count via related users
        $report = $ngos->map(function ($ngo) {
            // Count aid_requests via users who belong to this NGO
            $aidRequested = 0;

            foreach ($ngo->volunteers as $volunteer) {
                $aidRequested += $volunteer->aidRequests()->count();
            }

            return [
                'ngo_id'           => $ngo->id,
                'ngo_name'         => $ngo->name,
                'email'            => $ngo->email,
                'phone'            => $ngo->phone,
                'website'          => $ngo->website,
                'description'      => $ngo->description,
                'based_in'         => $ngo->based_in,
                'registration_no'  => $ngo->registration_no,
                'established_year' => $ngo->established_year,
                'director_name'    => $ngo->director_name,
                'director_phone'   => $ngo->director_phone,
                'num_employees'    => $ngo->num_employees,
                'logo_url'         => $ngo->logo_url,
                'approved'         => $ngo->approved,

                // Metrics
                'aid_requested'    => $aidRequested,
                'aid_supplied'     => $ngo->aid_supports_count,
                'volunteer_count'  => $ngo->volunteers_count,
                'tasks_assigned'   => $ngo->tasks_count,
                'disasters_engaged'=> $ngo->disasterCampaignAssignments->pluck('disaster.id')->unique()->values(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }
}
