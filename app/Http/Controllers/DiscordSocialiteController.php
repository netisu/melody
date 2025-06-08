<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\UserSettings;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Throwable;
use Illuminate\Auth\Events\Registered;
use App\Models\Admin;

class DiscordSocialiteController extends Controller
{
    public function redirectToDiscord()
    {
        $socialiteRedirectResponse = Socialite::driver('discord')->redirect();;
        $redirectUrl = $socialiteRedirectResponse->getTargetUrl();
        return Inertia::location($redirectUrl);
    }

    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function handleCallback(): JsonResponse|RedirectResponse
    {
        try {
            // Get the user information from Google
            $discordUser = Socialite::driver(driver: 'discord')->user();
        } catch (Throwable $e) {
            return response()->json([
                'type' => 'danger',
                'message' => 'Discord authentication failed.'
            ]);
        }

        $existingUser  = User::where(column: 'email', operator: $discordUser->email)->first();

        if ($existingUser) {
            if ($existingUser->social_type = 'discord') {
                Auth::loginUsingId($existingUser->id);
                return redirect(to: '/my/dashboard');
            }
            return response()->json([
                'type' => 'danger',
                'message' => 'You already have an account under your discord account.'
            ]);
        }

        $newUser = User::create(attributes: [
            'username' => $discordUser->name,
            'display_name' => $discordUser->nickname,
            'email' => $discordUser->email,
            'email_verified_at' => now(),
            'password' => Hash::make(value: Str::random(length: 10)),
            'status' => 'Hey, Im new to ' . config('Values.name'),
            'about_me' => 'Greetings! Im new to ' . config('Values.name'),
            'social_id' => $discordUser->id,
            'social_type' => 'discord',
        ]);

        $newUser->avatar();

        UserSettings::create([
            'user_id' => $newUser->id,
            'profile_picture_enabled' => true,
            'profile_picture_pending' => false,
            'profile_picture_url' => $discordUser->avatar,
        ]);

        if ($newUser->id === 1) {
            Admin::create([
                'user_id' => $newUser->id,
                'role_id' => 1,
            ]);
        }

        event(new Registered($newUser));

        Auth::login(user: $newUser);

        return redirect(to: '/my/dashboard');
    }
}
