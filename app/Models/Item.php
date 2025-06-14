<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ItemEditStyle;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Item extends Model
{
    use HasFactory, Searchable;
    protected $table = 'items';

    protected $fillable = [
        'creator_id',
        'creator_type',
        'name',
        'description',
        'hash',
        'item_type',
        'status',
        'public_view',
        'cost_sparkles',
        'cost_stars',
        'sale_ongoing',
        'percent_off',
        'avatar_preview',
        'thumbnail_hash',
        'rare',
        'in_event',
        'event_id',
        'in_style',
        'style_of',
        'onsale',
        'initial_stock',
        'remaining_stock',
        'time_off_sale',
        'time_on_sale',
        'impression_count',
        'trade_lock',
        'limit_copies',
    ];

    protected $appends = ['DateHum', 'UpdateHum'];

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
        $array['id'] = $this->getKey();
        $array['name'] = $this->name;
        $array['description'] = $this->description;
        $array['thumbnail'] = $this->thumbnail();
        $array['owners'] = $this->owners();
        $array['creator'] = $this->creator();

        return $array;
    }

    public function getDateHumAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getUpdateHumAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function onsale()
    {
        return $this->onsale ? true : false;
    }

    public function owners()
    {
        return Inventory::where([
            ['ownable_id', '=', $this->id],
            ['ownable_type', '=', Item::class],
        ]);
    }

    public function thumbnail()
    {
        $url = config('app.storage.url');
        return "{$url}/thumbnails/{$this->item_type}/{$this->hash}.png";
    }

    public function preview()
    {
        $url = config('app.storage.url');
        $PreviewTypes = config('Values.shop.preview_types');

        if (in_array($this->item_type, $PreviewTypes ?? [])) {
            return "{$url}/thumbnails/{$this->avatar_preview}.png";
        } else {
            return null;
        }
    }

    public function inventories(): MorphMany
    {
        return $this->morphMany(Inventory::class, 'ownable');
    }

    public function slug(): string
    {
        $text = $this->name;

        // Convert to lowercase and replace spaces with a separator
        $text = strtolower(trim(preg_replace('/\s+/', '-', $text)));

        $allowedChars = 'a-z0-9-'; // Adjust this to include desired characters
        $text = preg_replace('/[^' . $allowedChars . ']/', '', $text);
        $text = preg_replace('/-{2,}/', '-', $text);

        // Handle trailing separators
        $text = trim($text, '-');

        return $text;
    }
    public function editStyles(): HasMany
    {
        return $this->hasMany(ItemEditStyle::class);
    }
}
