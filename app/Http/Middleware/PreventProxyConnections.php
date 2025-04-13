<?php

namespace App\Http\Middleware;

use Closure;

class PreventProxyConnections
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $proxyHeaders = [
            'HTTP_VIA',
          	'VIA',
	          'Proxy-Connection',
	          'HTTP_X_FORWARDED_FOR',  
	          'HTTP_FORWARDED_FOR',
	          'HTTP_X_FORWARDED',
	          'HTTP_FORWARDED',
	          'HTTP_CLIENT_IP',
	          'HTTP_FORWARDED_FOR_IP',
	          'X-PROXY-ID',
	          'MT-PROXY-ID',
	          'X-TINYPROXY',
	          'X_FORWARDED_FOR',
	          'FORWARDED_FOR',
	          'X_FORWARDED',
	          'FORWARDED',
	          'CLIENT-IP',
	          'CLIENT_IP',
	          'PROXY-AGENT',
	          'HTTP_X_CLUSTER_CLIENT_IP',
	          'FORWARDED_FOR_IP',
	          'HTTP_PROXY_CONNECTION'
        ];

        foreach ($proxyHeaders as $header) {
            if ($request->server($header)) {
                abort(403, 'Please disable your proxy connection.');
            }
        }

        return $next($request);
    }
}
