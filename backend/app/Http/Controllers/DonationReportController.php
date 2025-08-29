<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonationReport;
use App\Models\AidSupport;

class DonationReportController extends Controller
{
    // Store a new donation report
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'ngo_staff') {
            abort(403, 'Unauthorized');
        }
        $staff = $user->ngoStaff; // hasOne NgoStaff
        if (!$staff || !$staff->campaign_id) {
            abort(403, 'No parent campaign mapped for this staff.');
        }
        $data = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'amount_received_financial' => 'required|numeric|min:0',
            'amount_used_financial' => 'required|numeric|min:0',
            'amount_received_medical' => 'required|numeric|min:0',
            'amount_used_medical' => 'required|numeric|min:0',
            'amount_received_resource' => 'required|numeric|min:0',
            'amount_used_resource' => 'required|numeric|min:0',
            'usage_breakdown' => 'nullable|string',
        ]);
        $data['campaign_id'] = $staff->campaign_id;
        $report = DonationReport::create($data);
        return response()->json([
            'message' => 'Donation report submitted successfully.',
            'report' => $report
        ], 201);
    }

    // Get all donation reports grouped by campaign
    public function reportsByCampaign(Request $request)
    {
        $reports = DonationReport::with(['campaign.disaster', 'campaign.ngo'])
            ->get()
            ->groupBy('campaign_id')
            ->map(function ($group) {
                $campaign = $group[0]->campaign;
                return [
                    'campaign' => [
                        'id' => $campaign->id,
                        'name' => $campaign->id . ' - ' . ($campaign->disaster->name ?? 'Unknown Disaster') . ' / ' . ($campaign->ngo->name ?? 'Unknown NGO'),
                        'disaster' => $campaign->disaster->name ?? null,
                        'ngo' => $campaign->ngo->name ?? null,
                    ],
                    'reports' => $group->values(),
                ];
            })
            ->values();
        return response()->json($reports);
    }

    // Get all donation reports grouped by campaign (legacy)
    public function allDonationReports()
    {
        $reports = DonationReport::with('campaign')
            ->get()
            ->groupBy('campaign_id')
            ->map(function ($group) {
                return [
                    'campaign' => $group[0]->campaign,
                    'reports' => $group->values(),
                ];
            })
            ->values(); // reset keys
        return response()->json($reports);
    }
}
