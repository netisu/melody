<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'site_configuration';

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
                    return "{$url}/uploads/user-banners/{$imageUrl}.png";
                } else {
                    // Return the external URL from settings if present
                    return $imageUrl;
                }
            }
            return null;
    }
}
