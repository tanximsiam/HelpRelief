<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DisasterAlert;

class DisasterAlertController extends Controller
{
    //
    public function index()
    {
        return response()->json(
        DisasterAlert::orderBy('reported_at', 'desc')->get());
    }

    public function new()
    {
        return DisasterAlert::where('confirmed', 'pending')
            ->orderBy('reported_at', 'desc')
            ->get();
    }

    public function reject($id)
    {
        $alert = DisasterAlert::findOrFail($id);
        $alert->confirmed = 'rejected';
        $alert->save();

        return response()->json(['message' => 'Alert rejected']);
    }

    public function confirm($id)
    {
        $alert = DisasterAlert::findOrFail($id);
        $alert->confirmed = 'confirmed';
        $alert->save();

        return response()->json(['message' => 'Alert confirmed']);
    }
}
