<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promocode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type', // 'item' or 'currency'
        'reward',  // If type is 'currency', the amount to give & If type is 'item', the ID of the item to give
        'expires_at',
    ];
}
