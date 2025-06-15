<?php

namespace App\Http\Controllers\Endpoints;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\CrateItem;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\ItemReseller;
use App\Models\ItemPurchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Stevebauman\Location\Facades\Location;
use App\Jobs\ItemPreviewRenderer;
use App\Jobs\ItemRenderer;

class ItemApiController extends Controller
{

    public function getItemsByCategory(Request $request, $category)
    {
        // Define the getEventItems function before calling it
        $categories = $this->getCategoryData();
        //dd($categories);

        // Check if requested category exists
        if (isset($categories[$category])) {
            $categoryData = $categories[$category];
        } else {
            // Check if requested category matches a subcategory internal value
            foreach ($categories as $categoryName => $categoryDetails) {
                foreach ($categoryDetails as $subCategory) {
                    if (isset($subCategory['internal']) && $subCategory['internal'] === $category) {
                        $categoryData = $categories[$categoryName];
                        break 2; // Exit both loops after finding a match
                    }
                }
            }
        }

        // Handle category not found
        if (!isset($categoryData)) {
            return response()->json(
                [
                    "message" => "Invalid category",
                    "type" => "danger",
                ],
                500
            );
        }

        $items = [];

        $categoryPossibleNames = [
            "showpiece_*",
            "exclusive_*",
            "collectible_*",
            "limited_*",
            "special_*"
        ];

        $internalValue = $category ?? null; // Handle both object and array access, null if not present

        $matchFound = false;
        if ($internalValue !== null) { // Only proceed if the internal value exists
            foreach ($categoryPossibleNames as $pattern) {
                if (fnmatch($pattern, $internalValue)) {
                    $matchFound = true;
                    break; // Exit the loop once a match is found
                }
            }
        }

        // Handle all of the "Exclusives" category
        if ($matchFound) {
            $exclusiveType = $category;
            if ($exclusiveType === "exclusive_all") {
                $items = Item::where(['rare' => true, 'status' => 'approved', 'public_view' => true])->with('creator')->orderBy('updated_at')->get();
            } else {
                $exclusiveType = str_replace("exclusive_", "", $exclusiveType); // Change to str_replace instread of explode
                $items = Item::where(['rare' => true, 'item_type' => $exclusiveType, 'status' => 'approved', 'public_view' => true])->with('creator')->orderBy('updated_at')->get();
            }
        } else {
            // Get items for the specific category
            $items = Item::where(['item_type' => $category, 'status' => 'approved', 'public_view' => true])->with('creator')->orderBy('updated_at')->get();
        }

        return response()->json(
            $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'thumbnail' => $item->thumbnail(),
                    'name' => $item->name,
                    'onsale' => $item->onsale,
                    'rare' => $item->rare,
                    'cost_sparkles' => $item->cost_sparkles,
                    'cost_stars' => $item->cost_stars,
                    'creator' => [
                        'username' => $item->creator->username,
                        'staff' => $item->creator->isStaff(),
                        'verified' => $item->creator->settings->verified,
                    ],
                ];
            })
        );
        
    }
    public function reRenderItem($itemID)
    {
        $item = Item::where(['id' => $itemID])->first();


        if ($item->item_type !== 'face' || $item->item_type !== 'head') {
            ItemRenderer::dispatch($item->id)->onQueue('render');
            ItemPreviewRenderer::dispatch($item->id, true, $item->hash)->onQueue('render');
        }

        if ($item->item_type === 'face' || $item->item_type === 'head') {
            ItemPreviewRenderer::dispatch($item->id, true, $item->hash)->onQueue('render');
        }


        return response()->json([
            'message' => 'Item successfully rerendered.',
            'type' => 'success'
        ]);
    }

    private function getCategoryData()
    {
        $data = config('ItemCategories');

        // Check if data is an array (optional)
        if (!is_array($data)) {
            throw new Exception('Invalid ItemCategories data structure');
        }

        return $data;
    }

    function openCrate(Request $request)
    {
        // Check user ownership and crate existence
        $crate = $this->validateCrateOwnership($request->id);
        if (!$crate) {
            return response()->json(
                [
                    "message" => "This crate does not exist or you do not own it.",
                    "type" => "danger",
                ],
                500
            );
        }

        // Get crate items and ensure they exist
        $crateItems = $crate->crateItems;
        if ($crateItems->isEmpty()) {
            return response()->json(['error' => 'This crate is empty.']);
        }

        // Determine winning item and update user inventory
        $winningItemId = CrateItem::getWinningItem($crate->id);
        $winningItem = CrateItem::where('id', '=', $winningItemId)->first();

        $this->updateUserInventory($crate, $winningItem);

        // Prepare item information for response
        $winningItemDetails = [
            'id' => $winningItem->id,
            'name' => $winningItem->item->name,
            'thumbnail' => $winningItem->item->thumbnail(),
            'url' => route('store.item', [$winningItem->item->id, $winningItem->item->slug()]),
            'rarity' => [
                'name' => CrateItem::rarityName($winningItem->rarity),
                'color' => CrateItem::rarityColor($winningItem->rarity)
            ]
        ];

        // Generate unboxing animation data
        $images = $this->generateUnboxingAnimation($crateItems);
        $winningIndex = floor(count($images) / 2);
        $images[$winningIndex] = $winningItemDetails;
        $animateLeft = ($winningIndex - 2) * 153.3; // Adjust based on image width

        // Return response with crate opening details
        return response()->json([
            ...$winningItemDetails, // Include item details from $winningItemDetails
            'unboxing_images' => $images,
            'animate_left' => "-={$animateLeft}px"
        ]);
    }

    // Helper functions for better organization
    private function validateCrateOwnership($crateId)
    {
        $crate = Item::where([
            ['id', '=', $crateId],
            ['type', '=', 'crate']
        ])->first();

        if (!$crate || !Auth::user()->ownsItem($crateId)) {
            return null;
        }

        return $crate;
    }

    private function updateUserInventory($crate, $winningItem)
    {
        $user = User::where('id',  Auth::id())->first();
        Inventory::where([
            ['user_id', '=', $user->id],
            ['item_id', '=', $crate->id]
        ])->first()->delete();

        $user->inventory()->create([
            'item_id' => $crate->id,
            'ownable_type' => Item::class,
        ]);
    }

    private function generateUnboxingAnimation($crateItems)
    {
        $images = [];
        for ($i = 0; $i < 50; $i++) {
            $itemImages = [];
            shuffle($crateItems);
            foreach ($crateItems as $crateItem) {
                $itemImages[] = [
                    'id' => $crateItem->id,
                    'src' => $crateItem->thumbnail,
                    'color' => crateRarity('color', $crateItem->rarity),
                ];
            }
            shuffle($itemImages);
            $images[] = $itemImages[0];
        }
        return $images;
    }

    public function getEventItems($eventId)
    {
        // Add return statement to get the query result
        return Item::where(['in_event' => true, 'event_id' => $eventId])->get();
    }

    public function purchase(int $itemId, string $currencyType, ?int $resellerId = null)
    {
        // Fetch item and validate access
        $item = $this->getItemAndValidateAccess($itemId);


        if ($item instanceof JsonResponse) { // Check if it's an instance of JsonResp
            return response()->json($item->original);
        }

        // Handle reseller purchase logic
        if ($resellerId) {
            return $this->handleResellerPurchase($item, $resellerId, $currencyType);
        }

        // Handle direct purchase logic
        return $this->handleDirectPurchase($item, $currencyType);
    }

    private function getItemAndValidateAccess(int $itemId)
    {
        $item = Item::where('id', $itemId)->firstOrFail();
        if (config('app.env') != 'local') {
            if (!Auth::user()->isStaff() && !$item->public_view) {
                abort(403);
            }

            if ($item->status != 'approved' && $item->public_view != true && !Auth::user()->isStaff()) {
                return response()->json(
                    [
                        "message" => "This item is not approved.",
                        "type" => "danger",
                    ],
                    500
                );
            } else if (!$item->onsale) {
                return response()->json(
                    [
                        "message" => "This item is not for sale.",
                        "type" => "danger",
                    ],
                    500
                );
            } else if (Auth::user()->ownsItem($itemId)) {
                return response()->json(
                    [
                        "message" => "You already own the item.",
                        "type" => "danger",
                    ],
                    500
                );
            } else {
                return $item;
            }
        }
        return $item;
    }

    private function handleResellerPurchase(Item $item, int $resellerId, string $currencyType)
    {
        $listing = ItemReseller::where('id', $resellerId)->first();

        if (Auth::user()->id == $listing->seller->id) {
            return response()->json(
                [
                    "message" => "You cannot buy your own item.",
                    "type" => "danger",
                ],
                500
            );
        }

        if ($currencyType != 'stars') {
            return response()->json(
                [
                    "message" => "You can only buy resold items for stars.",
                    "type" => "danger",
                ],
                500
            );
        }

        $price = $listing->price;
        $seller = $listing->seller;

        // Validate user currency
        $this->validateUserCurrency($currencyType, $price);

        // Update seller currency and deduct buyer currency
        $this->updateCurrencies($seller, $price);
        Auth::user()->{$currencyType} -= $price;

        // Transfer ownership and delete listing
        $inventory = $listing->inventory;
        $inventory->user_id = Auth::user()->id;
        $inventory->save();
        $listing->delete();

        // Handle seller wearing item case
        if (!$seller->ownsItem($item->id) && $seller->isWearingItem($item->id)) {
            $seller->takeOffItem($item->id);
            $this->regeneratewithID($resellerId, $seller->id);
        }

        // Create purchase record
        $this->createPurchaseRecord($item, $seller, $currencyType, $price);
        Auth::user()->addPoints(30);

        return response()->json(
            [
                "message" => "You now own this item.",
                "type" => "success",
            ],
            200
        );
    }

    private function handleDirectPurchase(Item $item, string $currencyType)
    {
        if (!$item->rare || $item->owners() != $item->initial_stock && $item->remaining_stock > 0) {
            $price = ($currencyType === 'sparkles') ? $item->cost_sparkles : $item->cost_stars;
            $seller = $item->creator;

            // Validate user currency
            $verifyBank = $this->validateUserCurrency($currencyType, $price);
            if ($verifyBank !== true) {
                // Handle validation errors
                return response()->json([
                    "message" => "You do not have enough " . $currencyType . " to purchase this item.", // Replace with actual error message
                    "type" => "danger"
                ], 422);
            }
            // Update seller currency and deduct buyer currency
            $myu = Auth::user();
            // Calculate the new currency value
            $newCurrencyValue = $myu->{$currencyType} - $price;


            // Update the user record
            $myu->update([
                $currencyType => $newCurrencyValue
            ]);

            $creator = User::where('id', $item->creator_id)->first();
            $newCurrencyCreatorValue = $creator->{$currencyType} + $price;

            $creator->update([
                $currencyType => $newCurrencyCreatorValue
            ]);

            // Create inventory record
            $item->inventories()->create([
                'user_id' => Auth::user()->id,
            ]);

            // Create purchase record
            $purchaseRecord = new ItemPurchase;
            $purchaseRecord->create([
                "seller_id" => $seller->id,
                "buyer_id" => Auth::user()->id,
                "item_id" => $item->id,
                "ip" => \Request::ip(),
                "currency_used" => $currencyType,
                "price" => $price,
            ]);


            Auth::user()->addPoints(5);

            // Reduce stock if applicable
            if ($item->rare && $item->owners() != $item->initial_stock && $item->remaining_stock > 0) {
                $item->remaining_stock -= 1;
                $item->save();
            }

            if ($item->rare && $item->owners() == $item->initial_stock && $item->remaining_stock == 0) {
                $item->onsale = 0;
                $item->save();
            }

            return response()->json(
                [
                    "message" => "You now own this item.",
                    "type" => "success",
                ],
                200
            );
        }

        return response()->json(
            [
                "message" => "This item is out of stock.",
                "type" => "danger",
            ],
            500
        );
    }
    private function validateUserCurrency(string $currencyType, float $price): bool
    {
        $myu = Auth::user();
        if ($myu->{$currencyType} < $price) {
            return false;
        }
        return true;
    }


    public function resell(Request $request)
    {
        $copy = Inventory::where([
            ['id', '=', $request->id],
            ['user_id', '=', Auth::user()->id]
        ])->firstOrFail();
        $isReselling = ItemReseller::where('inventory_id', '=', $copy->id)->exists();

        if (!$copy->item->limited || ($copy->item->limited && $copy->item->stock > 0) | $isReselling) abort(404);

        $request->validate($request, [
            'price' => ['required', 'numeric', 'min:1', 'max:1000000']
        ]);

        $reseller = new ItemReseller;
        $reseller->seller_id = Auth::user()->id;
        $reseller->item_id = $copy->item->id;
        $reseller->inventory_id = $copy->id;
        $reseller->price = $request->price;
        $reseller->save();
        Auth::user()->addPoints(10);
        return redirect()->route('store.item', [$copy->item->id, $copy->item->slug()])->with('success_message', 'Item has been put up for sale, You have earned 10 XP.');
    }

    public function makeOffsale(Request $request)
    {
        $copy = ItemReseller::where([
            ['id', '=', $request->id],
            ['seller_id', '=', Auth::user()->id]
        ])->firstOrFail();

        $id = $copy->item_id;
        $slug = Item::where('id', '=', $id)->first()->slug();

        $copy->delete();

        return redirect()->route('catalog.item', [$id, $slug])->with('success_message', 'Item has been taken off sale.');
    }
}
