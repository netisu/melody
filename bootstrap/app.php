<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Space;
use Illuminate\Support\Carbon;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__ .'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withBroadcasting(
        __DIR__ .'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleWithRedis();
        $middleware->replace(
            search: \Illuminate\Http\Middleware\TrustProxies::class,
            replace: \Monicahq\Cloudflare\Http\Middleware\TrustProxies::class
        );
        $middleware->trustProxies('*');
        $middleware->validateCsrfTokens(except: [
            //
        ]);
        $middleware->statefulApi();
        $middleware->api(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        $middleware->web(append: [
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \Inertia\EncryptHistoryMiddleware::class,
            \App\Http\Middleware\HandleInertiaCrossDomainVisits::class,
            \App\Http\Middleware\HandleInertiaRequests::class,

            // Custom Application Middleware.
            \App\Http\Middleware\CompressResponse::class,
            \App\Http\Middleware\Localization::class,
            \App\Http\Middleware\SiteConfigMiddleware::class,
            \App\Http\Middleware\WordFilterCheck::class,
            \App\Http\Middleware\Roles::class,
            \App\Http\Middleware\FeatureFlags::class,
            \App\Http\Middleware\UpdateOnlineStatus::class,
            \App\Http\Middleware\EnsureSettings::class,
            \App\Http\Middleware\RedirectIfBanned::class,
            \App\Http\Middleware\GiveDailyRewards::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'password.confirm' => \App\Http\Middleware\EnsurePasswordIsConfirmed::class,
            'cacheable' => \Spatie\Varnish\Middleware\CacheWithVarnish::class,
        ]);
        $middleware->redirectGuestsTo(fn() => route('auth.login.page'));
    })->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {

            if (app()->environment(['local', 'testing'])) {
                return $response;
            }
            $statusCode = $response->getStatusCode();

            /**
             * A list of error messages
             *
             * @var array<int, string>
             */

            $messages = [
                500 => 'Whoops, something went wrong on our servers.',
                503 => 'Sorry, we are doing some maintenance. Please check back soon.',
                404 => 'The page you are looking for could not be found.',
                403 => 'You are not authorized to access this page.',
                419 => 'The page expired, please try again.',
            ];

            $routes = [
                500 => 'E_SERVICE_ERROR',
                503 => 'E_SERVICE_UNAVAILABLE',
                404 => 'E_ROUTE_NOT_FOUND',
                403 => 'E_AUTHORIZATION_FAILURE',
                419 => 'E_PAGE_EXPIRED',
            ];

            $icons = [
                500 => 'fa-solid fa-bomb fa-gradient text-danger',
                503 => 'fa-solid fa-cloud-slash fa-gradient text-danger',
                404 => 'fa-solid fa-person-circle-question fa-gradient text-warning',
                403 => 'fa-solid fa-person-circle-exclamation fa-gradient text-danger',
                419 => 'fa-solid fa-hourglass-empty fa-gradient text-info',
            ];

            // Handle ModelNotFoundException for JSON requests specifically
            if ($exception instanceof ModelNotFoundException && $request->expectsJson()) {
                return response()->json([
                    'data' => 'Resource not found'
                ], 404);
            }
            if ($request->expectsJson() || !$request->isMethod('GET')) {
                // authenticated API request returning 403
                if ($statusCode === 403 && !Auth::check()) {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Unauthenticated.'
                    ], 401);
                }
                // other JSON errors
                return response()->json([
                    'type' => 'error',
                    'message' => $messages[$statusCode] ?? 'An unexpected error occurred.'
                ], $statusCode);
            }
            if ($statusCode === 419) {
                return back()->with([
                    'message' => $messages[$statusCode],
                ]);
            }

            if (!array_key_exists($statusCode, $messages)) {
                return $response;
            }

            return inertia('App/Error', [
                'site' => config('Values'),
                'auth' => function () use ($request): array {
                    $user = $request->user();
                    $userData = null;

                    if ($user) {
                        $notifications = $user->unreadNotifications()->limit(5)->get()
                            ->each(fn($notification) => $notification->DateHum = $notification->created_at->diffForHumans());

                        $userData = [
                            'id' => $user->id,
                            'username' => $user->username,
                            'display_name' => $user->display_name,
                            'next_currency_payout' => Carbon::parse($user->next_reward_payout)->diffForHumans(),
                            'coins' => shortNum($user->coins),
                            'bucks' => shortNum($user->bucks),
                            'staff' => $user->isStaff() ?? false,
                            'headshot' => $user->headshot(),
                            'thumbnail' => $user->thumbnail(),
                            'settings' => $user->settings,
                            'level' => $user->getLevel(),
                            'xp' => $user->getPoints(),
                            'nextlevelxp' => $user->nextLevelAt(),
                            'mainSpaces' => $user->mainSpaces(),
                            'navSpaces' => $user->navSpaces(),
                            'notifications' => $notifications,
                        ];

                        if ($user->isStaff()) {
                            // Only fetch these if staff
                            $pendingItems = Item::where('status', 'pending')->with('creator')->count(); // Get count directly
                            $pendingSpaces = Space::where('thumbnail_pending', true)->with('creator')->count(); // Get count directly
                            $userData['position'] = $user->CurrentPosition();
                            $userData['positionID'] = $user->CurrentPositionID();
                            $userData['pendingAssets'] = $pendingItems + $pendingSpaces; // Sum counts
                        };

                        if (config('Values.in_event')) {
                            $userData['event_currency'] = shortNum($user->event_currency);
                        };

                        if ($request->route()->named('user.settings.page')) {
                            // Sensitive info, only send on specific page
                            $userData['email'] = preg_replace(pattern: '/[^@]+@([^\s]+)/', replacement: '' . substr(string: $user->email, offset: 0, length: 3) . '********@$1', subject: $user->email);
                            $userData['birthdate'] = $user->birthdate;
                            $userData['email_verified_at'] = $user->email_verified_at;
                        };
                    }

                    return ['user' => $userData];
                },
                'site_config' => [
                    'announcement_message' => siteSetting('announcement_message') ?? null,
                    'announcement' => siteSetting('announcement_enabled') ?? false,
                    'item_creation_enabled' => siteSetting('item_creation_enabled') ?? false,
                    'in_maintenance' => siteSetting('site_maintenance') ?? false,
                ],
                'status' => $statusCode,
                'message' => $messages[$statusCode] ?? 'An unknown error occurred.',
                'adonis_error' => $routes[$statusCode] ?? 'E_UNKNOWN_ERROR',
                'icon' => $icons[$statusCode] ?? 'fa-solid fa-circle-exclamation text-danger',
            ])
                ->toResponse($request)
                ->setStatusCode($statusCode);
        });
    })->create();
