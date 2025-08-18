<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\DonationReport;
use App\Models\AidSupport;

class DonationReportController extends Controller
{
    //
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'ngo_staff') {
            abort(403, 'Unauthorized');
        }

        $staff = $user->ngoStaff; // hasOne NgoStaff
        if (!$staff || !$staff->ngo_id) {
            abort(403, 'No parent NGO mapped for this staff.');
        }

        $data = $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'aid_type' => 'required|in:financial,medical,resource',
            'amount_received' => 'required|numeric|min:0',
            'amount_used' => 'required|numeric|min:0',
            'usage_breakdown' => 'nullable|string',
            'reporting_period' => 'required|string',
            'confirmed' => 'boolean'
        ]);

        $data['ngo_id'] = $staff->ngo_id;

        $report = DonationReport::create($data);

        return response()->json([
            'message' => 'Donation report submitted successfully.',
            'report' => $report
        ], 201);
    }

    public function userReportForDisaster(Request $request, $disasterId)
    {
        $reports = DonationReport::with('disaster')
            ->where('disaster_id', $disasterId)
            ->where('confirmed', true)
            ->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'No confirmed reports available for this disaster.',
                'reports' => [],
            ]);
        }

        return response()->json([
            'message' => 'Report available.',
            'reports' => $reports,
        ]);
    }

    public function allDonationReports()
    {
        $reports = DonationReport::with('disaster')
            ->where('confirmed', true)
            ->get()
            ->groupBy('disaster_id')
            ->map(function ($group) {
                return [
                    'disaster' => $group[0]->disaster,
                    'reports' => $group->values(),
                ];
            })
            ->values(); // reset keys

        return response()->json($reports);
    }



}
