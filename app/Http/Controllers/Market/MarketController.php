<?php

namespace App\Http\Controllers\Market;

use App\Models\User;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\ItemReseller;
use App\Models\ItemPurchase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Endpoints\RenderController;
use Illuminate\Support\Facades\Auth;
use App\Models\IpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use App\Jobs\ItemPreviewRenderer;
use Illuminate\Support\Facades\Cache;

class MarketController extends Controller
{
    public function StoreIndex()
    {
        // Define a cache key for this data
        $cacheKey = 'store_index_data';

        // Use caching to store the data for StoreIndex
        $categories = Cache::remember($cacheKey, now()->addHours(6), function () {
            return config('ItemCategories');
        });

        return inertia('Store/Index', [
            'categories' => $categories,
        ]);
    }

    public function storeItem($id)
    {
        /** @var \App\Models\User $user **/
        $authenticatedUserModel = Auth::user();

        // Define cache keys
        $cacheKey = 'store_item_' . $id;
        $recommendationCacheKey = 'recommendation_' . $id;

        // Retrieve item data with caching
        $item = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            return Item::where('id', $id)->first();
        });

        if (!$item || (!Auth::check() && !$item->public_view)) {
            abort(403);
        }

        if (!$item->public_view && Auth::check() && $item->creator_id != Auth::id() && !$authenticatedUserModel->isStaff()) {
            abort(403);
        }


        // Retrieve recommendations with caching
        $recommendations = Cache::remember($recommendationCacheKey, now()->addHours(6), function () use ($item) {
            return Item::where([
                ['id', '!=', $item->id],
                ['public_view', true],
                ['status', 'approved'],
                ['creator_id', $item->creator->id]
            ])->with('creator')->inRandomOrder()->take(6)->get();
        });

        foreach ($recommendations as $recommendation) {
            $thumbnail = $recommendation->thumbnail();
            $recommendation->thumbnail = $thumbnail;
        }


        return inertia('Store/Item', [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'item_type' => $item->item_type,
                'thumbnail' => $item->thumbnail(),
                'preview' => $item->preview(),
                'hash' => $item->hash,
                'description' => $item->description,
                'public_view' => $item->public_view,
                'status' => $item->status,
                'onsale' => $item->onsale,
                'cost_stars' => $item->cost_stars,
                'cost_sparkles' => $item->cost_sparkles,
                'DateHum' => $item->DateHum,
                'UpdateHum' => $item->UpdateHum,
                'created_at' => $item->created_at,
                'rare' => $item->rare,
                'in_event' => $item->in_event,
                'sale_ongoing' => $item->sale_ongoing,

                'creator' => [
                    'id' => $item->creator->id,
                    'username' => $item->creator->username,
                    'displayname' => $item->creator->display_name,
                    'thumbnail' => $item->creator->headshot(),
                    'staff' => $item->creator->isStaff(),
                ],
                'owners' => [
                    'list' => $item->owners(),
                    'count' => $item->owners()->count(),
                ],

            ],
            'itemOwnership' => Auth::check() ? $authenticatedUserModel->ownsItem($item->id) : false,
            'recommendations' => $recommendations,
        ]);
    }

    public function CreateIndex()
    {
        return inertia('Store/Create');
    }

    public function uploadItem(Request $request)
    {
        $this->validateRequest($request);
        $itemHashName = bin2hex(random_bytes(22));
        $previewName = "{$itemHashName}_preview";

        $info = getimagesize($request->file('image'));
        if ($info[2] == IMAGETYPE_JPEG) {
            return response()->json([
                'message' => 'This file needs to be a true PNG, none of that sneaky jpeg-in-disguise business.',
                'type' => 'danger' // Optional: specify message type for styling
            ], 502);
        }

        $this->uploadImage($request->file('image'), $itemHashName);

        $item = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'hash' => $itemHashName,
            'creator_id' => Auth::id(),
            'item_type' => $request->type,
            'status' => 'pending',
            'public_view' => false,
            'cost_sparkles' => $request->sparkles,
            'cost_stars' => $request->stars,
            'avatar_preview' => $previewName,
            'onsale' => true,
        ]);

        $inventory = new Inventory;

        $inventory->create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
        ]);

        ItemPreviewRenderer::dispatch($item->id, true, $itemHashName)->onQueue('render');

        return redirect()->route('store.item', $item->id)->with([
            'message' => 'Item created successfully!',
            'type' => 'success' // Optional: specify message type for styling
        ], 201);
    }

    private function uploadImage(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('uploads', $file, "{$name}.png", 'public');

        return $path;
    }

    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:30',
            'description' => 'required|string|max:255',
            'type' => 'required|string|in:shirt,tshirt,pants', // Allowed item types
            'image' => 'required|image|mimes:png', // Validate PNG image
            'sparkles' => 'required|integer|max:50000',
            'stars' => 'required|integer|max:10000',
        ]);

        if ($validator->fails()) { // Use Validator object's fails method on $validator
            throw ValidationException::withMessages([$validator->errors()]);
        }
    }

    public function edit(int $id)
    {
        $item = Item::findOrFail($id);
        if (!$item) {
            abort(404);
        }

        if ($item->creator_id != Auth::id()) {
            abort(403);
        }

        return inertia('Store/Edit', [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'thumbnail' => $item->thumbnail(),
                'description' => $item->name,

            ]
        ]);
    }

    public function update(Request $request, int $id)
    {
        $item = Item::findOrFail($id);
        if (!$item) {
            abort(404);
        }

        if ($item->creator_id != Auth::id()) {
            abort(403);
        }

        $item->update([
            'name' => $request->name ??  $item->name,
            'description' => $request->description ??  $item->description,
            'cost_sparkles' => $request->cost_sparkles ??  $item->cost_sparkles,
            'cost_stars' => $request->cost_stars ??  $item->cost_stars,
            'onsale' => $request->isOnsale ??  $item->onsale,
        ]);

        return redirect()->route('store.item', $item->id)->with([
            'message' => 'Item edited successfully!',
            'type' => 'success' // Optional: specify message type for styling
        ], 201);
    }
}
