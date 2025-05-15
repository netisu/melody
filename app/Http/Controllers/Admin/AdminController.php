<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Item;
use App\Models\UsernameHistory;
use App\Models\Avatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\IpLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use App\Jobs\ItemPreviewRenderer;
use App\Jobs\ItemRenderer;
use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use App\Services\SendItemWebhook;
use App\Models\Space;

class AdminController extends Controller
{

    public function AdminIndex()
    {
        $admin = Admin::where('user_id', Auth::user()->id)->first();
        return inertia('Admin/Dashboard', [
            'stats' => [
                'adminPoints' => $admin->adminPoints,
                'canControlMarketPurchases' => $admin->rolePermissions('can_enable_market_purchases') ?? false,
                'canControlDiscussion' => $admin->rolePermissions('can_enable_discussion') ?? false,
                'canControlCustomization' => $admin->rolePermissions('can_enable_customization') ?? false,
                'canControlSpaces' => $admin->rolePermissions('can_enable_spaces') ?? false,
                'canControlMaintenance' => $admin->rolePermissions('can_activate_maintenance') ?? false,
                'canEnableAnnouncement' => $admin->rolePermissions('can_enable_announcement') ?? false,
            ],
            'siteSettings' => [
                'inMaintenance' => siteSetting("site_maintenance"),
                'ForumActive' => siteSetting("discussion_enabled"),
                'MarketActive' => siteSetting("market_enabled"),
                'CustomizationActive' => siteSetting("market_enabled"),
                'SpacesActive' => siteSetting("market_enabled"),
            ],
        ]);
    }

    public function ContentApprovingIndex()
    {
        $admin = Admin::where('user_id', Auth::user()->id)->first();
        if ($admin->rolePermissions('can_manage_items') ?? false != false):
            $pendingItems = Item::where('status', 'pending')->with('creator')->get();

            $pendingSpaces = Space::where('thumbnail_pending', true)->with('creator')->get();

            $pendingItemsAndSpaces = $pendingItems->merge($pendingSpaces);

            $results = [];

            foreach ($pendingItemsAndSpaces as $itemOrSpace) {
                if ($itemOrSpace instanceof Item) {
                    $results[] = [
                        'type' => 'item',
                        'data' => [
                            'id' => $itemOrSpace->id,
                            'thumbnail' => $itemOrSpace->thumbnail(),
                            'name' => $itemOrSpace->name,
                            'description' => $itemOrSpace->description,
                            'DateHum' => $itemOrSpace->DateHum,
                            'creator' => [
                                'id' => $itemOrSpace->creator->id,
                                'username' => $itemOrSpace->creator->username,
                            ],
                        ],
                    ];
                } elseif ($itemOrSpace instanceof Space) {
                    $results[] = [
                        'type' => 'space',
                        'data' => [
                            'id' => $itemOrSpace->id,
                            'thumbnail' => $itemOrSpace->thumbnail(),
                            'name' => $itemOrSpace->name,
                            'description' => $itemOrSpace->description,
                            'DateHum' => $itemOrSpace->DateHum,
                            'creator' => [
                                'id' => $itemOrSpace->creator->id,
                                'username' => $itemOrSpace->creator->username,
                            ],
                        ],
                    ];
                }
            }

            return inertia('Admin/Assets/Approve', [
                'pendingAssets' => $results,
            ]);
        else:
            $response =  inertia('Admin/Error/PermissionError', [
                'permission' => 'can_manage_items'
            ]);
        endif;

        return $response;
    }

    public function GiftUser(int $userID)
    {
        $user = User::where(['id' => $userID])->first();
        $admin = Admin::where('user_id', Auth::user()->id)->first();

        if (!$user) {
            abort(404);
        }

        if ($userID == Auth::id() && !Auth::user()->CurrentPositionID() != 1) {
            abort(403);
        }

        if ($admin->rolePermissions('can_gift_users') ?? false != false):
            $response = inertia('Admin/Users/Gift', [
                'userID' => $userID,
            ]);
        else:
            $response =  inertia('Admin/Error/PermissionError', [
                'permission' => 'can_gift_users'
            ]);
        endif;

        return $response;
    }

