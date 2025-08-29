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
        if (!$staff) {
            abort(403, 'No NGO staff record found.');
        }
        $data = $request->validate([
            'campaign_id' => 'required|exists:disaster_campaign_assignments,id',
            'amount_received_financial' => 'required|numeric|min:0',
            'amount_used_financial' => 'required|numeric|min:0',
            'amount_received_medical' => 'required|numeric|min:0',
            'amount_used_medical' => 'required|numeric|min:0',
            'amount_received_resource' => 'required|numeric|min:0',
            'amount_used_resource' => 'required|numeric|min:0',
            'usage_breakdown' => 'nullable|string',
        ]);
        $report = DonationReport::create($data);

        // Set campaign status to inactive
        $campaign = \App\Models\DisasterCampaignAssignment::find($data['campaign_id']);
        if ($campaign) {
            $campaign->status = 'inactive';
            $campaign->save();

            // Check if any active campaigns remain for this disaster
            $activeCampaigns = \App\Models\DisasterCampaignAssignment::where('disaster_id', $campaign->disaster_id)
                ->where('status', 'active')
                ->count();
            if ($activeCampaigns === 0) {
                $disaster = \App\Models\Disaster::find($campaign->disaster_id);
                if ($disaster) {
                    $disaster->status = 'closed';
                    $disaster->save();
                }
            }
        }

        return response()->json([
            'message' => 'Donation report submitted and campaign ended successfully.',
            'report' => $report,
            'campaign' => $campaign
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
