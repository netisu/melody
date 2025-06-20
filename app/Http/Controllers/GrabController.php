<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Jobs\UserRenderer;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Models\Avatar;

class GrabController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function customizeIndex()
    {
        $availableColors = config('avatar_colors');
        $categories = config('ItemCategories');

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $avatar = $user->avatar();
        if (!$avatar) {
            $avatar = Avatar::create([
                'user_id' => $user->id,
                'image' => 'default',
                'colors' => [
                    'Head' => 'd3d3d3',
                    'Torso' => '055e96',
                    'LeftArm' => 'd3d3d3',
                    'RightArm' => 'd3d3d3',
                    'LeftLeg' => 'd3d3d3',
                    'RightLeg' => 'd3d3d3',
                ],
            ]);
        }

        // Retrieve current body colors directly from the avatar model's JSON column
        $currentBodyColors = $avatar->colors ?? [
            'Head' => 'd3d3d3',
            'torso' => '055e96',
            'LeftArm' => 'd3d3d3',
            'RightArm' => 'd3d3d3',
            'LeftLeg' => 'd3d3d3',
            'RightLeg' => 'd3d3d3',
        ];

        // Derive current_face_url directly from the 'face' JSON column for the initial prop
        $faceItemHash = 'none';
        $faceSlotData = $avatar->face;

        if (is_array($faceSlotData) && isset($faceSlotData['item_id']) && !is_null($faceSlotData['item_id'])) {
            $faceItem = Item::find($faceSlotData['item_id']);
            if ($faceItem) {
                $faceItemHash = $faceItem->hash;
            }
        }

        $currentFaceUrl = config('app.storage.url') . (
            $faceItemHash !== 'none'
            ? '/uploads/' . $faceItemHash . ".png"
            : '/assets/default.png'
        );

        return inertia('Customize/Index', [
            'avatar' => [
                'thumbnail' => $user->thumbnail(),
                'colors' => $currentBodyColors,
                'current_face_url' => $currentFaceUrl,
            ],
            'available_colors' => $availableColors,
            'categories' => $categories,
        ]);
    }


    public function regeneratewithID($id)
    {
        UserRenderer::dispatch($id)->onQueue('user-render');
    }

    public function regenerate()
    {
        UserRenderer::dispatch(Auth::id())->onQueue('user-render');
    }

    public function UpdateAvatar(Request $request)
    {
        \Log::info('UpdateAvatar request received for user ID: ' . Auth::id() . ', action: ' . $request->action . ', request data: ' . json_encode($request->all()));
        /** @var \App\Models\User $user **/
        $avatar = Auth::user()->avatar();

        switch ($request->action) {
            case 'reset':
                $avatar->resetAvatar();
                $this->regenerate($request);
                return Auth::user()->thumbnail();
            case 'color':
                $validBodyParts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
                $bodyPart = $request->body_part;
                $newColor = $request->color;

                if (!in_array($bodyPart, $validBodyParts)) {
                    return response()->json(['error' => 'Invalid body part.'], 400); // 400 Bad Request
                }

                $currentColors = $avatar->colors ?? [];


                Log::info('Current color of ' . $bodyPart . ': ' . ($currentColors[$bodyPart] ?? 'N/A'));
                Log::info('Requested color: ' . $newColor);

                // Check if the avatar's current color for the specified body part matches the requested color
                if (($currentColors[$bodyPart] ?? null) === $newColor) {
                    Log::info('Color already matches, skipping regeneration for user ID: ' . Auth::id());
                    return Auth::user()->thumbnail();
                }
                Log::info('Saving Color for:' . $avatar->user_id);

                $currentColors[$bodyPart] = $newColor;

                $avatar->colors = $currentColors;

                // Update the avatar's color for the specified body part
                $avatar->save();

                Log::info('About to call $this->regenerate() for user ID: ' . $avatar->user_id);
                $this->regeneratewithID($avatar->user_id);
                \Log::info('$this->regenerate() called for user ID: ' . $avatar->user_id);

                return Auth::user()->thumbnail();


            default:
                return response()->json(['error' => 'Invalid action.']);
        }
    }
}
