<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use App\Models\UserSettings;
use Illuminate\Http\Response;

class GoogleSocialiteController extends Controller
{
    public function redirectToGoogle(): Response
    {
        $redirectUrl = Socialite::driver(driver: 'google')->stateless()->redirect();
        return response(content: '', status: 409)->header(key: 'X-Inertia-Location', values: $redirectUrl);
    }

    /**
     * Create a new controller instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function handleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver(driver: 'google')->user();
            $user = User::where(column: 'email', operator: $googleUser->email)->first();

            if (!$user) {
                Abort(code: 404);
            };
            $newUser = User::create(attributes: [
                'display_name' => $user->username,
                'username' => $user->username,
                'email' => $user->email,
                'social_id' => $user->id,
                'social_type' => 'google',
                'password' => Hash::make(value: Str::random(length: 10)),
            ]);

            $newUser->createDefaultAvatar();

            UserSettings::create([
                'user_id' => $user->id,
            ]);

            Auth::login(user: $newUser);

            return redirect(to: '/my/dashboard');
        } catch (Exception $e) {
            dd(vars: $e->getMessage());
        }
    }
}
