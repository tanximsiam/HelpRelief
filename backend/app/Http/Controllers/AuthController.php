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
use App\Http\Controllers\NgoInviteLinkController;
use Illuminate\Support\Str;

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
            'phone' => $data['phone'] ?? null,
            'password' => isset($data['password'])
            ? Hash::make($data['password'])
            : Hash::make(Str::random(16)),
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
        $redirect = $request->query('redirect', '/dashboard');
        $state = json_encode([
            'token' => $token,
            'redirect' => $redirect,
        ]);

        // Optional: Validate token before redirecting
        $invite = NgoInviteLink::where('token', $token)->where('active', true)->first();

        if ($token && ($invite->usage_limit && $invite->used_count >= $invite->usage_limit)) {
            return response()->json(['error' => 'Invalid or expired token'], 403);
        }

        // Token is valid → attach to state and redirect to Google
        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $state = json_decode($request->query('state', ''), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            abort(403, 'Invalid OAuth state');
        }
        $googleUser = Socialite::driver('google')->stateless()->user();
        $redirectPath = $state['redirect'] ?? '/dashboard';

        // 1) If user exists → issue token + redirect
        if ($existing = User::where('email', $googleUser->email)->first()) {
            $token = $existing->createToken('google-token')->plainTextToken;
            return redirect(rtrim(config('app.frontend_url'), '/') . '/oauth/callback?token=' . urlencode($token) . '&redirect=' . urlencode($redirectPath));

        }

        // 2) Read invite from OAuth state (optional) and validate
        $inviteToken = $state['token'] ?? '';
        $invite = null;

        if ($inviteToken !== '') {
            $invite = NgoInviteLink::with('ngo')->where('token', $inviteToken)->first();

            if (!$invite) {
                $invite = null; // invalid token
            } else {
                // active flag (if exists)
                if (isset($invite->active) && !$invite->active) {
                    $invite = null;
                }
                // usage limit (if exists)
                if (isset($invite->usage_limit, $invite->used_count)
                    && !is_null($invite->usage_limit)
                    && $invite->used_count >= $invite->usage_limit) {
                    $invite = null;
                }
                // NGO email-domain match (if NGO has an email)
                if ($invite && $invite->ngo && $invite->ngo->email) {
                    $expectedDomain = substr(strrchr($invite->ngo->email, '@'), 1) ?: '';
                    $userDomain     = substr(strrchr($googleUser->email, '@'), 1) ?: '';
                    if ($expectedDomain && $userDomain && strcasecmp($expectedDomain, $userDomain) !== 0) {
                        abort(403, 'Unauthorized email domain for this NGO');
                    }
                }
            }
        }

        // 3) Create user (role depends on valid invite)
        $user = $this->createUser(
            [
                'name'  => $googleUser->name,
                'email' => $googleUser->email,
            ],
            [
                'role' => $invite ? 'ngo_staff' : 'general',
            ]
        );

        // 4) If valid invite → attach NGO staff + mark invite used
        if ($invite) {
            NgoStaff::firstOrCreate(
                ['user_id' => $user->id, 'ngo_id' => $invite->ngo_id],
                ['privilege_role' => $invite->privilege_role]
            );
            app(NgoInviteLinkController::class)->markInviteUsed($invite);
        }

        // 5) Issue token + redirect (safe URL)
        $token = $user->createToken('google-token')->plainTextToken;
        return redirect(rtrim(config('app.frontend_url'), '/') . '/oauth/callback?token=' . urlencode($token) . '&redirect=' . urlencode($redirectPath));

    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
