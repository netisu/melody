<?php

namespace App\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrateItem extends Model
{
    use HasFactory;

    protected $table = 'crate_items';

    protected $fillable = [
        'item_id',
        'crate_id',
        'rarity'
    ];

    public const RARITY_WEIGHTS = [
        6 => 0.4,
        5 => 1.5,
        4 => 4,
        3 => 9,
        2 => 18,
        1 => 56,
    ];

    public const RARITY_RANK = [
        6 => 'Elite',
        5 => 'Five Star',
        4 => 'Four Star',
        3 => 'Three Star',
        2 => 'Two Star',
        1 => 'One Star'
    ];

    public const RARITY_COLORS = [
        6 => '#000000',
        5 => '#ff1414',
        4 => '#a514ff',
        3 => '#41ab17',
        2 => '#388bff',
        1 => '#ff00c8'
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id');
    }

    public static function rarityName($rarity)
    {
        return self::RARITY_RANK[$rarity] ?? 'Unknown';
    }

    public static function rarityRank($rarity)
    {
        return self::RARITY_COLORS[$rarity] ?? 'inherit';
    }

    public static function getWinningItem($id)
    {
        $items = self::where('crate_id', '=', $id)->get();
        $valuesAndWeights = [];
        $x = 0;

        foreach ($items as $item)
            $valuesAndWeights[$item->id] = self::RARITY_WEIGHTS[$item->rarity];

        $pick = mt_rand(1, array_sum($valuesAndWeights));

        foreach ($valuesAndWeights as $value => $weight) {
            if (($x += $weight) > $pick)
                return $value;
        }
    }
}
