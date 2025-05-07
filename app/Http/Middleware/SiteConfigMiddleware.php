<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SiteSettings;

class SiteConfigMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        // Share the site configuration settings with Inertia
        Inertia::share(key: 'site_config', value: [
            'announcement_message' => siteSetting(key: 'announcement_message') ?? null,
            'announcement' => siteSetting(key: 'announcement_enabled') ?? false,
            'item_creation_enabled' => siteSetting(key: 'item_creation_enabled') ?? false,
            'in_maintenance' => siteSetting(key: 'site_maintenance') ?? false,
        ]);

        return $next($request);
    }
}
