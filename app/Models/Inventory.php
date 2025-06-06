<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'user_id',
        'ownable_id',   // <--- Changed from item_id
        'ownable_type', // <--- Added
    ];

    /**
     * Get the user that owns the inventory item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning model of the inventory entry (Item, ItemEditStyle, Achievement, etc.).
     */
    public function ownable(): MorphTo
    {
        return $this->morphTo();
    }
}
