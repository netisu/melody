<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Item;
use App\Models\Space;
use Illuminate\Support\Carbon;

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
        $pendingItems = Item::where('status', 'pending')->with('creator')->get();

        $pendingSpaces = Space::where('thumbnail_pending', true)->with('creator')->get();

        $pendingItemsAndSpaces = $pendingItems->merge($pendingSpaces);

        return array_merge(parent::share($request), [
            'site' => config('Values'),
            'locale' => function () {
                return app()->getLocale();
            },
            'locales' => function () {
                return config('ActiveLocales');
            },
            'language' => function () {
                return translations(
                    resource_path('lang/' . app()->getLocale() . '.json')
                );
            },
            'auth' => function () use ($request,  $pendingItemsAndSpaces) {
                $response = [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'username' => $request->user()->username,
                        'display_name' => $request->user()->display_name,
                        'next_currency_payout' => Carbon::parse($request->user()->next_reward_payout)->diffForHumans(),
                        'coins' => shortNum($request->user()->coins),
                        'bucks' => shortNum($request->user()->bucks),
                        'staff' => $request->user()->isStaff() ?? false,
                        'position' => $request->user()->CurrentPosition() ?? null,
                        'positionID' => $request->user()->CurrentPositionID() ?? null,
                        'headshot' => $request->user()->headshot(),
                        'thumbnail' => $request->user()->thumbnail(),
                        'settings' => $request->user()->settings,
                        'level' => $request->user()->getLevel(),
                        'xp' => $request->user()->getPoints(),
                        'nextlevelxp' =>  $request->user()->nextLevelAt(),
                        'mainSpaces' => $request->user()->mainSpaces(),
                        'navSpaces' => $request->user()->navSpaces(),
                        'notifications' => $request->user()->unreadNotifications()->limit(5)->get()
                            ->each(function ($notification) {
                                $notification->DateHum = $notification->created_at->diffForHumans();
                            }),
                    ] : null,
                ];

                if ($request->user() && $request->user()->isStaff()) {
                    $response['user']['pendingAssets'] = $pendingItemsAndSpaces->count();
                };

                if ($request->user() && config('Values.in_event')) {
                     $response['user']['event_currency'] = shortNum($request->user()->event_currency);
                };

                if ($request->user() && $request->route()->named('user.settings.page')){
                         $response['user']['email'] = preg_replace('/[^@]+@([^\s]+)/', ''.substr($request->user()->email, 0, 3).'********@$1', $request->user()->email);
                         $response['user']['birthdate'] = $request->user()->birthdate;
                         $response['user']['email_verified_at'] = $request->user()->email_verified_at;
                };
                
                return $response;
            },
        ]);
    }
}
