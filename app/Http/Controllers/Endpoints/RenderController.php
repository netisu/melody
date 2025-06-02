<?php

namespace App\Http\Controllers\Endpoints;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Avatar;
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RenderController extends Controller
{
    function getAvatarRecord($id): Avatar
    {
        return Avatar::where('user_id', $id)->firstOrFail();
    }

    public function UserRender($id): string
    {
        // Retrieve parameters for the request
        // Verify the encryption or any other required validations
        $avatar = $this->getAvatarRecord($id);
        Log::info('Retrieved avatar for userID: {id}', ['id' => $avatar->user_id]);

        $avatar_thumbnail_name = bin2hex(random_bytes(22));

        $requestData = $this->prepareRequestData('user', $avatar, $avatar_thumbnail_name);

        // Make HTTP request to the rendering server
        $this->makeRenderRequest($requestData);
        Log::info('Fowarded to external render service');

        // Update the user's image and save
        $avatar->image = $avatar_thumbnail_name;
        $avatar->save();
        Log::info('Thumbnail Saved To DB');

        // Return the rendered image as a response
        Log::info('Finished');
        return $this->getAvatarRenderHash($avatar->user_id);
    }

    public function ItemRender($id)
    {
        // Retrieve parameters for the request
        $item = Item::findOrFail($id);

        // Verify the encryption or any other required validations
        $requestData = $this->prepareRequestData('item', $item, $item->hash);
        // Make HTTP request to the rendering server
        $this->makeRenderRequest($requestData);

        // Return the rendered image as a response
        return $this->getItemThumb($item->id);
    }

    public function ItemPreviewRender($id, $noNameOverride = false, $itemName = null)
    {
        // Retrieve parameters for the request
        $item = Item::findOrFail($id);

        // Verify the encryption or any other required validations
        $itemHashName = bin2hex(random_bytes(22));
        if ($noNameOverride) {
            $requestData = $this->prepareRequestData('item_preview', $item, $itemName);
        } else {
            $requestData = $this->prepareRequestData('item_preview', $item, $itemHashName);
        }
        // Make HTTP request to the rendering server
        $this->makeRenderRequest($requestData);

        // Update the items image and save
        if ($noNameOverride) {
            $item->hash = $itemName;
            $itemPreview = "{$itemName}_preview";
        } else {
            $item->hash = $itemHashName;
            $itemPreview = "{$itemHashName}_preview";
        }

        $item->avatar_preview = $itemPreview;
        $item->save();

        // Return the rendered image as a response
        return $this->getItemThumb($item->id, true);
    }

    public function getAvatarRenderHash(int $id)
    {
        $avatar = $this->getAvatarRecord($id);

        return config('app.storage.url') . '/thumbnails/' . $avatar->image . '.png';
    }

    public function getItemThumb($id, bool $isPreview = false)
    {
        $item = Item::findOrFail($id);
        if ($isPreview) {
            return config('app.storage.url') . '/uploads/' . $item->hash . '_preview.png';
        } else {
            return config('app.storage.url') . '/uploads/' . $item->hash . '.png';
        }
    }

    private function getItemRenderData(?object $slotData): array
    {
        if (!$slotData || !$slotData->item) {
            return [
                'item' => 'none',
                'edit_style' => null,
                'is_model' => false,
                'is_texture' => false,
            ];
        }

        $itemHash = $slotData->item;
        $editStyleHash = null;
        $isModel = false;
        $isTexture = false;

        if ($slotData->edit_style_details) {
            $editStyleHash = $slotData->edit_style_details->hash; // Hash from ItemEditStyle
            $isModel = $slotData->edit_style_details->is_model_style;
            $isTexture = $slotData->edit_style_details->is_texture_style;
        }

        return [
            'item' => $itemHash,
            'edit_style' => $editStyleHash,
            'is_model' => $isModel,
            'is_texture' => $isTexture,
        ];
    }

    // --- Prepare Request Data Method ---
    private function prepareRequestData($type, $db, $hash)
    {
        $requestData = [
            'RenderType' => $type,
            'Hash' => $hash,
        ];

        if ($type == 'user') {
            $wearingItems = $db->getWearingItemsStructured(); // Get the structured data from Avatar model
            $itemsForRender = [];
            $itemSlots = [
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
            ];
            foreach ($itemSlots as $slot) {
                $itemsForRender[$slot] = $this->getItemRenderData($wearingItems[$slot]);
            }
            $requestData['RenderJson'] = [
                'items' => $itemsForRender,
                'colors' => [
                    'head_color' => $this->getColor($db->colors['head'], 'd3d3d3'),
                    'torso_color' => $this->getColor($db->colors['torso'], '055e96'),
                    'leftLeg_color' => $this->getColor($db->colors['left_leg'], 'd3d3d3'),
                    'rightLeg_color' => $this->getColor($db->colors['right_leg'], 'd3d3d3'),
                    'leftArm_color' => $this->getColor($db->colors['left_arm'], 'd3d3d3'),
                    'rightArm_color' => $this->getColor($db->colors['right_arm'], 'd3d3d3')
                ],
            ];
        } elseif ($type == 'item') {
            $requestData['RenderJson'] = [
                'ItemType' => $db->item_type,
                'Item' => getItemHash($db->id),
                'PathMod' => false,
            ];
        } elseif ($type == 'item_preview') {
            if ($db->item_type == 'hat') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => true,
                ];
            } elseif ($db->item_type == 'addon') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => true,
                ];
            } elseif ($db->item_type == 'face') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => true,
                ];
            } elseif ($db->item_type == 'tshirt') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => false,
                ];
            } elseif ($db->item_type == 'tool') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => true,
                ];
            } elseif ($db->item_type == 'shirt') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => false,
                ];
            } elseif ($db->item_type == 'pants') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => false,
                ];
            } elseif ($db->item_type == 'head') {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => false,
                ];
            } else {
                $requestData['RenderJson'] = [
                    'ItemType' => $db->item_type,
                    'Item' => getItemHash($db->id),
                    'PathMod' => true,
                ];
            }
        }

        return json_encode($requestData);
    }

    private function getColor($value, $default)
    {
        return isset($value) ? str_replace('#', '', $value) : $default;
    }

    private function makeRenderRequest($requestData)
    {
        $host = config('app.renderer.host');
        $port = config('app.renderer.port');

        if ($port) {
            $url = $host . ":" . $port;
        } else {
            $url =  $host;
        }

        return Http::withBody($requestData, 'application/json')->withOptions([
            'headers' => ['Aeo-Access-Key' => config('app.renderer.key')],
            'timeout' => 30,
        ])->post($url)->throw();
    }
}
