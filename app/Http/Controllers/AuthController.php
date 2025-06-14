<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Vite;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function showConfirmForm()
    {
        return Inertia::render('App/ConfirmYourIdentity', [
            'intendedUrl' => Session::get('url.intended'),
        ]);
    }

    public function ProvidersIndex() {
        return Inertia::render('Authentication/Providers');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!Auth::attempt(['username' => Auth::user()->username, 'password' => $request->password])) {
            return response()->json(['message' => 'Incorrect password.'], 422);
        }

        $request->session()->put('password.confirmed_at', now()->timestamp);

        return redirect($request->input('intended'));
    }

    public function LoginVal(Request $request): RedirectResponse
    {

        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('username', $credentials['username'])->first();

        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            // Authentication failed
            RateLimiter::hit($this->throttleKey($request));
            if ($this->limiter()->attempts($this->throttleKey($request)) == '5') {
                if (!Cache::has("{$this->throttleKey($request)}_mins"))
                    Cache::put("{$this->throttleKey($request)}_mins", 2, now()->addMinutes(30));
                else
                    Cache::increment("{$this->throttleKey($request)}_mins");
            }

            throw ValidationException::withMessages([
                'password' => __('auth.failed'),
            ]);
        } else {
            // Authentication passed
            RateLimiter::clear($this->throttleKey($request));
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            Auth::login($user);

            return redirect()->route('my.dashboard.page');
        };
    }

    public function LoginIndex()
    {
        return Inertia::render('Authentication/Login');
    }

    public function ForgotIndex()
    {
        return Inertia::render('Authentication/Forgot');
    }

    public function RegisterIndex()
    {
        $countries = Country::all();

        $tester = Cache::remember('tester', 60, function () {
            return User::whereHas('settings', function ($query) {
                $query->where('beta_tester', 1);
            })->inRandomOrder()->first();
        });

        return inertia('Authentication/Create', [
            'themes' => array_filter(config('themes.roster'), function ($theme) {
                return $theme['available'];
            }),
            'tester' => [
                'username' => $tester->username ?? "Aeo",
                'avatar' => $tester ? $tester->thumbnail() : Vite::asset("resources/js/images/earl_placeholder.png"),
            ],
            'countries' => $countries,

        ]);
    }

    public function registerVal(Request $request)
    {
        $request->validate([
            'username' => 'required|alpha_num|min:3|max:25|profane|unique:' . User::class,
            'displayName' => 'required|alpha_num|min:3|max:25|profane',
            'country' => 'alpha|max:2',
            'birthdate.day' => 'required|numeric|min:1|max:31',
            'birthdate.month' => 'required|numeric|min:1|max:12',
            'birthdate.year' => 'required|numeric|min:1900|max:' . date('Y'),
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $birthdate = sprintf(
            '%02d/%02d/%04d',
            $request->input('birthdate.month'),
            $request->input('birthdate.day'),
            $request->input('birthdate.year')
        );

        $user = User::create([
            'username' => $request->username,
            'display_name' => $request->displayName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthdate' => $birthdate,
            'status' => 'Hey, Im new to ' . config('Values.name'),
            'about_me' => 'Greetings! Im new to ' . config('Values.name'),
        ]);

        $user->avatar();

        UserSettings::create([
            'user_id' => $user->id,
            'country_code' => strtolower($request->country),
        ]);


        if ($user->id === 1) {
            Admin::create([
                'user_id' => $user->id,
                'role_id' => 1,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route('my.dashboard.page');

    }
    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower('attempts_on_' . $request->input('username')) . '_from' . $request->ip());
    }

    public function UserExit(): RedirectResponse
    {
        Auth::guard('web')->logout();

        Inertia::clearHistory();

        return redirect()->intended(route("welcome.page"))->with([
            'type' => 'success',
            'message' => 'You have logged out.'
        ]);
    }
}
