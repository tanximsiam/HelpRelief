<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Disaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

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
            ->orderBy('start_date', 'desc')
            ->get(['id','name','disaster_type','location','severity','status','start_date']);
        return response()->json($disasters);
    }

    // Store a new disaster report (NGO staff)
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'disaster_type' => ['required', Rule::in(['flood','earthquake','storm','wildfire','drought','other'])],
            'location' => ['required','string','max:255'],
            'start_date' => ['required','date'],
            'severity' => ['required', Rule::in(['low','medium','high'])],
            'description' => ['nullable','string'],
        ]);

        $user = $request->user();
        // created_by expects ngo_staff id; fall back to user id if relationship not established
        $createdBy = method_exists($user, 'ngoStaff') && $user->ngoStaff ? $user->ngoStaff->id : $user->id;

        $disaster = Disaster::create([
            'name' => $validated['name'],
            'disaster_type' => $validated['disaster_type'],
            'location' => $validated['location'],
            'start_date' => $validated['start_date'],
            'severity' => $validated['severity'],
            // Set directly to active instead of pending
            'status' => 'active',
            'description' => $validated['description'] ?? null,
            'created_by' => $createdBy,
        ]);

        return response()->json($disaster, 201);
    }
}
