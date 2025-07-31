<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgoStaff;

class NgoStaffController extends Controller
{
    //
    public function index()
    {
        return NgoStaff::with(['user', 'ngo'])->get();
    }

    public function destroy($id)
    {
        NgoStaff::findOrFail($id)->delete();
        return response()->json(['message' => 'Staff removed']);
    }
}
