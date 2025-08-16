<?php

namespace App\Http\Controllers;

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
}
