<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class RateLimitMiddleware
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $key = "rate_limit:{$ip}";
        $maxAttempts = 5; // Maximum number of requests
        $decayMinutes = 1; // Timeframe in minutes

        $currentAttempts = Redis::get($key) ?? 0;

        if ($currentAttempts >= $maxAttempts) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }

        Redis::incr($key);
        Redis::expire($key, $decayMinutes * 60);

        return $next($request);
    }
}
