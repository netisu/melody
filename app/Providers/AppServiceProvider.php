<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Inertia\Ssr\Gateway;
use App\Http\Ssr\HttpGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public const HOME = '/my/dashboard';

    public function register(): void
    {
        Model::unguard();
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {


        $this->bootRoute();
        if (!$this->app->environment('local')) {
            $url->forceScheme('https');
        }

        Cashier::calculateTaxes();

        RedirectIfAuthenticated::redirectUsing(function () {
            return route('my.dashboard.page');
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
        });

        Inertia::share([
            'locale' => function () {
                return app()->getLocale();
            },
            'csrf_token' => csrf_token(),
            'language' => function () {
                if (!file_exists(resource_path('lang/' . app()->getLocale()
                    . '/' . app()->getLocale() . '.json'))) {
                    return [];
                }
                return json_decode(
                    file_get_contents(
                        resource_path('lang/' . app()->getLocale() . '/'
                            . app()->getLocale() . '.json')
                    ),
                    true
                );
            },

        ]);
    }
    public function bootRoute(): void
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
