<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceRanks extends Model
{
    protected $fillable = [
        'space_id',
        'rank',
        'name',
        'description',
        'can_make_games',
        'can_view_wall',
        'can_kick_users',
    ];

    public function space()
    {
        return $this->belongsTo(Space::class, 'space_id');
    }
}
