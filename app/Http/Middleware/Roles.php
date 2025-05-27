<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use App\Models\IpLog;
use App\Models\UserSettings;
use App\Models\Avatar;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (Auth::check() && is_null($user->lastIP())) {
            IpLog::create([
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
        };

        if (Auth::check() && !UserSettings::where(['user_id' => Auth::id()])->exists()) {
            $user = UserSettings::create([
                'user_id' => Auth::id(),
            ]);
        };

        if (config('app.env') != 'local') {
            if (Auth::check() && config('Values.in_testing_phase') && $user->settings->beta_tester !== true) {
                $user = UserSettings::where('id', Auth::id())->update(['beta_tester' => true]);
            };
        }
        if (Auth::check() && !Avatar::where(['user_id' => Auth::id()])->exists()) {
            $user = Avatar::create([
                'user_id' => Auth::id(),
            ]);
        };


        return $next($request);
    }
}
