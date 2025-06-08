<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Event
{
    public $keys = [
        'crackingup' => 199,
        'enigmaticEgg' => 209,
        'invertedeggy212' => 212,
        'gettrolled' => 194,
    ];  // Array of key strings associated with item IDs (key => item ID)

    public function grantItem(Item $item, User $user, string $key, bool $internalRedeem = false): bool
    {
        // Validate key existence
        if (!array_key_exists($key, $this->keys) || $this->keys[$key] !== $item->id) {
            Log::error("Invalid key for granting item: $key (user: {$user->id}, item: {$item->id})"); // Log error details
            return false;
        }

        $lockKey = 'event_' . $user->id . '_lock';
        if (!Cache::lock($lockKey, 5)) {
            Log::error("Failed to acquire lock for granting item (user: {$user->id}, item: {$item->id})"); // Log error details
            return false;
        }

        try {
            // Check if user already has the item
            if ($user->ownsItem($item->id, Item::class)) {
                Log::error("User already owns item: $key (user: {$user->id}, item: {$item->id})"); // Log error details
                return false;
            } else {
                // Grant the item
                $item->inventories()->create([
                    'ownable_type' => Item::class,
                ]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error granting item (user: {$user->id}, item: {$item->id}): " . $e->getMessage()); // Log error details
            return false;
        } finally {
            optional(Cache::store())->forget($lockKey); // Release lock using current store
        }
    }
}
