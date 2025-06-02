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
    
    public function banner(): mixed
    {
        $url = config('app.storage.url');
        if ($this->profile_banner_pending != true && $this->profile_banner_enabled != false) {
            $imageUrl = $this->profile_banner_url;

            // Check if profile_banner_url doesn't contain an external domain
            if (!preg_match(pattern: '/https?:\/\/[^\/]+/', subject: $imageUrl)) {
                return "{$url}/uploads/user-banners/{$imageUrl}.png";
            } else {
                // Return the external URL from settings if present
                return $imageUrl;
            }
        }
        return null;
    }

    public function callingCard(): mixed
    {
        $url = config('app.storage.url');
        if ($this->profile_picture_pending != true && $this->profile_picture_enabled != false) {
            $imageUrl = $this->profile_picture_url;

            // Check if profile_picture_url doesn't contain an external domain
            if (!preg_match(pattern: '/https?:\/\/[^\/]+/', subject: $imageUrl)) {
                return "{$url}/uploads/user-cards/{$imageUrl}.png";
            } else {
                // Return the external URL from settings if present
                return $imageUrl;
            }
        }
        return null;
    }
}
