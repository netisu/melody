<?php

namespace App\Models;

use App\Models\User;

use Illuminate\{
    Database\Eloquent\Factories\HasFactory,
    Database\Eloquent\Relations\HasOne,
    Database\Eloquent\Model,
    Database\Eloquent\Relations\BelongsTo,
};

class UserSettings extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_settings';


    protected $fillable = [
        'user_id',
        'primary_space', // Add primary_space to fillable
        'secondary_space', // Add secondary_space to fillable
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primarySpace(): HasOne
    {
        return $this->hasOne(Space::class, 'id', 'primary_space');
    }

    public function secondarySpace(): HasOne
    {
        return $this->hasOne(Space::class, 'id', 'secondary_space');
    }
}
