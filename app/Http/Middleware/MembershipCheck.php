<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipCheck
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
        if (Auth::check() && strtotime(Auth::user()->membership_until) < time()) {
            $user = Auth::user();
            $user->membership_until = null;
            $user->save();
        }

        return $next($request);
    }
}
