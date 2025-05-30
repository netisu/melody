<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class ItemEditStyle extends Model
{
    use HasFactory;

    protected $table = 'item_edit_styles';

    protected $fillable = [
        'id', 'name', 'description', 'hash', 'is_model', 'is_texture'
    ];

    protected $casts = [
        'is_model' => 'boolean',
        'is_texture' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'id');
    }
    /**
     * An ItemEditStyle can be applied to many ItemBundleItems.
     */

    public function itemBundleItems()
    {
        return $this->hasMany(ItemBundleItem::class, 'item_edit_style_id');
    }
}
