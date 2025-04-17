<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class HandleInertiaCrossDomainVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->requiresLocationVisit($request)) {
            return Inertia::location($request->fullUrl());
        }

        return $next($request);
    }

    public function requiresLocationVisit(Request $request): bool
    {
        if (! $request->headers->has('X-Inertia')) {
            return false;
        }

        if ($request->method() !== $request::METHOD_GET) {
            return false;
        }

        if ($request->host() === parse_url(url()->previous(), PHP_URL_HOST)) {
            return false;
        }

        return true;
    }
}
