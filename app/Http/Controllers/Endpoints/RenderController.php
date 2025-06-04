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
        if (!$slotData || !isset($slotData->item)) {
            return [
                'item' => 'none',
                'edit_style' => null,
            ];
        }

        $itemHash = $slotData->item->hash;
        $editStyleData = null;

        if (isset($slotData->edit_style_details)) {
            $editStyleHash = $slotData->edit_style_details->hash; // Hash from ItemEditStyle
            $isModel = (bool) $slotData->edit_style_details->is_model_style;
            $isTexture = (bool) $slotData->edit_style_details->is_texture_style;

            // Construct the 'edit_style' object for the Go backend
            $editStyleData =  [
                'hash' => $editStyleHash,
                'is_model' => $isModel,
                'is_texture' => $isTexture,
            ];
        }

        return [
            'item' => $itemHash,
            'edit_style' => $editStyleData,
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
            $itemsForRender = [
                'face'   => $this->getItemRenderData(null), // This will be {"item": "none", "edit_style": null}
                'hats'   => array_fill(0, 6, $this->getItemRenderData(null)), // Initialize 6 hat slots as "none"
                'addon'  => $this->getItemRenderData(null),
                'tool'   => $this->getItemRenderData(null),
                'head'   => $this->getItemRenderData(null),
                'pants'  => $this->getItemRenderData(null),
                'shirt'  => $this->getItemRenderData(null),
                'tshirt' => $this->getItemRenderData(null),
            ];
            $wearingItems = $db->getWearingItemsStructured();
            if (isset($wearingItems['colors'])) {
                unset($wearingItems['colors']);
            }
            foreach ($wearingItems as $slotName => $slotDataObject) {
                if ($slotName === 'hats' && is_array($slotDataObject)) {
                    $itemsForRender['hats'] = [];
                    foreach ($slotDataObject as $index => $wornHat) {
                        if ($index < 6) {
                            $itemsForRender['hats'][$index] = $this->getItemRenderData($wornHat);
                        }
                    }
                    while (count($itemsForRender['hats']) < 6) {
                        $itemsForRender['hats'][] = $this->getItemRenderData(null);
                    }
                } elseif (isset($itemsForRender[$slotName]) && (is_object($slotDataObject) || (is_array($slotDataObject) && (isset($slotDataObject['item']) || isset($slotDataObject->item))))) {
                    $itemsForRender[$slotName] = $this->getItemRenderData((object)$slotDataObject);
                }
            }
            $requestData['RenderJson'] = [
                'items' => $itemsForRender,
                'colors' => [
                    'Head' => $this->getColor($db->colors['head'], 'd3d3d3'),
                    'Torso' => $this->getColor($db->colors['torso'], '055e96'),
                    'LeftLeg' => $this->getColor($db->colors['left_leg'], 'd3d3d3'),
                    'RightLeg' => $this->getColor($db->colors['right_leg'], 'd3d3d3'),
                    'LeftArm' => $this->getColor($db->colors['left_arm'], 'd3d3d3'),
                    'RightArm' => $this->getColor($db->colors['right_arm'], 'd3d3d3')
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
        Log::info('*** Entered makeRenderRequest ***'); // Keep this log!

        $host = config('app.renderer.host');
        $port = config('app.renderer.port');

        $url = $host;
        if ($port) {
            $url .= ":" . $port;
        }
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        Log::info('Making HTTP request to renderer URL: ' . $url, ['requestData' => $requestData]);

        return Http::withBody($requestData, 'application/json')->withOptions([
            'headers' => ['Aeo-Access-Key' => config('app.renderer.key')],
        ])->post($url)->throw();
    }
}
