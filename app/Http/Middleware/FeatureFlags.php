<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia as InertiaResponse;
use Inertia\Middleware;

class FeatureFlags
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route()->getName();
        $middleware = $request->route()->middleware();
        $isPost = $request->isMethod('post');

        if ($this->isMaintenanceEnabled($route)) {
            return $this->handleMaintenance($route, $request, $next);
        }

        if ($this->isFeatureDisabled($route)) {
            return $this->handleFeatureDisabled($route, $middleware, $isPost);
        }

        return $next($request);
    }

    private function isMaintenanceEnabled($route)
    {
        $envPass = env('MAINTENANCE_PASSWORD');
        $maintenancePassword = session('maintenance_password');

        return $this->isSiteMaintenanceEnabled($maintenancePassword, $envPass)
            && $this->shouldShowMaintenancePage($route);
    }

    private function isSiteMaintenanceEnabled($maintenancePassword, $envPass)
    {
        return siteSetting('site_maintenance')
            && ($maintenancePassword === null || $maintenancePassword != $envPass)
            || siteSetting('site_maintenance') && $maintenancePassword !== null;
    }

    private function shouldShowMaintenancePage($route)
    {
        return !session()->has('maintenance_password')
            && !Str::startsWith($route, 'maintenance.')
            && $route != 'upgrade.notify';
    }

    private function handleMaintenance($route, $request, $next)
    {
        if ($this->shouldShowMaintenancePage($route)) {
            return redirect()->route('maintenance.page');
        }

        return $next($request);
    }

    private function isFeatureDisabled($route)
    {
        $featureMap = [
            'store.' => 'market_enabled',
            'forum.' => 'discussion_enabled',
            'creator_area.' => 'item_creation_enabled',
            'avatar.' => 'customization_enabled',
            'account.trades.' => 'trading_enabled',
            'spaces.' => 'spaces_enabled',
            'user.settings.' => 'user_settings_enabled',
            'account.upgrade.' => 'real_life_purchases_enabled',
            'auth.register.' => 'registration_enabled',
        ];

        foreach ($featureMap as $prefix => $feature) {
            if ($this->isFeatureSettingDisabled($feature) && Str::startsWith($route, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private function isFeatureSettingDisabled($feature)
    {
        $settingValue = siteSetting("{$feature}");
        // var_dump($settingValue); // Add this line to debug the value of $settingValue
        return !$settingValue;
    }

    private function handleFeatureDisabled($route, $middleware, $isPost)
    {
        $feature = $this->getFeatureForRoute($route);
        $alias = $this->getFeatureAlias($feature);

        if ($feature === 'Maintenance') {
            return redirect()->route('maintenance.page');
        } elseif ($isPost || ((!Auth::check() || (Auth::check() && !Auth::user()->isStaff())))) {
            return inertia('App/FeatureDisabled', ['title' => $alias]);
        }

        return inertia('App/FeatureDisabled', ['title' => $alias]);
    }

    private function getFeatureForRoute($route)
    {
        $featureMap = [
            'store.' => 'market_enabled',
            'forum.' => 'discussion_enabled',
            'creator_area.' => 'item_creation_enabled',
            'avatar.' => 'customization_enabled',
            'account.trades.' => 'trading_enabled',
            'spaces.' => 'spaces_enabled',
            'user.settings.' => 'user_settings_enabled',
            'account.upgrade.' => 'real_life_purchases_enabled',
            'auth.register.' => 'registration_enabled',
        ];

        foreach ($featureMap as $prefix => $feature) {
            if (Str::startsWith($route, $prefix) && $this->isFeatureSettingDisabled($feature)) {
                return $feature;
            }
        }

        return '';
    }

    private function getFeatureAlias($feature)
    {
        $featureAliasMap = [
            'market_enabled' => 'Market',
            'discussion_enabled' => 'Forum',
            'item_creation_enabled' => 'Creator Area',
            'customization_enabled' => 'Avatar',
            'trading_enabled' => 'Trading',
            'spaces_enabled' => 'Spaces',
            'user_settings_enabled' => 'Settings',
            'real_life_purchases_enabled' => 'Real Life Purchases',
            'registration_enabled' => 'Register',
            // ... add more aliases as needed
        ];

        return $featureAliasMap[$feature] ?? 'Unknown Feature';
    }
}
