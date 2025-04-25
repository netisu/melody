<?php

namespace App\Http\Controllers\Endpoints;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Avatar;
use Mockery\Matcher\Any;
use App\Models\User;
use App\Jobs\UserRenderer;
use Illuminate\Http\JsonResponse;

class AvatarController extends Controller
{
    public function getItemsByCategory(string $category = "hat")
    {
        $inventory = Inventory::where('user_id', Auth::id())
            ->whereHas('item', function ($query) use ($category) {
                $query->where('item_type', $category);
            })
            ->paginate(24);
        // Paginate the inventory with 24 items per page, filtered by item_type

        $paginatedItems = $inventory->through(function ($itemData) {
            $item = $itemData->item;
            if ($item) {
                $item->creator = $item->creator;
                $item->thumbnail = $item->thumbnail();
                return $item;
            }
            return null;
        })->filter();

        return response()->json($inventory);
    }

    private function regenerate(): void
    {
        UserRenderer::dispatch(Auth::user()->id)->onQueue('user-render');
    }

    public function WearItem(int $itemId, int | string $slot)
    {
        // Fetch the avatar and item record
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $avatar = $user->avatar();
        $item = Item::where('id', '=', $itemId)->first();

        if (!$item) {
            return response()->json(['error' => 'Item reccord not found.']);
        }
        // Check if avatar record exists
        if (!$avatar) {
            return response()->json([
                "message" => "Avatar record not found.",
                "type" => "error",
            ], 404);
        }

        if ($item->item_type == "hat" && $slot != 'none' && $slot != null) {
            $hatSlot = "hat_" . $slot;

            // Check if another slot exists with the same item
            foreach (['hat_1', 'hat_2', 'hat_3', 'hat_4', 'hat_5', 'hat_6'] as $checkSlot) {
                if ($checkSlot !== $hatSlot && $avatar->$checkSlot == $item->id) {
                    return response(['error' => 'This item is already assigned to another slot.']);
                }
            }
        }


        // finally ownership checking
        if (!$user->ownsItem($item->id)) {
            return response()->json([
                "thumbnail" => $user->thumbnail(),
                'error' => 'You do not own this item.'
            ]);
        }

        // also finally status checking LOL
        if ($item->status !== 'approved') {
            return response()->json([
                "thumbnail" => $user->thumbnail(),
                'error' => 'This item is not approved.'
            ]);
        }

        if ($item->public_view === false) {
            return response()->json([
                "thumbnail" => $user->thumbnail(),
                'error' => 'This item is hidden.'
            ]);
        }


        if ($item->item_type == "hat" && !in_array($slot, range(1, 6)) && $slot != 'none') {
            return response()->json([
                "thumbnail" => $user->thumbnail(),
                "message" => "Invalid hat slot. Please choose between 1 and 6.",
                "type" => "error",
            ], 400); // Bad request status code
        }

        // Update the specific item based on type and id
        if ($item->item_type == "hat" && $slot != 'none' && $slot != null) {
            $avatar->setAttribute($hatSlot, $itemId);
        } else {
            $avatar->setAttribute($item->item_type, $itemId);
        }


        // Save the updated avatar record
        $avatar->save();

        $this->regenerate();

        return response()->json([
            "thumbnail" => $user->thumbnail(),
            "message" => "You have worn this item",
            "type" => "success",
        ], 200);
    }
    public function RemoveItem(int $itemId, int | string $slot)
    {
        // Fetch the avatar and item record
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $avatar = $user->avatar();
        $item = Item::where('id', '=', $itemId)->first();

        // Check if avatar record exists
        if (!$avatar) {
            return response()->json([
                "message" => "Avatar record not found",
                "type" => "error",
            ], 404);
        }

        // Validate slot number (1-6)
        if ($item->item_type == "hat" && $slot != 'none' && $slot != null) {
            $hatSlot = "hat_" . $slot; // Define $hatSlot here within the if block
        }


        if ($item->item_type == "hat" && !in_array($slot, range(1, 6)) && $slot != 'none') {
            return response()->json([
                "thumbnail" => $user->thumbnail(),
                "message" => "Invalid hat slot. Please choose between 1 and 6.",
                "type" => "error",
            ], 400); // Bad request status code
        }

        // Update the specific item based on type and id
        if ($item->item_type == "hat" && $slot != 'none' && $slot != null) {
            $avatar->{$hatSlot} = null;
        } else {
            $avatar->{$item->item_type} = null;
        }


        // Save the updated avatar record
        $avatar->save();

        $this->regenerate();

        return response()->json([
            "thumbnail" => $user->thumbnail(),
            "message" => "You have taken off this item",
            "type" => "success",
        ], 200);
    }


    public function getWearingHats()
{
    $cacheKey = 'wearing_hats.' . Auth::id();

    $items = Cache::remember($cacheKey, now()->addMinutes(5), function () {
        $avatar = Avatar::where('user_id', Auth::id())->first();

        if (!$avatar) {
            return collect(); // Return an empty collection if no avatar is found for the user
        }

        $hatColumns = ['hat_1', 'hat_2', 'hat_3', 'hat_4', 'hat_5', 'hat_6'];
        $wearingHats = collect();

        foreach ($hatColumns as $column) {
            if ($avatar->{$column} !== null) {
                $hat = Item::find($avatar->{$column});
                if ($hat) {
                    $wearingHats->push([
                        'id' => $hat->id,
                        'type' => $hat->item_type,
                        'name' => $hat->name,
                        'thumbnail' => $hat->thumbnail(),
                    ]);
                }
            }
        }

        return $wearingHats;
    });

    return response()->json($items);
}
}
