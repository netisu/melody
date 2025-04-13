<?php

namespace App\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Avatar extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_avatars';

    protected $fillable = [
        'user_id',
        'image',
        'hat_1',
        'hat_2',
        'hat_3',
        'hat_4',
        'hat_5',
        'hat_6',
        'addon',
        'head',
        'face',
        'tool',
        'tshirt',
        'shirt',
        'pants',
        'color_head',
        'color_torso',
        'color_left_arm',
        'color_right_arm',
        'color_left_leg',
        'color_right_leg',

    ];

    public function WearingItems(): Array
    {
        $array = [
            'hat_1' => $this->hat(1),
            'hat_2' => $this->hat(2),
            'hat_3' => $this->hat(3),
            'hat_4' => $this->hat(4),
            'hat_5' => $this->hat(5),
            'hat_6' => $this->hat(6),
            'head' => $this->head(),
            'addon' => $this->addon(),
            'face' => $this->face(),
            'tool' => $this->tool(),
            'tshirt' => $this->tshirt(),
            'shirt' => $this->shirt(),
            'pants' => $this->pants(),
        ];

        return $array;
    }

    public function hat($num)
    {
        return Item::where('id', '=', $this->{"hat_{$num}"})->first();
    }

    public function face()
    {
        return Item::where('id', '=', $this->face)->first();
    }

    public function tool()
    {
        return Item::where('id', '=', $this->tool)->first();
    }

    public function tshirt()
    {
        return Item::where('id', '=', $this->tshirt)->first();
    }

    public function head()
    {
        return Item::where('id', '=', $this->head)->first();
    }

    public function addon()
    {
        return Item::where('id', '=', $this->addon)->first();
    }


    public function shirt()
    {
        return Item::where('id', '=', $this->shirt)->first();
    }

    public function pants()
    {
        return Item::where('id', '=', $this->pants)->first();
    }
    
    public function resetAvatar()
    {
        // Set the paths for thumbnail and headshot
        $thumbnail = "thumbnails/{$this->image}.png";
        $headshot = "thumbnails/{$this->image}_headshot.png";

        // Reset all attributes to their default values
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

        // Update the model with the default values and save it
        $this->timestamps = false;
        $this->fill($defaultAttributes);
        $this->save();
    }
}
