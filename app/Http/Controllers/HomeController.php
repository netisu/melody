<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Status;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Services\BBCodeService;
use Mews\Purifier\Facades\Purifier;

class HomeController extends Controller
{
    public function WelcomeIndex(BBCodeService $bbcodeService)
    {
        // Define a cache key for this query result
        $cacheKeyItems = 'landing_items';
        $cacheKeyPosts = 'landing_posts';

        $landingUser = Cache::remember('landing_user', 10, function () {
            return User::inRandomOrder()->first();
        });

        // Use caching to store the item count query result
        $landingItems = Cache::remember($cacheKeyItems, now()->addHours(1), function () {
            return Item::where([
                ['public_view', true],
                ['status', 'approved'],
            ])->with('creator')->inRandomOrder()->paginate(6)->through(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'thumbnail' => $item->thumbnail(),
                    'creator' => [
                        'username' => $item->creator->username,
                        'displayname' => $item->creator->display_name,
                    ],
                ];
            });
        });



        $landingPosts = Cache::remember($cacheKeyPosts, now()->addHours(1), function () use ($bbcodeService) {
            return ForumThread::inRandomOrder()->paginate(4)->through(function ($post) use ($bbcodeService) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'body' => Purifier::clean($bbcodeService->parse($post->body)),
                    'creator' => [
                        'username' => $post->creator->username,
                    ],
                    'slug' => $post->slug(),
                ];
            });
        });

        return inertia('Welcome', [
            'landing' => [
                'user' => [
                    'username' => $landingUser->username ?? null,
                    'displayname' => $landingUser->display_name ?? null,
                    'avatar' => $landingUser ? $landingUser->thumbnail() : null,
                ],
                'items' => $landingItems,
                'posts' => $landingPosts,
            ],
        ]);
    }


    public function DashboardIndex()
    {
        // Retrieve recommendations with caching
        $recommendations = Cache::remember('daily-recomendations:' . Auth::id(), now()->addHours(6), function () {
            return Item::where([
                ['public_view', true],
                ['status', 'approved'],
            ])->with('creator')->inRandomOrder()->take(12)->get();
        });

        foreach ($recommendations as $recommendation) {
            $thumbnail = $recommendation->thumbnail();
            $recommendation->thumbnail = $thumbnail;
        }
        return inertia('Dashboard', [
            'nextlevelxp' => Auth::user()->getNextLevelExp(),
            'position' => Auth::user()->CurrentPosition(),
            'totalSales' => Auth::user()->TotalSales(),
            'totalFollowers' => Auth::user()->followers()->count(),
            'totalPosts' => Auth::user()->posts()->count(),
            'recommendations' => $recommendations,
        ]);
    }


    public function DashboardVal(Request $request)
    {
        $request->validate([
            'message' => ['max:124']
        ]);

        $maxAttempts = 5; // Allow 5 requests per two minutes (adjust as needed)
        $key = 'latest-status:' . Auth::id();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. You may try again in ' . $seconds . ' seconds.',
                'type' => 'error'
            ], 429);
        }

        RateLimiter::increment($key);

        if ($request->message != Auth::user()->latestStatus) {
            // Logging
            $status = new Status;
            $status->creator_id = Auth::id();
            $status->message = $request->message;
            $status->save();

            $user = User::find(Auth::id());
            $user->status = $request->message;
            $user->save();

            Auth::user()->addPoints(3);

            return response()->json([
                'message' => 'Your status has been changed.',
                'type' => 'success'
            ], 200);
        }
    }
}
