<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
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
=======
use Illuminate\Http\Request;
use App\Models\Disaster;

class DisasterController extends Controller
{
    // Show active disasters
    public function index()
    {
        $activeDisasters = Disaster::where('status', 'active')->get();
        return response()->json($activeDisasters);

    }

  
    public function active(): JsonResponse
    {
        $disasters = Disaster::where('status', 'active')
            ->orderBy('occurred_at', 'desc')
            ->get(['id','name','type','location','severity','status']);
        return response()->json($disasters);
    }
}
