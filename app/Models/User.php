<?php

namespace App\Models;

use Illuminate\{
    Database\Eloquent\Factories\HasFactory,
    Foundation\Auth\User as AeoAuthenticatable,
    Notifications\Notifiable,
    Database\Eloquent\Relations\HasMany,
    Database\Eloquent\Relations\HasOne,
    Contracts\Auth\MustVerifyEmail,
    Contracts\Auth\CanResetPassword,
    Support\Str,
};

use App\Models\{
    Avatar,
    Admin,
    Level,
    Status,
    IpLog,
    ForumThread,
    UserPromocodes,
    UsernameHistory,
};

use Laravel\{
    Cashier\Billable,
    Sanctum\HasApiTokens,
    Scout\Searchable,
};

use LevelUp\Experience\Concerns\{
    GiveExperience,
    HasAchievements,
};

class User extends AeoAuthenticatable implements MustVerifyEmail, CanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use
        Billable,
        HasApiTokens,
        HasFactory,
        GiveExperience,
        HasAchievements,
        Notifiable,
        Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'username',
        'display_name',
        'email',
        'password',
        'birthdate',
        'status',
        'about_me',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'coins',
        'bucks',
        'about_me',
        'latest_status',
        'social_id',
        'social_type',
        'wallet_address',
        'email',
        'password',
        'remember_token',
        'birthdate',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'latest_status',
        'created_at',
        'updated_at',
        'next_reward_payout',
        'event_currency',
        'email_verified_at',
        'stripe_id',


    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['DateHum'];
    protected $guard = ['web', 'admin'];

    public function setHiddenAttributes()
    {
        if ($this->isStaff()) {
            $this->hidden = ['password'];
        } else {
            $this->hidden = ['password', 'email', 'birthdate'];
        }
    }

    public function searchableAs(): string
    {
        return 'users_index';
    }
    /**
     * Meilisearch search array.
     *
     * @var array<string, string>
     */
    public function toSearchableArray(): array
    {
        // All model attributes are made searchable
        $array = $this->toArray();

        // Then we add some additional fields
        $array['id'] = (int) $this->id;
        $array['username'] = $this->username;
        $array['display_name'] = $this->display_name;
        $array['status'] = $this->status;
        $array['headshot'] = $this->headshot();
        $array['thumbnail'] = $this->thumbnail();

        return $array;
    }

    // Classes
    public function avatar()
    {
        return $this->hasOne(Avatar::class)->first() ?? $this->createDefaultAvatar();
    }

    public function createDefaultAvatar()
    {
        $avatar = Avatar::create([
            'user_id' => $this->id,
        ]);

        $defaultAttributes = [
            'image' => 'default',
            'hat_1' => null,
            'hat_2' => null,
            'hat_3' => null,
            'hat_4' => null,
            'hat_5' => null,
            'hat_6' => null,
            'addon' => null,
            'head' => null,
            'face' => null,
            'tool' => null,
            'tshirt' => null,
            'shirt' => null,
            'pants' => null,
            'color_head' => 'd3d3d3',
            'color_torso' => '055e96',
            'color_left_arm' => 'd3d3d3',
            'color_right_arm' => 'd3d3d3',
            'color_left_leg' => 'd3d3d3',
            'color_right_leg' => 'd3d3d3',
        ];
        $avatar->timestamps = false;
        $avatar->fill($defaultAttributes);
        $avatar->save();
    }

    public function promocodes(): HasOne
    {
        return $this->hasOne(UserPromocodes::class, 'user_id');
    }

    public function getNextLevelExp()
    {
        $currentLevel = min($this->getLevel() + 1, 100);
        $nextLevel = Level::where('level', $currentLevel)->first();
        return $nextLevel->next_level_experience;
    }

    public function getDateHumAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    // Define the followers relationship
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id');
    }

    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Checks if the user has an active ban.
     *
     * @return bool True if an active ban is found, false otherwise.
     */
    public function hasActiveBan(): bool
    {
        // Leverage the relationship to efficiently check for active bans
        return $this->bans()->where('active', 1)->exists();
    }

    public function bans(): HasMany
    {
        return $this->hasMany(UserBan::class, 'user_id', 'id');
    }

    public function isFollowedBy(User $userToCheck)
    {
        return $this->followers()->where('follower_id', $userToCheck->id)->exists();
    }

    public function isFriend(User $user)
    {
        return $this->isFollowing($user) && $user->isFollowing($user); // Check mutual following
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'creator_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sending_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiving_id');
    }
    public function TotalSales()
    {
        return ItemPurchase::where('seller_id', '=', $this->id)->count();
    }

    public function ownsItem(int $id): bool
    {
        return Inventory::where([
            ['user_id', '=', $this->id],
            ['item_id', '=', $id]
        ])->exists();
    }

    public function ipLogs(): HasMany
    {
        return $this->hasMany(IpLog::class, 'user_id');
    }

    public function lastIP(): string|null
    {
        $lastLog = $this->ipLogs()->latest()->first();

        return $lastLog ? $lastLog->ip : null;
    }

    public function resellableCopiesOfItem(int $id)
    {
        $i = 1;
        $resellableCopies = [];
        $copies = Inventory::where([
            ['user_id', '=', $this->id],
            ['item_id', '=', $id]
        ])->get();

        foreach ($copies as $copy) {
            $isReselling = ItemReseller::where('inventory_id', '=', $copy->id)->exists();

            if (!$isReselling) {
                $copy->number = $i;
                $resellableCopies[] = $copy;
                $i++;
            }
        }

        return $resellableCopies;
    }

    public function spaces()
    {
        return $this->hasManyThrough(
            Space::class,
            SpaceMembers::class,
            'user_id', // Foreign key on the SpaceMember table
            'id', // Foreign key on the Space table
            'id', // Local key on the User table
            'space_id' // Local key on the SpaceMember table
        );
    }

    public function isInSpace(Space $space)
    {
        return $this->spaces->contains($space);
    }

    public function mainSpaces()
    {
        $primarySpace = $this->settings->primarySpace()->first();
        $secondarySpace = $this->settings->secondarySpace()->first();

        $spaces = collect();

        if ($primarySpace) {
            $spaces->push([
                'id' => $primarySpace->id,
                'name' => $primarySpace->name,
                'slug' => $primarySpace->slug(),
                'thumbnail' => $primarySpace->thumbnail(),
            ]);
        }

        if ($secondarySpace && $secondarySpace->id !== ($primarySpace ? $primarySpace->id : null)) {
            $spaces->push([
                'id' => $secondarySpace->id,
                'name' => $secondarySpace->name,
                'slug' => $secondarySpace->slug(),
                'thumbnail' => $secondarySpace->thumbnail(),
            ]);
        }

        if ($spaces->isEmpty()) {
            return null;
        }

        return $spaces;
    }

    public function navSpaces()
    {
        $primarySpace = $this->settings->primarySpace()->first();
        $secondarySpace = $this->settings->secondarySpace()->first();

        $spaceAmount = $secondarySpace ? 4 : 3;
        $spaces = $this->spaces()
            ->orderBy('created_at', 'asc') // Order by created_at in descending order (newest first)
            ->take($spaceAmount)
            ->get();

        return $spaces->map(function ($space) {
            return [
                'id' => $space->id,
                'name' => $space->name,
                'slug' => $space->slug(),
                'thumbnail' => $space->thumbnail(),
            ];
        });
    }

    public function canEditItem(int $id): bool
    {
        $item = Item::findOrFail($id);

        return ($item->creator_type === 'user' && $this->id === $item->creator->id) || ($item->creator_type === 'group' && $this->id === $item->creator->owner_id);
    }


    public function online(): int
    {
        return $this->updated_at->diffInSeconds() < 300;
    }

    public function settings()
    {
        return $this->hasOne(UserSettings::class);
    }

    public function isStaff()
    {
        return Admin::where([
            ['user_id', '=', $this->id],
        ])->exists();
    }

    public function CurrentPosition()
    {
        if ($this->isStaff()) {
            $admin = Admin::where([
                ['user_id', '=', $this->id],
            ])->first();

            $adminRole = AdminRoles::where([
                ['id', '=', $admin->role_id],
            ])->first();

            return $adminRole->name;
        } else {
            return null;
        }
    }

    public function CurrentPositionID()
    {
        if ($this->isStaff()) {
            $admin = Admin::where([
                ['user_id', '=', $this->id],
            ])->first();
            return $admin->role_id;
        } else {
            return null;
        }
    }

    public function thumbnail()
    {
        if (env('APP_ENV') != 'local') {

            $url = env('STORAGE_URL');
            $image = ($this->avatar()?->image === 'default') ? env('DEFAULT_AVATAR_FILE') : $this->avatar()?->image;
            if ($this->avatar()?->image === 'default') {
                return "{$image}.png";
            } else {
                return "{$url}/thumbnails/{$image}.png";
            }
        } else {
            return config('Values.icon');
        }
    }
    public function usernameHistory()
    {
        return UsernameHistory::where('user_id', $this->id)->orderBy('created_at')->get();
    }

    public function headshot()
    {
        if (env('APP_ENV') != 'local') {
            $url = env('STORAGE_URL');
            if ($this->settings && $this->settings->profile_picture_enabled != false) {
                $imageUrl = $this->settings->profile_picture_url;

                // Check if profile_picture_url doesn't contain an external domain
                if (!preg_match('/https?:\/\/[^\/]+/', $imageUrl)) {
                    return "{$url}/thumbnails/profile-pictures/{$imageUrl}.png";
                } else {
                    // Return the external URL from settings if present
                    return $imageUrl;
                }
            } else {
                $image = ($this->avatar()?->image === 'default') ? env('DEFAULT_AVATAR_FILE') : $this->avatar()?->image;
                if ($this->avatar()?->image === 'default') {
                    return "{$image}_headshot.png";
                } else {
                    return "{$url}/thumbnails/{$image}_headshot.png";
                }
            }
        } else {
            return config('Values.icon');
        }
    }

    public function pastUsernames(): HasMany
    {
        return $this->hasMany(UsernameHistory::class, 'user_id', 'id');
    }

    public function latestStatus(): HasOne
    {
        return $this->hasOne(Status::class, 'creator_id')->latest();
    }

    public function getStatusAttribute()
    {
        return $this->latestStatus?->message;
    }
    public function definition()
    {
        return [
            'username' => $this->faker->name(),
            'display_name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'status' => 'Mock Account',
            'about_me' => 'Mock Account',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }
}
