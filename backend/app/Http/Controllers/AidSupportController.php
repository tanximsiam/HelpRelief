<?php
// app/Http/Controllers/HelpController.php

namespace App\Http\Controllers;

use App\Models\Volunteer_registrations;
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
            'ngo_id' => 'required|exists:ngos,id',
            'aid_type' => 'required|in:financial,medical,resource',
            'quantity' => 'required|string',
            'description' => 'nullable|string',
            'contact' => 'nullable|string'
        ]);

        $aid = AidSupport::create([
            'user_id' => $user->id,
            'disaster_id' => $request->disaster_id,
            'ngo_id' => $request->ngo_id,
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