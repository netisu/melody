<?php

namespace App\Http\Controllers\Endpoints;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Kaankilic\WtFilter\Facades\WtFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;

use App\Models\{
    Item,
    Admin,
    Space,
    User,
    Inventory,
    SiteSettings,
    UsernameHistory
};
use App\Notifications\GiftNotification;

class AdminController extends Controller
{
    private $site;
    private $admin;

    public function __construct()
    {
        $userId = Auth::check() ? Auth::user()->id() : null;

        $this->admin = Admin::where( 'user_id', $userId)->first();

        $isProduction = app()->environment('production');

        if ($isProduction) {
            // Check if the settings are cached
            $this->site = Cache::remember('siteSettings', now()->addMinutes(30), function () {
                return SiteSettings::find(1); // Change to use the find() method instead of where() + first()
            });
        } else {
            // If not in production, retrieve settings without caching
            $this->site = SiteSettings::find(1);
        }
    }

    public function enableMaintenance(Request $request)
    {

        if (!$this->admin->rolePermissions("can_activate_maintenance")) {
            return response()->json([
                'message' => 'Unauthorized',
                'type' => 'danger',
            ]);
        }

        $this->site->site_maintenance = 1;
        $this->site->save();

        return response()->json([
            'message' => 'Maintenance Enabled',
            'type' => 'success'
        ]);
    }

    public function ModeratePendingAsset(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isStaff() && !$this->admin->rolePermissions("can_activate_maintenance") ?? false) {
            abort(403); // Immediately return a 403 for unauthorized users
        }

        $ModerateType = $request->type;

        $this->site = SiteSettings::find(1);

        $this->site->site_maintenance = 1;
        $this->site->save();
    }

    public function denyPendingItem(Request $request)
    {

        if (!$this->admin->rolePermissions("can_activate_maintenance") ?? false) {
            abort(403); // Immediately return a 403 for unauthorized users
        }
        $this->site = SiteSettings::find(1);

        $this->site->site_maintenance = 1;
        $this->site->save();
    }
    public function approveBanner(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    $userSettings = $user->settings;

    if ($userSettings->banner_status === 'pending' && $userSettings->pending_banner_path) {
        // Determine the final storage path
        $finalPath = 'public/banners/' . basename($userSettings->pending_banner_path);

        // Copy the temporary image to the final location
        Storage::move($userSettings->pending_banner_path, $finalPath);

        // Update the settings
        $userSettings->profile_banner_url = str_replace('public/', 'storage/', $finalPath);
        $userSettings->banner_status = 'approved';
        $userSettings->pending_banner_path = null; // Clean up
        $userSettings->save();

        return response()->json(['message' => 'Banner approved successfully for user ' . $userId]);
    }

    return response()->json(['error' => 'Invalid banner status or pending path not found.'], 400);
}

    public function giftItem(int $itemId, int $userId)
    {
        if ((!$this->admin->rolePermissions("can_gift_users"))) {
            return response()->json([
                'message' => 'Unauthorized',
                'type' => 'danger',
            ]);
        }

        $user = User::where(['id' => $userId])->first();
        $item = Item::where(['id' => $itemId])->first();
        if (!$item) {
            return response()->json([
                'message' =>  'Item Record not found',
                'type' => 'danger'
            ]);
        }

        if (!$user) {
            return response()->json([
                'message' =>  'User Record not found',
                'type' => 'danger'
            ]);
        }


        $inventory = new Inventory;

        $inventory->create([
            'user_id' => $userId,
            'item_id' => $itemId,
        ]);

        $user->notify(new GiftNotification(Auth::user(), $item));

        return response()->json([
            'message' =>  $item->name . ' has been gifted to ' . $user->username . '.',
            'type' => 'success'
        ]);
    }

    public function purge(string $type, int $assetId)
    {
        $canManage = [
            'item' => 'can_manage_items',
            'user' => 'can_manage_users',
            'space' => 'can_manage_spaces',
        ];

        if ((!$this->admin->rolePermissions($canManage[$type]))) {
            return response()->json([
                'message' => 'Unauthorized',
                'type' => 'danger',
            ]);
        }

        if ($type == "item") {
            $item = Item::where('id', $assetId)->first();

            $item->update([
                'name' => "[netisu-item:{$item->id}]"
            ]);
        } else if ($type == "user") {
            $user = User::where('id', $assetId)->first();
            $profanityList = Arr::wrap(config('filter.blacklist'));

            // Normalize username (for profanity check only)
            $normalizedName = strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $user->username));

            $containsProfanity = false; // Flag to track profanity

            foreach ($profanityList as $word) {
                if (stripos($normalizedName, $word) !== false) {
                    $containsProfanity = true;
                    break;
                }
            }

            if ($user->username != "[netisu-user:{$user->id}]" && !$containsProfanity) {
                $usernameHistory = new UsernameHistory;
                $usernameHistory->create([
                    'user_id' => $user->id,
                    'ip' => $user->lastIP(),
                    'name' => $user->username
                ]);
            }
            if ($user->username == "[netisu-user:{$user->id}]") {
                $oldusername = UsernameHistory::where('user_id', '=', $user->id)->first();
                $user->update([
                    'display_name' => $oldusername->name,
                    'username' => $oldusername->name
                ]);
            } else {
                $user->update([
                    'display_name' => "[netisu-user:{$user->id}]",
                    'username' => "[netisu-user:{$user->id}]"
                ]);
            }
        } else if ($type == "space") {
            $space = Space::where('id', $assetId)->first();
            $space->update([
                'name' => "[netisu-space:{$space->id}]",
            ]);
        }
    }

    public function disableMaintenance(Request $request)
    {

        if ((!$this->admin->rolePermissions("can_activate_maintenance"))) {
            return response()->json([
                'message' => 'Unauthorized',
                'type' => 'danger',
            ]);
        }
        $this->site = SiteSettings::find(1);

        $this->site->site_maintenance = 0;
        $this->site->save();

        return response()->json([
            'message' => 'Maintenance Disabled',
            'type' => 'success'
        ]);
    }
}
