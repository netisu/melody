<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPurchase extends Model
{
    use HasFactory;

    protected $table = 'item_purchases';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'item_id',
        'ip',
        'price'
    ];

    public function seller()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo('App\Models\User', 'buyer_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id');
    }

    public function sellerImage()
    {
        switch ($this->item->creator_type) {
            case 'user':
                return $this->seller->headshot();
            case 'group':
                return $this->item->creatorImage();
        }
    }

    public function sellerName()
    {
        switch ($this->item->creator_type) {
            case 'user':
                return $this->seller->username;
            case 'group':
                return $this->item->creatorName();
        }
    }
}