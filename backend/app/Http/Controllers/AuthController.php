<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\NgoInviteLink;
use App\Models\NgoStaff;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        $user = $this->createUser($fields);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function createUser(array $data, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'general',
        ], $overrides));
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.']
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);

    }

    public function redirectToGoogle(Request $request)
    {
        $token = $request->query('token');

        // Optional: Validate token before redirecting
        $invite = NgoInviteLink::where('token', $token)->where('active', true)->first();

        if ($token && ($invite->usage_limit && $invite->used_count >= $invite->usage_limit)) {
            return response()->json(['error' => 'Invalid or expired token'], 403);
        }

        // Token is valid → attach to state and redirect to Google
        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $token])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // dd([
        //     'state' => $request->query('state'),
        //     'code' => $request->query('code'),
        //     'invite' => NgoInviteLink::where('token', $request->query('state'))->first(),
        // ]);

        $existing = User::where('email', $googleUser->email)->first();

        if ($existing) {

            $token = $existing->createToken('google-token')->plainTextToken;

            return redirect(config('app.frontend_url') . '/dashboard?token=' . $token);
        }

        $token = $request->query('state');

        if (!$token) {

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(str()->random(16)),
                'role' => 'general',
            ]);

        } else {

            $invite = NgoInviteLink::where('token', $token)->first();

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(str()->random(16)),
                'role' => 'ngo_staff',
                ]);

            NgoStaff::create([
                'user_id' => $user->id,
                'ngo_id' => $invite->ngo_id,
                'privilege_role' => $invite->privilege_role,
            ]);

            app(NgoInviteLinkController::class)->markInviteUsed($invite);

        }

        $token = $user->createToken('google-token')->plainTextToken;

        return redirect(config('app.frontend_url') . '/dashboard?token=' . $token);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
