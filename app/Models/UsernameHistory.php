<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UsernameHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip',
        'username'
    ];

    /**
     * Get the user associated with the username history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}