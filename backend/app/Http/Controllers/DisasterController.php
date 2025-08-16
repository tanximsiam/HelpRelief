<?php

namespace App\Http\Controllers;

use App\Models\Disaster;
use Illuminate\Http\JsonResponse;

class DisasterController extends Controller
{
    // Return currently active disasters (status = 'active'). For now keep it simple.
    public function active(): JsonResponse
    {
        $disasters = Disaster::where('status', 'active')
            ->orderBy('occurred_at', 'desc')
            ->get(['id','name','type','location','severity','status']);
        return response()->json($disasters);
    }
}