    public function messagingIndex()
    {
        return inertia('Admin/Messaging/Index', []);
    }
    public function UserIndex()
    {
        // Define a cache key for this query result
        $cacheKey = 'admin_users_index';
        $users = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return User::orderBy('id', 'asc')->paginate(10)->through(function ($user) {
                return [
                    'id' => $user->id,
                    'isBanned' => $user->hasActiveBan(),
                    'isStaff' => $user->isStaff(),
                    'position' => $user->CurrentPosition(),
                    'username' => $user->username,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'DateHum' => $user->DateHum,
                    'headshot' => $user->headshot(),
                ];
            });
        });
        return inertia('Admin/Users/Index', [
            'users' => $users
        ]);
    }

    public function ItemIndex()
    {
        // Define a cache key for this query result
        $cacheKey = 'item_index';
        $items = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return Item::where("creator_id", "=", config("Values.system_account_id"))->orderBy('id', 'asc')->paginate(10)->through(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'isOffsale' => $item->offsale ?? false,
                    'thumbnail' => $item->thumbnail(),
                ];
            });
        });
        return inertia('Admin/Items/Index', [
            'items' => $items
        ]);
    }

    public function CreateIndex()
    {
        $admin = Admin::where('user_id', Auth::user()->id)->first();

        if ($admin->rolePermissions('can_manage_items') ?? false != false):
            $response =  inertia('Admin/Items/Create');
        else:
            $response =  inertia('Admin/Error/PermissionError', [
                'permission' => 'can_manage_items'
            ]);
        endif;

        return $response;
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

    public function uploadItem(Request $request)
    {
        $this->validateRequest($request);
        $itemHashName = bin2hex(random_bytes(22));
        $previewName = "{$itemHashName}_preview";

        if ($request->type != 'head') {
            $info = getimagesize($request->file('image'));
            if ($info[2] == IMAGETYPE_JPEG) {
                throw ValidationException::withMessages(['image' => 'This file needs to be a true PNG, none of that sneaky jpeg-in-disguise business.']);
            }
        }
        if (Auth::user()->isStaff() && $request->hasFile('modal') && $request->type === 'hat' || $request->type === 'addon' || $request->type === 'tool') {
            // Upload OBJ only if item type deals with models
            $this->uploadOBJ($request->file('modal'), $itemHashName);
            $this->uploadImage($request->file('image'), $itemHashName);
        } elseif ($request->type === 'face') {
            $this->uploadToThumbnails($request->file('image'), $itemHashName);
            $this->uploadImage($request->file('image'), $itemHashName);
        } elseif ($request->type === 'head') {
            $this->uploadOBJ($request->file('modal'), $itemHashName);
        } else {
            throw ValidationException::withMessages(['image' => 'One of your fields are empty']);
        }

        $item = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'hash' => $itemHashName,
            'creator_id' => config('Values.system_account_id'),
            'item_type' => $request->type,
            'status' => 'approved',
            'public_view' => false,
            'cost_coins' => $request->price_coins,
            'cost_bucks' => $request->price_bucks,
            'avatar_preview' => $previewName,
            'onsale' => true,
        ]);

        $inventory = new Inventory;

        $inventory->create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
        ]);

        if ($request->type !== 'face') {
            ItemRenderer::dispatch($item->id)->onQueue('render');
            ItemPreviewRenderer::dispatch($item->id, true, $itemHashName)->onQueue('render');
        }

        if ($request->type === 'face') {
            ItemPreviewRenderer::dispatch($item->id, true, $itemHashName)->onQueue('render');
        }

        $itemUrl = route('store.item', $item->id);

        $formattedPrice = [
            $request->price_coins . 'Coins',
            $request->price_bucks . 'Stars',
        ];

        $sendWebhook = new SendItemWebhook;
        $sendWebhook->sendDiscordNotification($item->item_type, $item->name, $item->description, $formattedPrice, $item->thumbnail(), $itemUrl);


        return inertia()->location($itemUrl);
    }

    private function uploadToThumbnails(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('thumbnails', $file, "{$name}.png");

        return $path;
    }

    private function uploadImage(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('uploads', $file, "{$name}.png", 'public');
        return $path;
    }

    private function uploadOBJ(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('uploads', $file, "{$name}.obj", 'public');
        return $path;
    }
    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'description' => 'string|max:255',
            'type' => 'required|string|in:hat,addon,tool,face,head', // Allowed item types
            'image' => 'required|image|mimes:png|max:2048', // Validate PNG image
            'modal' => 'required|file|mimes:text/plain|max:2048', // Validate Object
        ]);

        if ($validator->fails()) { // Use Validator object's fails method on $validator
            return ValidationException::withMessages([$validator->errors()]);
        }
    }

    public function ManageUser(int $id)
    {
        $admin = Admin::where('user_id', Auth::user()->id)->first();
        // Define a cache key for this query result
        $cacheKey = 'users_manage_' . $id;
        $user = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return User::where(['id' => $id])->first();
        });

        if (!$user) {
            abort(404);
        }

        $joindate = Carbon::parse($user->created_at)->format('M d Y');
        $uh = $user->pastUsernames()->get();

        return inertia('Admin/Users/Manage', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'display_name' => $user->display_name,
                'lastIp' => $user->LastIp(),
                'email' => $user->email,
                'about_me' => $user->about_me,
                'thumbnail' => $user->thumbnail(),
                'bucks' => $user->bucks,
                'coins' => $user->coins,
                'DateHum' => $user->date_hum,
                'joined' => $joindate,
                'past_usernames' => $uh,
                'settings' => [
                    'profile_banner_enabled' => $user->settings->profile_banner_enabled,
                    'profile_banner_url' => $user->settings->profile_banner_url,
                ],
            ],
            'permissions' => [
                'canMangeUser' => $admin->rolePermissions('can_manage_users') ?? false,
                'canMangeUserSettings' => $admin->rolePermissions('can_manage_user_settings') ?? false,
                'canGiftUser' => $admin->rolePermissions('can_gift_users') ?? false,

            ],
        ]);
    }

    public function ManageItem(int $id)
    {
        // Define a cache key for this query result
        $cacheKey = 'item_manage' . $id;
        $item = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return Item::where(['id' => $id])->first();
        });
        if (!$item) {
            abort(404);
        }

        return inertia('Admin/Items/Manage', [
            'item' => [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'sold' => $item->owners(),
                'name' => $item->name,
                'description' => $item->description,
                'thumbnail' => $item->thumbnail(),
                'cost_bucks' => $item->cost_bucks,
                'cost_coins' => $item->cost_coins,
                'isRare' => $item->rare ?? false,
                'isOnsale' => $item->onsale ?? false,
                'initial_stock' => $item->initial_stock ?? 0,
                'remaining_stock' => $item->remaining_stock ?? 0,
            ]
        ]);
    }
}
