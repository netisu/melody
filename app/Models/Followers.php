<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    protected $table = 'followers';
    protected $fillable = [
        'follower_id',
        'user_id',
        'status',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
