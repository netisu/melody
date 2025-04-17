<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceMembers extends Model
{
    protected $fillable = [
        'space_id',
        'user_id',
        'rank_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}