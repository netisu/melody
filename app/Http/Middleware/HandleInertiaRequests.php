<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Item;
use App\Models\Space;
use Illuminate\Support\Carbon;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
     /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

      /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $pendingItems = Item::where(column: 'status', operator: 'pending')->with(relations: 'creator')->get();

        $pendingSpaces = Space::where(column: 'thumbnail_pending', operator: true)->with(relations: 'creator')->get();

        $pendingItemsAndSpaces = $pendingItems->merge(items: $pendingSpaces);
        $notifications =  $request->user() ? $request->user()->unreadNotifications()->limit(value: 5)->get()
        ->each(callback: function ($notification): void {
            $notification->DateHum = $notification->created_at->diffForHumans();
        }) : null;

        return array_merge( parent::share(request: $request), [
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
            'site' => config(key: 'Values'),
            'locale' => function (): string {
                return app()->getLocale();
            },
            'locales' => function (): mixed {
                return config('ActiveLocales');
            },
            'language' => function (): mixed {
                return translations(
                    resource_path('lang/' . app()->getLocale() . '.json')
                );
            },
            'auth' => function () use ($request,  $pendingItemsAndSpaces, $notifications): array {
                $response = [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'username' => $request->user()->username,
                        'display_name' => $request->user()->display_name,
                        'next_currency_payout' => Carbon::parse($request->user()->next_reward_payout)->diffForHumans(),
                        'sparkles' => shortNum($request->user()->sparkles),
                        'stars' => shortNum($request->user()->stars),
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

                if ($request->user() && $request->route()->named(patterns: 'user.settings.page')){
                         $response['user']['email'] = preg_replace(pattern: '/[^@]+@([^\s]+)/', replacement: ''.substr(string: $request->user()->email, offset: 0, length: 3).'********@$1', subject: $request->user()->email);
                         $response['user']['birthdate'] = $request->user()->birthdate;
                         $response['user']['email_verified_at'] = $request->user()->email_verified_at;
                };

                return $response;
            },
        ]);
    }
}
