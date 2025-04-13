<?php

namespace App\Http\Controllers;

use App\Helpers\Event;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class ArgassetsController extends Controller
{
    protected $assetSteps = [
        'floralEaster' => 'moonlight.png',
        'chocolateBunny' => 'session.png',
        'starMaps' => 'fb9a1e2dd08e097729d097b2f3d558218b975d893b24.png',
        'thanksgivingCheer' => 'Untitled (12).png',
        'hiddenGems' => 'usePastLetters.png',
        'springRenewal' => true,
    ];
    
    protected $assetBaseUrl = 'assets/img/wmciws/';

    public function stepOne()
    {
        return $this->loadAsset('floralEaster');
    }

    public function stepTwo()
    {
        return $this->loadAsset('chocolateBunny');
    }

    public function stepThree()
    {
        return $this->loadAsset('starMaps');
    }

    public function stepFour()
    {
        return $this->loadAsset('thanksgivingCheer');
    }

    public function stepFive()
    {
        return $this->loadAsset('hiddenGems');
    }

    public function endStep()
    {
        $step = 'springRenewal';

        if (isset($this->assetSteps[$step]) && $this->assetSteps[$step] === true) {
            if (config('Values.in_event') == true && Auth::user()) {
                $eventItem = Item::where('id', 209)->first();
                if ($eventItem) {
                    $event = new Event;
                    $event->grantItem($eventItem, Auth::user(), 'enigmaticEgg', false);
                    return redirect()->to(route('store.item', 209));
                } else {
                    return response()->json(['error' => 'Event item not found.'], 404);
                }
            } else {
                // Optionally, a response if the conditions aren't met
                // make troll response later
                return response()->json(['message' => 'No item granted at this time.']);
            }
        } else {
            return response()->json(['error' => 'Invalid end step.'], 400);
        }
    }

    protected function loadAsset($step)
    {
        if (!isset($this->assetSteps[$step]) || $this->assetSteps[$step] === true) {
            return response()->json(['error' => 'Invalid step for asset loading.'], 400);
        }

        $filename = $this->assetSteps[$step];
        $fullAssetPath = $this->assetBaseUrl . $filename;

        $path = public_path($fullAssetPath);
        $mimeType = mime_content_type($path);

        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
            ]);
        } else {
            return response()->json(['error' => 'Asset file not found at: ' . $fullAssetPath], 404);
        }
    }

    public function getAssetSteps(): array
    {
        return $this->assetSteps;
    }

    public function index()
    {
        return view('argassets.index', ['steps' => array_keys($this->assetSteps)]);
    }
}
