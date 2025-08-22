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
            DisasterAlert::orderBy('reported_at', 'desc')->get()
        );
    }
}
