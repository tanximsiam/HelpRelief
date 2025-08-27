<?php
// app/Http/Controllers/HelpController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Disaster;
use App\Models\AidSupport;
use App\Models\VolunteerRegistration;

class AidSupportController extends Controller
{
     public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'disaster_id' => 'required|exists:disasters,id',
            'campaign_id' => 'required|exists:disaster_campaign_assignments,id',
            'aid_type' => 'required|in:financial,medical,resource',
            'quantity' => 'required|string',
            'description' => 'nullable|string',
            'contact' => 'nullable|string'
        ]);

        $aid = AidSupport::create([
            'user_id' => $user->id,
            'disaster_id' => $request->disaster_id,
            'campaign_id' => $request->campaign_id,
            'aid_type' => $request->aid_type,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'contact' => $request->contact,
            'status' => 'processing',
        ]);

        return response()->json([
            'message' => 'Aid offer submitted successfully.',
            'status' => $aid->status
        ]);
    }

    public function myOffers(Request $request)
    {
        return AidSupport::where('user_id', $request->user()->id)->get();
    }

}