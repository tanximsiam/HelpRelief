<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function runDisasterAlertCron(Request $request)
    {
        // Run the correct command for disaster alerts
        \Artisan::call('app:fetch-reliefweb-disasters');
        $output = \Artisan::output();
        return response()->json([
            'message' => 'Disaster alert cronjob executed.',
            'output' => $output
        ]);
    }
}
