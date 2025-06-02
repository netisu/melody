<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Item;

class Achievements extends Model
{
    use HasFactory;

    protected $table = 'achievements'; // Specify the actual table name
     public function products(): MorphMany
    {
        return $this->morphMany(Item::class, 'purchasable');
    }
}
