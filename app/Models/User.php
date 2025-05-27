<?php

namespace App\Models;

use Illuminate\{
    Database\Eloquent\Factories\HasFactory,
    Foundation\Auth\User as AeoAuthenticatable,
    Notifications\Notifiable,
    Support\Collection,
    Database\Eloquent\Relations\HasMany,
    Database\Eloquent\Relations\HasManyThrough,
    Database\Eloquent\Relations\BelongsToMany,
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
        'sparkles',
        'stars',
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

    public function setHiddenAttributes(): void
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
    public function avatar(): Avatar
    {
        return $this->hasOne(related: Avatar::class)->first() ?? $this->createDefaultAvatar();
    }

    public function createDefaultAvatar(): Avatar
    {
        $avatar = Avatar::create(attributes: [
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
        $avatar->fill(attributes: $defaultAttributes);
        $avatar->save();

        return $avatar;
    }

    public function promocodes(): HasOne
    {
        return $this->hasOne(related: UserPromocodes::class, foreignKey: 'user_id');
    }

    public function getNextLevelExp()
    {
        $currentLevel = min( $this->getLevel() + 1,  100);
        $nextLevel = Level::where(column: 'level', operator: $currentLevel)->first();
        return $nextLevel->next_level_experience;
    }

    public function getDateHumAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    // Define the followers relationship
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'followers', foreignPivotKey: 'following_id', relatedPivotKey: 'follower_id');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'followers', foreignPivotKey: 'follower_id', relatedPivotKey: 'following_id');
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where(column: 'following_id', operator: $user->id)->exists();
    }

    /**
     * Checks if the user has an active ban.
     *
     * @return bool True if an active ban is found, false otherwise.
     */
    public function hasActiveBan(): bool
    {
        // Leverage the relationship to efficiently check for active bans
        return $this->bans()->where(column: 'active', operator: 1)->exists();
    }

    public function bans(): HasMany
    {
        return $this->hasMany(related: UserBan::class, foreignKey: 'user_id', localKey: 'id');
    }

    public function isFollowedBy(User $userToCheck): bool
    {
        return $this->followers()->where(column: 'follower_id', operator: $userToCheck->id)->exists();
    }

    public function isFriend(User $user): bool
    {
        return $this->isFollowing(user: $user) && $user->isFollowing(user: $user); // Check mutual following
    }

    public function posts(): HasMany
    {
        return $this->hasMany(related: ForumThread::class, foreignKey: 'creator_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(related: Message::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(related: Message::class, foreignKey: 'sending_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(related: Message::class, foreignKey: 'receiving_id');
    }
    public function TotalSales(): int
    {
        return ItemPurchase::where(column: 'seller_id', operator: '=', value: $this->id)->count();
    }

    public function ownsItem(int $id): bool
    {
        return Inventory::where(column: [
            ['user_id', '=', $this->id],
            ['item_id', '=', $id]
        ])->exists();
    }

    public function ipLogs(): HasMany
    {
        return $this->hasMany(related: IpLog::class, foreignKey: 'user_id');
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

    public function spaces(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: Space::class,
            through: SpaceMembers::class,
            firstKey: 'user_id', // Foreign key on the SpaceMember table
            secondKey: 'id', // Foreign key on the Space table
            localKey: 'id', // Local key on the User table
            secondLocalKey: 'space_id' // Local key on the SpaceMember table
        );
    }

    public function isInSpace(Space $space): mixed
    {
        return $this->spaces->contains($space);
    }

    public function mainSpaces(): Collection|null
    {
        $primarySpace = $this->settings->primarySpace()->first();
        $secondarySpace = $this->settings->secondarySpace()->first();

        $spaces = collect();

        if ($primarySpace) {
            $spaces->push(values: [
                'id' => $primarySpace->id,
                'name' => $primarySpace->name,
                'slug' => $primarySpace->slug(),
                'thumbnail' => $primarySpace->thumbnail(),
            ]);
        }

        if ($secondarySpace && $secondarySpace->id !== ($primarySpace ? $primarySpace->id : null)) {
            $spaces->push(values: [
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

    public function navSpaces(): Collection
    {
        $primarySpace = $this->settings->primarySpace()->first();
        $secondarySpace = $this->settings->secondarySpace()->first();

        $spaceAmount = $secondarySpace ? 4 : 3;
        $spaces = $this->spaces()
            ->orderBy(column: 'created_at', direction: 'asc') // Order by created_at in descending order (newest first)
            ->take(value: $spaceAmount)
            ->get();

        return $spaces->map(callback: function ( $space) {
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
        $item = Item::findOrFail(id: $id);

        return ($item->creator_type === 'user' && $this->id === $item->creator->id) || ($item->creator_type === 'group' && $this->id === $item->creator->owner_id);
    }


    public function online(): int
    {
        return $this->updated_at->diffInSeconds() < 300;
    }

    public function settings(): HasOne
    {
        return $this->hasOne(related: UserSettings::class);
    }

    public function isStaff(): bool
    {
        return Admin::where(column: [
            ['user_id', '=', $this->id],
        ])->exists();
    }

    public function CurrentPosition(): mixed
    {
        if ($this->isStaff()) {
            $admin = Admin::where(column: [
                ['user_id', '=', $this->id],
            ])->first();

            $adminRole = AdminRoles::where(column: [
                ['id', '=', $admin->role_id],
            ])->first();

            return $adminRole->name;
        } else {
            return null;
        }
    }

    public function CurrentPositionID(): mixed
    {
        if ($this->isStaff()) {
            $admin = Admin::where(column: [
                ['user_id', '=', $this->id],
            ])->first();
            return $admin->role_id;
        } else {
            return null;
        }
    }

    public function thumbnail(): string
    {
        if (config('app.env') != 'local') {

            $url = config('app.storage.url');
            $image = ($this->avatar()?->image === 'default') ? config('app.default_avatar_file') : $this->avatar()?->image;
            if ($this->avatar()?->image === 'default') {
                return "{$image}.png";
            } else {
                return "{$url}/thumbnails/{$image}.png";
            }
        } else {
            return config(key: 'Values.icon');
        }
    }
    public function usernameHistory(): Collection
    {
        return UsernameHistory::where(column: 'user_id', operator: $this->id)->orderBy(column: 'created_at')->get();
    }

    public function headshot(): mixed
    {
        if (config('app.env') != 'local') {
            $url = config('app.storage.url');
            if ($this->settings && $this->settings->profile_picture_pending != false && $this->settings->profile_picture_enabled != false) {
                $imageUrl = $this->settings->profile_picture_url;

                // Check if profile_picture_url doesn't contain an external domain
                if (!preg_match(pattern: '/https?:\/\/[^\/]+/', subject: $imageUrl)) {
                    return "{$url}/thumbnails/profile-pictures/{$imageUrl}.png";
                } else {
                    // Return the external URL from settings if present
                    return $imageUrl;
                }
            } else {
                $image = ($this->avatar()?->image === 'default') ? config('app.default_avatar_file') : $this->avatar()?->image;
                if ($this->avatar()?->image === 'default') {
                    return "{$image}_headshot.png";
                } else {
                    return "{$url}/thumbnails/{$image}_headshot.png";
                }
            }
        } else {
            return config(key: 'Values.icon');
        }
    }

    public function pastUsernames(): HasMany
    {
        return $this->hasMany(related: UsernameHistory::class, foreignKey: 'user_id', localKey: 'id');
    }

    public function latestStatus(): HasOne
    {
        return $this->hasOne(related: Status::class, foreignKey: 'creator_id')->latest();
    }

    public function getStatusAttribute(): mixed
    {
        return $this->latestStatus?->message;
    }

    public function definition(): array
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
