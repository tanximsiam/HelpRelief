<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function index()
    {
        return User::all();
    }


    public function update(Request $request)
    {
        $user = $request->user();

        // Base validation
        $rules = [
            'name' => 'string|sometimes',
            'phone' => 'string|sometimes',
            'password' => 'string|confirmed|sometimes',
        ];

        // If general user → allow email update
        if ($user->role === 'general') {
            $rules['email'] = 'email|unique:users,email,' . $user->id;
        }

        $data = $request->validate($rules);

        // Handle password separately
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Fill + check if actually changed
        $user->fill($data);

        if (! $user->isDirty()) {
            return response()->json([
                'message' => 'No changes detected.'
            ], 200);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }
}

