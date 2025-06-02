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
            'csrf_token' => csrf_token(),
            'site' => config(key: 'Values'),
            'locale' => function () {
                return app()->getLocale();
            },
            'locales' => function (): mixed {
                return config('ActiveLocales');
            },
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
        ]);
    }
}
