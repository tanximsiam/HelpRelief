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
        $aidRequests = $query->orderByRaw("(urgency='critical') DESC, (urgency='high') DESC, (urgency='medium') DESC, (urgency='low') DESC")
                            ->orderBy('created_at', 'desc')
                            ->get();

        return response()->json($aidRequests);
    }

    // Store a newly created aid request.
    public function store(Request $request): JsonResponse
    {
        // Check if user is a verified volunteer
        $user = $request->user();
        if (!$user || !$user->volunteer) {
            return response()->json([
                'message' => 'Only volunteers can submit aid requests'
            ], 403);
        }

        try {
            $validatedData = $request->validate([
                'disaster_id' => 'required|integer', // TODO: Add |exists:disasters,id when disasters table is ready
                'location' => 'required|string|max:255',
                'aid_type' => 'required|in:financial,medical,resource', // Fixed to match enum values
                'urgency' => 'required|in:low,medium,high,critical',
                'description' => 'required|string|max:1000',
            ]);

            // TODO: Uncomment when disasters table is ready
            // $disaster = \DB::table('disasters')->where('id', $validatedData['disaster_id'])->first();
            // if (!$disaster || !$disaster->active) {
            //     return response()->json([
            //         'message' => 'Only active disasters can be selected'
            //     ], 400);
            // }

            // Set automatically by controller
            $validatedData['requester_id'] = $user->id;
            $validatedData['status'] = 'pending'; // Default status as per migration

            $aidRequest = AidRequest::create($validatedData);

            return response()->json([
                'message' => 'Aid request submitted successfully.',
                'aid_request' => $aidRequest
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create aid request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->volunteer) {
            return response()->json(['message' => 'Only volunteers can view this'], 403);
        }

        $requests = AidRequest::where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function verifyByRequester(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        // Find the aid request by route param
        $aidRequest = AidRequest::findOrFail($id);

        // Ensure the logged-in user is the requester
        if ($aidRequest->requester_id !== $user->id) {
            return response()->json(['message' => 'You can only verify your own requests'], 403);
        }

        // Update status
        $aidRequest->status = 'completed';
        $aidRequest->save();

        return response()->json([
            'message' => 'Aid request verified successfully',
            'aid_request' => $aidRequest
        ]);
    }

}
