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
use App\Models\ItemEditStyle;

class AvatarController extends Controller
{
    public function getItemsByCategory(string $category = "hat")
    {
        $inventory = Inventory::where('user_id', Auth::id())
            ->where('ownable_type', Item::class)
            ->whereHas('ownable', function ($query) use ($category) {
                $query->where('item_type', $category);
            })
            ->paginate(24);

        return response()->json($inventory);
    }

    private function regenerate(): void
    {
        UserRenderer::dispatch(Auth::user()->id)->onQueue('user-render');
    }

    public function WearItem(Request $request, int $itemId, string $slot): JsonResponse
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $avatar = $user->avatar(); // Get the avatar instance
        $item = Item::where('id', '=', $itemId)->first();
        $editStyleId = $request->input('edit_style_id');

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item record not found.'], 404);
        }

        if (!$avatar) {
            return response()->json([
                "success" => false,
                "message" => "Avatar record not found.",
                "type" => "error",
            ], 404);
        }

        if ($editStyleId !== null) {
            $editStyle = ItemEditStyle::where('id', $editStyleId)->where('item_id', $itemId)->first();
            if (!$editStyle) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid edit style provided for this item.",
                    "type" => "error",
                ], 400);
            }
        }

        $slotName = $slot;
        if ($item->item_type === "hat" && $slot !== 'none' && $slot !== null) {
            if (!in_array($slot, range(1, 6))) {
                return response()->json([
                    "success" => false,
                    "thumbnail" => $user->thumbnail(),
                    "message" => "Invalid hat slot. Please choose between 1 and 6.",
                    "type" => "error",
                ], 400);
            }
            $slotName = "hat_" . $slot;
            foreach (['hat_1', 'hat_2', 'hat_3', 'hat_4', 'hat_5', 'hat_6'] as $checkSlot) {
                if ($checkSlot !== $slotName) {
                    $equipped = $avatar->getEquippedItemAndStyle($checkSlot);
                    if ($equipped && $equipped->item && $equipped->item->id === $item->id) {
                        return response()->json(['success' => false, 'message' => 'This item is already assigned to another hat slot.', 'type' => 'error'], 400);
                    }
                }
            }
        } else {
            $slotName = $item->item_type; // For non-hats, slot name is item_type
        }
        // Generic checks

        if (!$user->ownsItem($item->id)) {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                'message' => 'You do not own this item.',
                'type' => 'error'
            ], 403);
        }

        if ($item->status !== 'approved') {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                'message' => 'This item is not approved.',
                'type' => 'error'
            ], 403);
        }

        if ($item->public_view === false) {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                'message' => 'This item is hidden.',
                'type' => 'error'
            ], 403);
        }

        if ($item->item_type === "hat" && !in_array($slot, range(1, 6)) && $slot !== 'none') {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                "message" => "Invalid hat slot. Please choose between 1 and 6.",
                "type" => "error",
            ], 400);
        }

        // Update the JSON column for the slot
        $avatar->setAttribute($slotName, [
            'item_id' => $itemId,
            'edit_style_id' => $editStyleId,
        ]);

        $avatar->save();
        $this->regenerate();

        return response()->json([
            "success" => true,
            "message" => "You have worn this item.",
            "type" => "success",
            "thumbnail" => $user->thumbnail(), // Updated thumbnail URL
            "wearing_items" => $avatar->getWearingItemsStructured(),
        ], 200);
    }
    public function RemoveItem(Request $request, int $itemId, string $slot): JsonResponse
    {
        $editStyleId = $request->input('edit_style_id');

        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $avatar = $user->avatar();
        $item = Item::where('id', '=', $itemId)->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item record not found.'], 404);
        }
        if (!$avatar) {
            return response()->json([
                "success" => false,
                "message" => "Avatar record not found",
                "type" => "error",
            ], 404);
        }

        $slotName = $slot;
        if ($item->item_type === "hat" && $slot !== 'none' && $slot !== null) {
            if (!in_array($slot, range(1, 6))) {
                return response()->json([
                    "success" => false,
                    "thumbnail" => $user->thumbnail(),
                    "message" => "Invalid hat slot. Please choose between 1 and 6.",
                    "type" => "error",
                ], 400);
            }
            $slotName = "hat_" . $slot;
        } else {
            $slotName = $item->item_type; // For non-hats, slot name is item_type
        }

        // Verify the item and style are actually worn in this slot before removing
        $currentlyWorn = $avatar->getEquippedItemAndStyle($slotName);

        if (!$currentlyWorn || !$currentlyWorn->item || $currentlyWorn->item->id !== $itemId) {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                "message" => "This item is not currently worn in the specified slot.",
                "type" => "error",
            ], 400);
        }

        if ($editStyleId !== null && (!$currentlyWorn->edit_style_details || $currentlyWorn->edit_style_details->id !== $editStyleId)) {
            return response()->json([
                "success" => false,
                "thumbnail" => $user->thumbnail(),
                "message" => "The specified edit style is not currently active for this item in this slot.",
                "type" => "error",
            ], 400);
        }


        $avatar->setAttribute($slotName, null);

        $avatar->save();
        $this->regenerate();

        return response()->json([
            "success" => true,
            "message" => "You have taken off this item.",
            "type" => "success",
            "thumbnail" => $user->thumbnail(),
            "wearing_items" => $avatar->getWearingItemsStructured(),
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
                $hatData = $avatar->{$column};

                if (is_array($hatData) && isset($hatData['item_id']) && $hatData['item_id'] !== null) {
                    $itemId = $hatData['item_id'];
                    $hat = Item::find($itemId);
                    if ($hat) {
                        $editStyleId = $hatData['edit_style_id'] ?? null;
                        $editStyle = null;
                        if ($editStyleId) {
                            $editStyle = ItemEditStyle::find($editStyleId);
                        }
                        $wearingHats->push([
                            'id' => $hat->id,
                            'type' => $hat->item_type,
                            'name' => $hat->name,
                            'thumbnail' => $hat->thumbnail(),
                            'edit_style' => $editStyle ? (object)['hash' => $editStyle->hash, 'is_model' => $editStyle->is_model, 'is_texture' => $editStyle->is_texture] : null,

                        ]);
                    }
                }
            }

            return $wearingHats;
        });

        return response()->json($items);
    }
}
