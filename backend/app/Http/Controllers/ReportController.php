<?php

namespace App\Http\Controllers;

use App\Models\Ngo;
use App\Models\AidSupport;
use App\Models\AidRequest;
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
                'volunteers',
                'tasks',
            ])
            ->with('disasterCampaignAssignments.disaster:id,name as title')
            ->findOrFail($ngoId);

        // Count aid requests where requester is a volunteer registered under this NGO
        $aidRequested = AidRequest::whereHas('requester.volunteerRegistration', function ($q) use ($ngoId) {
            $q->where('ngo_id', $ngoId);
        })->count();

        // Count all aid supplied to campaigns under this NGO (map by campaign_id)
        $campaignIds = $ngo->disasterCampaignAssignments->pluck('id')->all();
        $aidSupplied = 0;
        if (!empty($campaignIds)) {
            $aidSupplied = AidSupport::whereIn('campaign_id', $campaignIds)->count();
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
                'aid_supplied'     => $aidSupplied,
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
                'volunteers',
                'tasks',
            ])
            ->with([
                'disasterCampaignAssignments.disaster:id,name as title',
            ])
            ->get();

        // Manually calculate aid_requested count via related users
        $report = $ngos->map(function ($ngo) {
            // Count aid requests where requester is a volunteer registered under this NGO
            $aidRequested = AidRequest::whereHas('requester.volunteerRegistration', function ($q) use ($ngo) {
                $q->where('ngo_id', $ngo->id);
            })->count();

            // Count all aid supplied to campaigns under this NGO
            $campaignIds = $ngo->disasterCampaignAssignments->pluck('id')->all();
            $aidSupplied = 0;
            if (!empty($campaignIds)) {
                $aidSupplied = AidSupport::whereIn('campaign_id', $campaignIds)->count();
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
                'aid_supplied'     => $aidSupplied,
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
