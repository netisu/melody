<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'user_id',
        'item_id'
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id');
    }
}