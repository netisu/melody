<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avatar extends Model
{
    use HasFactory;

    protected $table = 'user_avatars';

    protected $fillable = [
        'user_id',
        'image',
        'hat_1',
        'hat_2',
        'hat_3',
        'hat_4',
        'hat_5',
        'hat_6',
        'addon',
        'head',
        'face',
        'tool',
        'tshirt',
        'shirt',
        'pants',
        'colors',
    ];

    protected $casts = [
        'hat_1' => 'array',
        'hat_2' => 'array',
        'hat_3' => 'array',
        'hat_4' => 'array',
        'hat_5' => 'array',
        'hat_6' => 'array',
        'addon' => 'array',
        'head' => 'array',
        'face' => 'array',
        'tool' => 'array',
        'tshirt' => 'array',
        'shirt' => 'array',
        'pants' => 'array',
        'colors' => 'array',
    ];

    /**
     * Get the user that owns the avatar.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to get an equipped item and its applied edit style.
     *
     * @param string $slotName The name of the slot (e.g., 'hat_1', 'shirt', 'head').
     * @return object|null An object containing 'item' (Item::class) and 'edit_style' (ItemEditStyle::class), or null.
     */
    private function getEquippedItemAndStyle(string $slot): ?object
    {
        $slotData = $this->{$slot} ?? null;

        // If no data for this slot, or if it's not in the expected array format return null.
        if (!$slotData || !is_array($slotData)) {
            return null;
        }

        $item = null;
        // Check if 'id' key exists in the $slotData (which is like ['id' => 1, 'edit_style_id' => 2])
        if (isset($slotData['id'])) {
            $item = Item::find($slotData['id']); // Load the full Item model
        }

        $editStyle = null;
        if (isset($slotData['edit_style_id']) && $slotData['edit_style_id'] !== null) {
            $editStyle = ItemEditStyle::find($slotData['edit_style_id']); // Load the full ItemEditStyle model
        }

        if ($item || $editStyle) {
            return (object) [
                'item' => $item,
                'edit_style_details' => $editStyle,
            ];
        }

        return null; // Nothing equipped in this specific slot
    }

    /**
     * Get all equipped items and their styles in a structured array.
     */
    public function getWearingItemsStructured(): array
    {
        $slots = [
            'hat_1', 'hat_2', 'hat_3', 'hat_4', 'hat_5', 'hat_6',
            'addon', 'head', 'face', 'tool', 'tshirt', 'shirt', 'pants',
        ];

        $wearing = [];
        foreach ($slots as $slot) {
            $wearing[$slot] = $this->getEquippedItemAndStyle($slot);
        }

        $wearing['colors'] = $this->colors ?? [
            'head' => 'd3d3d3',
            'torso' => '055e96',
            'left_arm' => 'd3d3d3',
            'right_arm' => 'd3d3d3',
            'left_leg' => 'd3d3d3',
            'right_leg' => 'd3d3d3',
        ];

        return $wearing;
    }

    /**
     * Retrieves the specific hat item (and its style) for a given hat number.
     * This is a convenience method now wrapper around getEquippedItemAndStyle.
     */
    public function hat(int $num): ?object
    {
        return $this->getEquippedItemAndStyle("hat_{$num}");
    }
    // Example usage: $avatar->getEquippedItemAndStyle('face');


    /**
     * Resets the avatar to its default state, handling JSON columns.
     */
    public function resetAvatar(): void
    {
        // Set the paths for thumbnail and headshot (if they are still part of the image column)
        $thumbnail = "thumbnails/{$this->image}.png";
        $headshot = "thumbnails/{$this->image}_headshot.png";

        $defaultAttributes = [
            'image' => 'default',
            'hat_1' => null,
            'hat_2' => null,
            'hat_3' => null,
            'hat_4' => null,
            'hat_5' => null,
            'hat_6' => null,
            'addon' => null,
            'head' => null,
            'face' => null,
            'tool' => null,
            'tshirt' => null,
            'shirt' => null,
            'pants' => null,
            'colors' => [
                'head' => 'd3d3d3',
                'torso' => '055e96',
                'left_arm' => 'd3d3d3',
                'right_arm' => 'd3d3d3',
                'left_leg' => 'd3d3d3',
                'right_leg' => 'd3d3d3',
            ],
        ];

        // Update the model with the default values and save it
        $this->fill($defaultAttributes);
        $this->save();
    }
}
