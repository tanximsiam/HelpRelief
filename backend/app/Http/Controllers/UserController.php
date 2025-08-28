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

        // Require current password for any update
        $request->validate([
            'current_password' => 'required|string',
        ]);

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'error' => 'Current password is incorrect.'
            ], 403);
        }

        // Base validation for other fields
        $rules = [
            'name' => 'string|sometimes',
            'phone' => 'string|sometimes',
            'password' => 'string|confirmed|sometimes',
        ];

        // If general user → allow email update
        if ($user->role === 'general') {
            $rules['email'] = 'email|unique:users,email,' . $user->id . '|sometimes';
        }

        $data = $request->validate($rules);

        // Remove current_password from data to update
        unset($data['current_password']);

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

    public function show(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'volunteer' => $user->volunteer
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

