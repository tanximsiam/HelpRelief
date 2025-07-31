<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\NgoApplication;


class SocialAuthController extends Controller
{
    //
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $email = $googleUser->getEmail();
        $domain = explode('@', $email)[1];
        $isNgo = NgoApplication::where('email', 'like', "%@$domain")
            ->where('status', 'approved')
            ->exists();

        $role = $isNgo ? 'ngo' : 'general';

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => Hash::make(str()->random(16)),
                'role' => $role,
            ]
        );

        if (! $user->wasRecentlyCreated) {
            $user->update(['name' => $googleUser->getName()]);
        }

        Auth::login($user);
        $token = $user->createToken('socialite-token')->plainTextToken;

        return redirect("/socialite-token-receiver?token=$token");

    }


}
