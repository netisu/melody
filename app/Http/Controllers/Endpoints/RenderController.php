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
    function getAvatarRecord($id) : Avatar
    {
        return Avatar::where('user_id', $id)->first();
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

    private function prepareRequestData($type, $db, $hash)
    {
        $requestData = [
            'RenderType' => $type,
            'Hash' => $hash,
        ];

        if ($type == 'user') {
            $requestData['RenderJson'] = [
                'items' => [
                    'face' => ($db->face) ? getItemHash($db->face) : 'none',
                    'hats' => [
                        ($db->hat_1) ? getItemHash($db->hat_1) : 'none',
                        ($db->hat_2) ? getItemHash($db->hat_2) : 'none',
                        ($db->hat_3) ? getItemHash($db->hat_3) : 'none',
                        ($db->hat_4) ? getItemHash($db->hat_4) : 'none',
                        ($db->hat_5) ? getItemHash($db->hat_5) : 'none',
                    ],
                    'addon' => ($db->addon) ? getItemHash($db->addon) : 'none',
                    'tool' => ($db->tool) ? getItemHash($db->tool) : 'none',
                    'head' => ($db->head) ? getItemHash($db->head) : 'none',
                    'shirt' => ($db->shirt) ? getItemHash($db->shirt) : 'none',
                    'pants' => ($db->pants) ? getItemHash($db->pants) : 'none',
                    'tshirt' => ($db->tshirt) ? getItemHash($db->tshirt) : 'none',
                ],
                'colors' => [
                    'head_color' => $this->getColor($db->color_head, 'ffffff'),
                    'torso_color' => $this->getColor($db->color_torso, '055e96'),
                    'leftLeg_color' => $this->getColor($db->color_left_leg, 'ffffff'),
                    'rightLeg_color' => $this->getColor($db->color_right_leg, 'ffffff'),
                    'leftArm_color' => $this->getColor($db->color_left_arm, 'ffffff'),
                    'rightArm_color' => $this->getColor($db->color_right_arm, 'ffffff')
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
            }
            else {
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
        $host = config('app.render.host');
        $port = config('app.render.port');

        if ($port) {
            $url = $host . ":" . $port;
        } else {
            $url =  $host;
        }

        return Http::withBody($requestData, 'application/json')->withOptions([
            'headers' => ['Aeo-Access-Key' => config('app.renderer.key')]
        ])->post($url);
    }
}
