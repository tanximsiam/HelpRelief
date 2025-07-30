<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ngo;


class NgoController extends Controller
{
    //
    public function index()
    {
        return Ngo::all();
    }
}
