<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AidRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Disaster; // added
use App\Models\DisasterCampaignAssignment;

class AidRequestController extends Controller
{
    // Display a listing of aid requests.

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ensure the user is an NGO staff
        $ngoId = $user->ngoStaff->ngo_id ?? null;
        if (!$ngoId) {
            return response()->json(['message' => 'You are not authorized to view aid requests'], 403);
        }

        // Start query with related requester + volunteerRegistration + ngo
        $query = AidRequest::with('requester.volunteerRegistration.ngo')
            ->whereHas('requester.volunteerRegistration', function ($q) use ($ngoId) {
                $q->where('ngo_id', $ngoId);
            });

        // Optional filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('urgency')) {
            $query->byUrgency($request->urgency);
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $aidRequests = $query
            ->orderByRaw("(urgency='critical') DESC, (urgency='high') DESC, (urgency='medium') DESC, (urgency='low') DESC")
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
            $validated = $request->validate([
                'campaign_id' => 'required|exists:disaster_campaign_assignments,id',
                'aid_type' => 'required|in:financial,medical,resource',
                'urgency' => 'required|in:low,medium,high,critical',
                'description' => 'required|string|max:1000',
            ]);

            $campaign = DisasterCampaignAssignment::with('disaster:id,location')->find($validated['campaign_id']);
            if (!$campaign) {
                return response()->json(['message' => 'Campaign not found'], 422);
            }
            $disaster = $campaign->disaster; // for location only

            $payload = [
                'campaign_id' => $campaign->id,
                'location' => $disaster?->location ?? '',
                'aid_type' => $validated['aid_type'],
                'urgency' => $validated['urgency'],
                'description' => $validated['description'],
                'requester_id' => $user->id,
                'status' => 'pending',
            ];

            $aidRequest = AidRequest::create($payload);

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

    // Return a single aid request (NGO staff scoped)
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $ngoId = $user->ngoStaff->ngo_id ?? null;
        if (!$ngoId) {
            return response()->json(['message' => 'You are not authorized to view aid requests'], 403);
        }

        $aidRequest = AidRequest::with('requester.volunteerRegistration.ngo', 'campaign')->findOrFail($id);

        if ($aidRequest->campaign && ($aidRequest->campaign->ngo_id ?? null) !== $ngoId) {
            return response()->json(['message' => 'Not authorized to view this aid request'], 403);
        }

        return response()->json($aidRequest);
    }

}
