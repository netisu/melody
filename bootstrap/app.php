<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Space;
use Illuminate\Support\Carbon;
use App\Http\Middleware\EnsurePasswordIsConfirmed;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptionxceptions) {
        $exceptionxceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            /**
             * A list of error messages
             *
             * @var array<int, string>
             */

            $messages = [
                500 => 'Sorry, we are doing some maintenance. Please check back soon.',
                503 => 'Whoops, something went wrong on our servers.',
                404 => 'The page you are looking for could not be found.',
                403 => 'You are not authorized to access this page.',
            ];

            /**
             * A list of backend development helpers
             *
             * @var array<int, string>
             */

            $routes = [
                500 => 'E_CANNOT_LOOKUP_ROUTE',
                503 => 'E_SERVICE_UNAVAILABLE',
                404 => 'E_ROUTE_NOT_FOUND',
                403 => 'E_AUTHORIZATION_FAILURE',
            ];

            $icons = [
                500 => 'fa-solid fa-bomb fa-gradient text-danger',
                503 => 'fa-solid fa-cloud-slash fa-gradient text-danger',
                404 => 'fa-solid fa-person-circle-question fa-gradient text-warning',
                403 => 'fa-solid fa-person-circle-exclamation fa-gradient text-danger',
            ];

            $pendingItems = Item::where('status', 'pending')->with('creator')->get();

            $pendingSpaces = Space::where('thumbnail_pending', true)->with('creator')->get();

            $pendingItemsAndSpaces = $pendingItems->merge($pendingSpaces);

            $notifications =  $request->user() ? $request->user()->unreadNotifications()->limit(value: 5)->get()
            ->each(callback: function ($notification): void {
                $notification->DateHum = $notification->created_at->diffForHumans();
            }) : null;

            if (app()->environment(['local', 'testing'])) {
                return $response;
            }

            if (!array_key_exists($response->getStatusCode(), $messages)) {
                return $response;
            }

            if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
                return response()->json([
                    'data' => 'Resource not found'
                ], 404);
            }

            if (!$request->isMethod('GET')) {
                return response()->json([
                    'type' => 'error',
                    'message' => $messages[$response->getStatusCode()]
                ], $response->getStatusCode());
            }

            if ($request->wantsJson() || $request->isJson() && !Auth::check()) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Unauthentiated.'
                ], 403);
            }

            return inertia('App/Error', [
                'site' => config('Values'),
                'auth' => function () use ($request,  $pendingItemsAndSpaces, $notifications): array {
                    $response = [
                        'user' => $request->user() ? [
                            'id' => $request->user()->id,
                            'username' => $request->user()->username,
                            'display_name' => $request->user()->display_name,
                            'next_currency_payout' => Carbon::parse($request->user()->next_reward_payout)->diffForHumans(),
                            'coins' => shortNum($request->user()->coins),
                            'bucks' => shortNum($request->user()->bucks),
                            'staff' => $request->user()->isStaff() ?? false,
                            'headshot' => $request->user()->headshot(),
                            'thumbnail' => $request->user()->thumbnail(),
                            'settings' => $request->user()->settings,
                            'level' => $request->user()->getLevel(),
                            'xp' => $request->user()->getPoints(),
                            'nextlevelxp' =>  $request->user()->nextLevelAt(),
                            'mainSpaces' => $request->user()->mainSpaces(),
                            'navSpaces' => $request->user()->navSpaces(),
                            'notifications' => $notifications,
                        ] : null,
                    ];

                    if ($request->user() && $request->user()->isStaff()) {
                        $response['user']['position'] = $request->user()->CurrentPosition();
                        $response['user']['positionID'] = $request->user()->CurrentPositionID();
                        $response['user']['pendingAssets'] = $pendingItemsAndSpaces->count();
                    };

                    if ($request->user() && config(key: 'Values.in_event')) {
                        $response['user']['event_currency'] = shortNum(num: $request->user()->event_currency);
                    };

                    if ($request->user() && $request->route()->named(patterns: 'user.settings.page')) {
                        $response['user']['email'] = preg_replace(pattern: '/[^@]+@([^\s]+)/', replacement: '' . substr(string: $request->user()->email, offset: 0, length: 3) . '********@$1', subject: $request->user()->email);
                        $response['user']['birthdate'] = $request->user()->birthdate;
                        $response['user']['email_verified_at'] = $request->user()->email_verified_at;
                    };

                    return $response;
                },
                'site_config' => [
                    'announcement_message' => siteSetting('announcement_message') ?? null,
                    'announcement' => siteSetting('announcement_enabled') ?? false,
                    'item_creation_enabled' => siteSetting('item_creation_enabled') ?? false,
                    'in_maintenance' => siteSetting('site_maintenance') ?? false,
                ],
                'status' => $response->getStatusCode(),
                'message' => $messages[$response->getStatusCode()],
                'adonis_error' => $routes[$response->getStatusCode()],
                'icon' => $icons[$response->getStatusCode()]
            ])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
        });
    })
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )->withMiddleware(function (Middleware $middleware) {
        if (env("APP_ENV") != 'local') {
            $middleware->throttleWithRedis();
        }
        $middleware->replace(
            search: \Illuminate\Http\Middleware\TrustProxies::class,
            replace: \Monicahq\Cloudflare\Http\Middleware\TrustProxies::class
        );

        $environment = env('APP_ENV');

        switch ($environment) {
            case 'production':
                // AWS ELB Configuration
                $middleware->trustProxies(
                    at: '*',
                    headers: \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_TRAEFIK | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_AWS_ELB
                );
                break;

            case 'staging':
                // Digital Ocean Configuration
                $middleware->trustProxies(
                    at: '*',
                    headers: \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
                        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST |
                        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT |
                        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO
                );
                break;

            default:
                // Local/Development Configuration
                $middleware->trustProxies(
                    at: ['127.0.0.1', '::1'],
                    headers: \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
                        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO
                );
        }
        $middleware->validateCsrfTokens(except: [
            //
        ]);
        $middleware->throttleApi();
        $middleware->statefulApi();
        $middleware->api(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        $middleware->web(append: [
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\CompressResponse::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            // \Fahlisaputra\Minify\Middleware\MinifyHtml::class,
            // \Fahlisaputra\Minify\Middleware\MinifyCss::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \Fahlisaputra\Minify\Middleware\MinifyJavascript::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\Localization::class,
            \App\Http\Middleware\HandleInertiaCrossDomainVisits::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SiteConfigMiddleware::class, // SiteSettings
            \App\Http\Middleware\WordFilterCheck::class,
            \App\Http\Middleware\Roles::class,
            \App\Http\Middleware\FeatureFlags::class,
            \App\Http\Middleware\UpdateOnlineStatus::class,
            \App\Http\Middleware\EnsureSettings::class,
            \App\Http\Middleware\RedirectIfBanned::class,
            // \App\Http\Middleware\PreventProxyConnections::class,
            \App\Http\Middleware\GiveDailyRewards::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\UpdateOnlineStatus::class,
            \Inertia\EncryptHistoryMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
        ]);
        $middleware->redirectGuestsTo(fn(Request $request) => route('auth.login.page'));
    })->create();
