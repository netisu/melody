<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    SocialiteProviders\Manager\ServiceProvider::class,
    Spatie\Varnish\VarnishServiceProvider::class,
];

if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
