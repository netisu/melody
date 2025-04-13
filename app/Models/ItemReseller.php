<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReseller extends Model
{
    use HasFactory;

    protected $table = 'item_resellers';

    protected $fillable = [
        'seller_id',
        'item_id',
        'inventory_id',
        'price'
    ];

    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }

    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory', 'inventory_id');
    }
}