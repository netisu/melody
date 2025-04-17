<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSettings;
use Illuminate\Support\Facades\Auth;

class EnsureSettings
{

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->settings === null) {
            UserSettings::firstOrCreate([
                'user_id' => Auth::user()->id,
            ]);
        }
        
        return $next($request);
    }
}
