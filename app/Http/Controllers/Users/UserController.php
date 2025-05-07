<?php

namespace App\Http\Controllers\Users;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Http\Controllers\Controller;
use App\Models\Followers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
class UserController extends Controller
{

    public function UserIndex()
    {
        // Define a cache key for this query result
        $cacheKey = 'users_index';
        $currentPage = request()->get('page', 1);
        $userCount = User::count();
        $users = Cache::remember($cacheKey . '-' . $currentPage, now()->addMinutes(3), function () {
            return User::orderBy('updated_at', 'desc')->paginate(6)->through(function ($user) {
                $isFollowing = Auth::check() && Auth::user()->isFollowing($user) ?? false;
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'dname' => $user->display_name,
                    'staff' => $user->isStaff(),
                    'status' => $user->status,
                    'DateHum' => $user->DateHum,
                    'avatar' => $user->headshot(),
                    'online' => $user->online(),
                    'is_following' => $isFollowing,
                    'settings' => [
                        'beta_tester' => $user->settings->beta_tester ?? false,
                    ],
                ];
            });
        });

        return inertia('Users/Index', [
            'users' => $users,
            'user_count' => $userCount
        ]);
    }


    public function ProfileIndex($username, Request $request)
    {
        // Define a unique cache key for this user's profile
        $cacheKey = 'user_profile_' . $username;

        // Retrieve the user profile from the cache, ensuring `$user` is accessible
        $user = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($username) {
            return User::where('username', $username)->first();
        });

        // Check if the user exists
        if (!$user) {
            abort(404);
        }

        if (!is_null($user->settings->private_profile ?? null)) {
            if ($user->settings->private_profile) {
                if (!Auth::check() || (Auth::check() && $user->id != Auth::id())) {
                    return inertia('App/ProfileDisabled', [
                        'username' => $user->username,
                    ]);
                }
            }
        }

        switch ($request->category) {
            case '':
            case 'Inventory':
            case 'Followers':
            case 'Following':
            case 'Spaces':
            case 'Posts':
                $categories = [
                    'Main' => ['Inventory', 'Posts', 'Spaces'],
                    'Social' => ['Followers', 'Following'],
                ];
                break;
            default:
                abort(404);
        }

        // Load relationships and counts
        $FollowingCount = $user->following()->count();
        $FollowerCount = $user->followers()->count();

        $userFollowing = $user->following()->paginate(6, ['*'], 'Following');
        $userFollowers = $user->followers()->paginate(6, ['*'], 'followers');

        // Determine if the authenticated user is following the profile user
        $isFollowing = Auth::check() && Auth::user()->isFollowing($user) ?? false;

        // Determine if the profile user is following the authenticated user
        $thisFollowing = Auth::check() && $user->isFollowing(Auth::user()) ?? false;

        // Format join date
        $joindate = Carbon::parse($user->created_at)->format('M d Y');

        $statuscacheKey = $username . 'profile_statuses';

        // Use caching to store the statuses query results
        $statuses = Cache::remember($statuscacheKey, now()->addMinutes(5), function () use ($user) {
            return Status::where([
                ['creator_id', '=', $user->id]
            ])->orderBy('created_at', 'desc')->paginate(6, ['*'], 'posts')->through(function ($status) {
                return [
                    'name' => $status->creator->username,
                    'headshot' => $status->creator->headshot(),
                    'dname' => $status->creator->display_name,
                    'timecreated' => $status->created_at,
                    'message' => $status->message,
                    'DateHum' => $status->DateHum,
                ];
            });
        });




        $response =  inertia('Users/Profile', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'about_me' => $user->about_me,
                'posts' => $user->posts()->count(),
                'thumbnail' => $user->thumbnail(),
                'headshot' => $user->headshot(),
                'level' => $user->getLevel(),
                'following' => $userFollowing,
                'acheivements' => $user->acheivements,
                'followers' => $userFollowers,
                'staff' => $user->isStaff(),
                'spaces'=> $user->spaces()->take(6),
                'position' => $user->CurrentPosition(),
                'followsYou' => $thisFollowing,
                'followers_count' => $FollowerCount,
                'following_count' => $FollowingCount,
                'joindate' => $joindate,
                'DateHum' => $user->DateHum,
                'status' => $user->status,
                'online' => $user->online(),
                'avatar' => [
                    'color_head' => $user->avatar()->color_head ?? 'ffffff',
                    'color_left_arm' => $user->avatar()->color_left_arm ?? 'ffffff',
                    'color_torso' => $user->avatar()->color_torso ?? '055e96',
                    'color_right_arm' => $user->avatar()->color_right_arm ?? 'ffffff',
                    'color_left_leg' => $user->avatar()->color_left_leg ?? 'ffffff',
                    'color_right_leg' => $user->avatar()->color_right_leg ?? 'ffffff',
                    'current_face' => env('STORAGE_URL') . (
                        $user->avatar()->face ? '/uploads/' . getItemHash($user->avatar()->face) . ".png" : '/assets/default.png'
                    ),
                ],
                'settings' => [
                    "verified" => $user->settings->is_verified ?? false,
                    "beta_tester" => $user->settings->beta_tester ?? false,
                    "private_profile" => $user->settings->private_profile ?? false,
                    "followers_enabled" => $user->settings->followers_enabled  ?? false,
                    "trading_enabled" => $user->settings->trading_enabled ?? false,
                    "posting_enabled" => $user->settings->posting_enabled ?? false,
                    "chat_enabled" => $user->settings->chat_enabled ?? false,
                    "alert_enabled" => $user->settings->alert_enabled ?? false,
                    "alert_message" => $user->settings->alert_message ?? null,
                    "calling_card_enabled" => $user->settings->calling_card_enabled ?? false,
                    "calling_card_url" => $user->settings->calling_card_url ?? null,
                    "profile_banner_enabled" => $user->settings->profile_banner_enabled ?? false,
                    "profile_banner_url" => $user->settings->profile_banner_url ?? null,
                    "primarySpace" => $user->settings->primarySpace ? [
                        'id' => $user->settings->primarySpace->id,
                        'slug' => $user->settings->primarySpace->slug(),
                        'name' => $user->settings->primarySpace->name,
                        'thumbnail' => $user->settings->primarySpace->thumbnail(),
                    ] : null,
                    "secondarySpace" => $user->settings->secondarySpace ?  [
                        'id' => $user->settings->secondarySpace->id,
                        'slug' => $user->settings->secondarySpace->slug(),
                        'name' => $user->settings->secondarySpace->name,
                        'thumbnail' => $user->settings->secondarySpace->thumbnail(),
                    ] : null,
                    "ugc_creator" => $user->settings->ugc_creator ?? false,
                    "content_creator" => $user->settings->content_creator ?? false,
                    "country_code" => $user->settings->country_code ?? null,
                ]
            ],
            'is_following' => $isFollowing,
            'statuses' => $statuses,
            'categorizedItems' =>  $categories,
        ]);

        return $response;
    }
}
