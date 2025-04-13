<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_ENV' !== 'local')) {
            $gzipresponse = $next($request);
            $gzipresponse->headers->set('Content-Encoding', 'gzip'); // You can also use 'deflate' or 'br' (Brotli) as alternatives
            $gzipresponse->headers->set('Vary', 'Accept-Encoding');
            return $gzipresponse;
        }
        return $next($request);
    }
}
