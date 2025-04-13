<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{

    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();

        if (!Auth::check() || !$user->isStaff()) {
            abort(404);
        }

        return $next($request);
    }

}
