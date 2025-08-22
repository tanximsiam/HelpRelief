<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AidNeedController extends Controller
{
    public function index()
    {
        // Get aid requests count grouped by disaster
        $requests = DB::table('aid_requests')
            ->select('disaster_id', DB::raw('COUNT(*) as request_count'))
            ->groupBy('disaster_id');

        // Get aid supports (tasks) count grouped by disaster and NGO
        $supports = DB::table('aid_supports')
            ->select('disaster_id', 'ngo_id', DB::raw('COUNT(*) as support_count'))
            ->groupBy('disaster_id', 'ngo_id');

        // Join with disaster_campaign_assignments to merge disaster_id and ngo_id
        $result = DB::table('disaster_campaign_assignments as dca')
            ->joinSub($requests, 'req', function ($join) {
                $join->on('dca.disaster_id', '=', 'req.disaster_id');
            })
            ->leftJoinSub($supports, 'sup', function ($join) {
                $join->on('dca.disaster_id', '=', 'sup.disaster_id')
                     ->on('dca.ngo_id', '=', 'sup.ngo_id');
            })
            ->select([
                'dca.disaster_id',
                'dca.ngo_id',
                'req.request_count',
                DB::raw('COALESCE(sup.support_count, 0) as support_count'),
                DB::raw('(req.request_count - COALESCE(sup.support_count, 0)) as aid_needed')
            ])
            ->orderBy('dca.disaster_id')
            ->orderBy('dca.ngo_id')
            ->get();

        return response()->json([
            'message' => 'Simplified aid need calculation',
            'data' => $result,
        ]);
    }
}
