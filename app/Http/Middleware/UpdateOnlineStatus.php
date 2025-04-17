<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateOnlineStatus
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
        if(Auth::check() && Carbon::parse(Auth::user()->updated_at)->addMinute()->isPast()) {
            $user = Auth::user();

            $user->updated_at = Carbon::now()->toDateTimeString();
            $user->save();
        }

        return $next($request);
    }
}
