<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Event;
use Inertia\Ssr\Gateway;
use App\Http\InertiaHttpGateway;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Listeners\CreateUserAvatarOnRegistration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Model::unguard();
        if (app()->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            app()->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            app()->register(TelescopeServiceProvider::class);
        }
        app()->bind(Gateway::class, InertiaHttpGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        $this->bootRoute();
        if (!app()->environment('local')) {
            $url->forceScheme('https');
        }

        Cashier::calculateTaxes();

        RedirectIfAuthenticated::redirectUsing(function () {
            return route('my.dashboard.page');
        });

        Event::listen(
            SendEmailVerificationNotification::class,
            CreateUserAvatarOnRegistration::class,
        );

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
    }
    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
