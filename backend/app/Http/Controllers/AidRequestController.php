<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AidRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AidRequestController extends Controller
{
    // Display a listing of aid requests.

    public function index(Request $request): JsonResponse
    {
        $query = AidRequest::with('requester');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by urgency if provided
        if ($request->has('urgency')) {
            $query->byUrgency($request->urgency);
        }

        // Filter by disaster_id if provided
        if ($request->has('disaster_id')) {
            $query->where('disaster_id', $request->disaster_id);
        }

        // Order by urgency (critical first) and created_at
        $aidRequests = $query->orderByRaw("FIELD(urgency, 'critical', 'high', 'medium', 'low')")
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        return response()->json($aidRequests);
    }

    // Store a newly created aid request.
    public function store(Request $request): JsonResponse
    {
        // Check if user is a verified volunteer
        $user = auth()->user();
        if (!$user || !$user->volunteer || $user->email_verified_at === null) {
            return response()->json([
                'message' => 'Only verified volunteers can submit aid requests'
            ], 403);
        }

        try {
            $validatedData = $request->validate([
                'disaster_id' => 'required|integer|exists:disasters,id',
                'location' => 'required|string|max:255',
                'aid_type' => 'required|string|max:255',
                'urgency' => 'required|in:low,medium,high,critical',
                'description' => 'required|string',
            ]);

            // Check if the disaster is active (assuming there's an 'active' field on disasters table)
            $disaster = \DB::table('disasters')->where('id', $validatedData['disaster_id'])->first();
            if (!$disaster || !$disaster->active) {
                return response()->json([
                    'message' => 'Only active disasters can be selected'
                ], 400);
            }

            // Set automatically by controller
            $validatedData['requester_id'] = $user->id;
            $validatedData['status'] = 'pending_assignment'; // Default status

            $aidRequest = AidRequest::create($validatedData);

            return response()->json([
                'message' => 'Aid request submitted successfully.'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
