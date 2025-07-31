<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CauseFocus;

class CauseFocusController extends Controller
{
    //
    // GET /api/cause-focuses
    public function index()
    {
        return CauseFocus::orderBy('name')->get();
    }

    // POST /api/cause-focuses (optional for admin panel)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:cause_focuses,name',
        ]);

        return CauseFocus::create($data);
    }
}
