<?php

namespace App\Http\Controllers\Endpoints;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Followers;
use App\Models\Avatar;
use App\Models\Status;
use App\Notifications\FollowerNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Event;
use App\Models\Item;

class UserController extends Controller
{
    public function getStatus($userID)
    {
        $user = User::where('id', '=', $userID)->first();

        if ($user && $user->online()) {
            return response()->json(['online' => true]);
        } else {
            return response()->json(['online' => false]);
        }
    }

    public function getUserStatus(Request $request)
    {
        $statuses = Status::where([
            //['creator_id', '!=', Auth::user()->id],
            ['message', '!=', null]
        ])->orderBy('created_at', 'desc')->paginate(2)->through(function ($status) {
            return [
                'name' => $status->creator->username,
                'dname' => $status->creator->display_name,
                'timecreated' => $status->created_at,
                'message' => $status->message,
                'DateHum' => $status->DateHum,
            ];
        });

        return response()->json($statuses); // Convert the paginator to an array
    }

    public function getDashboardStatus(Request $request)
    {
        // Define a cache key for this query
        $cacheKey = 'dashboard_statuses';
        $statuses = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $s = Status::where([
                ['message', '!=', null]
            ])->orderBy('created_at', 'desc')->paginate(6);

            // Transform each status object into a desired structure
            $statuses = $s->transform(function ($status) {
                return [
                    'name' => $status->creator->username,
                    'dname' => $status->creator->display_name,
                    'timecreated' => $status->created_at, // assuming this is the desired format
                    'message' => $status->message,
                    'DateHum' => $status->DateHum,
                    'thumbnail' => $status->creator->headshot(),
                ];
            });

            // Return the transformed data as JSON
            return response()->json($statuses);
        });
        return $statuses;
    }

    public function ProfileImage(string $username)
    {
        $GetUser = User::where('username', $username)->first();
        if ($GetUser) {
            return response()->json($GetUser->headshot());
        };
        return response()->json(config('app.default_avatar_file'));
    }

    public function getAvatar($userID)
    {
        $GetUser = Avatar::where('id', $userID)->first();
        $url = config('Values.storage.url');
        $image = ($GetUser->image === 'default') ? config('Values.render.default_avatar') : $GetUser->image;
        $image =  "{$url}/{$image}.png";
        echo ($image);
    }

    public function getFollowingStatus($userID)
    {
        $userfollowing = Followers::where(['follower_id' => Auth::user()->id, 'following_id' => $userID])->exists()->first();
        dd($userfollowing);

        if ($userfollowing) {
            return response()->json(['online' => true]);
        } else {
            return response()->json(['online' => false]);
        }
    }

    public function follow(User $user)
    {
        $loggedInUser = Auth::user();

        if ($loggedInUser->id === $user->id) {
            // User is logged in and trying to follow themself
            return response()->json(['message' => 'You cannot follow yourself.'], 400);
        };

        if ($loggedInUser->isFollowing($user)) {
            return response()->json(['message' => 'You are already following this user.'], 400);
        };

        if ($loggedInUser !== null) {
            $loggedInUser->following()->attach($user->id);
            $user->notify(new FollowerNotification($loggedInUser));

            if (config('Values.in_event') == null && !$loggedInUser->ownsItem(1)) {
                $eventItem = Item::where('id', 124)->first();
                $event = new Event;
                $event->grantItem($eventItem, $loggedInUser, false);
            };

            return response()->json(['message' => 'Successfully followed.'], 200);
            // ...
        } else {
            return response()->json(['message' => 'Login to follow this user.'], 503);
        }
    }

    public function getUser($username, Request $request)
    {
        // Define a unique cache key for this user's profile
        $cacheKey = 'user_profile_data_' . $username;

        // Retrieve the user profile from the cache, ensuring `$user` is accessible
        $user = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($username) {
            return User::where('username', $username)->first();
        });

        // Check if the user exists
        if (!$user) {
            response()->json([
                'type' => 'error',
                'message' => 'This user does not exist.',
            ]);
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
            case 'Profile':
            case 'Inventory':
            case 'Followers':
            case 'Following':
            case 'Spaces':
            case 'Posts':
                $categories = [
                    'Main' => ['Profile', 'Inventory', 'Posts', 'Spaces'],
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




        $response = response()->json([
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
                'spaces' => $user->spaces()->take(6),
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
                    'current_face' => config('app.storage.url') . (
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

    public function unfollow(User $user)
    {
        $loggedInUser = Auth::user();

        // Check if the logged-in user is the same as the user they are trying to unfollow
        if ($loggedInUser->id === $user->id) {
            return response()->json(['message' => 'You cannot unfollow yourself.'], 400);
        }

        $loggedInUser->following()->detach($user->id);

        return response()->json(['message' => 'Successfully unfollowed.']);
    }
    public function UsernameAvailability(Request $request)
    {
        $name = $request->input('name');

        $userExists = User::where('username', $name)->exists();

        // Basic Validation
        if (empty($name)) {
            return response()->json(['error' => true, 'message' => 'Your username is empty!']);
        }

        if (strtolower($name) === strtolower(config('Values.name'))) {
            return response()->json(['error' => true, 'message' => 'You cannot sign up with the same name as the site.']);
        }

        if (strlen($name) < 3) {
            return response()->json(['error' => true, 'message' => 'Your username must be at least 3 characters!']);
        }

        if (strlen($name) > 20) {
            return response()->json(['error' => true, 'message' => 'Your username must be at most 20 characters!']);
        }

        if (!preg_match('/^[a-zA-Z\d]+$/', $name)) {
            return response()->json(['error' => true, 'message' => 'Your username contains special characters!']);
        }

        $profanityList = Arr::wrap(config('filter.blacklist'));

        // Normalize username
        $normalizedName = strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $name));

        foreach ($profanityList as $word) {
            if (stripos($normalizedName, $word) !== false) {
                return response()->json(['error' => true, 'message' => "Your username contains profanity. Please remove '$word' and try again."]);
            }
        }

        // Check for Existing User
        $userExists = User::where('username', $name)->exists();

        if ($userExists) {
            $newname = $name . rand(1, 999);
            return response()->json(['error' => true, 'message' => "This username is already taken! Try {$newname} instead!"]);
        }

        return response()->json(['error' => false, 'message' => 'OK'], 200);
    }

    public function ValidateEmail(Request $request)
    {
        $email = $request->input('email');
        $filteredEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($filteredEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => true, 'message' => "Your email is invalid."]);
        }

        $userHasEmail = User::where('email', $email)->exists();

        if ($userHasEmail) {
            return response()->json(['error' => true, 'message' => "Your email is already linked to another account."]);
        }

        return response()->json(['error' => false, 'message' => 'OK'], 200);
    }

    public function ValidatePassword(Request $request)
    {
        $weakPasswords = [
            config('Values.name'),
            "password",
            "test",
            "test123",
            "pass",
            "abc123",
            "abc",
            "123",
            "php",
            "laravel",
            "adonis",
            "mark",
            "netisux",
            "netisu",
            "aeo",
            "Aeo",
            "AEO",
            "Ae0"
        ];

        $compliments = [
            config('Values.name') . 'iscool',
            strtolower(config('Values.name')) . 'iscool',
        ];

        $request->validate(
            ['password' => ['required', 'confirmed', Rules\Password::defaults()]],
            [
                'password.required' => ['error' => true, 'message' => "The Password Field is Required"],
                'password.confirmed' => ['error' => true, 'message' => "The Password Confirmation Field is Required"],
            ],
        );

        if (!$request->password) {
            return response()->json(['error' => true, 'message' => "The Password Field is Empty"]);
        }
        if (in_array($request->password, $compliments)) {
            return response()->json(['error' => true, 'message' => "Thanks, but that password is sadly not strong enough."]);
        }

        if (in_array($request->password, $weakPasswords)) {
            return response()->json(['error' => true, 'message' => "._. Please make a stronger password"]);
        }

        return response()->json(['error' => false, 'message' => 'OK'], 200);
    }

    public function getUserItems($userID)
    {
        $inventory = Inventory::where('user_id', $userID)->get();
        // Changed 'first()' to 'get()' to retrieve all items

        $items = $inventory->map(function ($itemData) {
            $item = $itemData->item;
            if ($item) {
                $item->creator = $item->creator;
                $item->thumbnail = $item->thumbnail();
                return $item;
            }
        })->filter();

        return response()->json($items);
    }

    public function getUserCurrentlyWearing($userID)
    {
        $avatar = Avatar::where('user_id', $userID)->first();

        if (!$avatar) {
            return response()->json(['error' => 'No avatar record found.']); // No avatar found for this user
        }

        $wearingItems = $avatar->WearingItems();

        $items = [];
        foreach ($wearingItems as $slot => $item) {
            if ($item) {
                $item->slot = $slot;
                $item->creator = $item->creator;
                $item->thumbnail = $item->thumbnail();
                $items[] = $item;
            }
        }

        return $items;
    }
}
