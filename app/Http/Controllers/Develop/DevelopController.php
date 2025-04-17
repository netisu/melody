<?php

namespace App\Http\Controllers\Develop;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\CrateItem;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\ItemReseller;
use App\Models\ItemPurchase;
use Illuminate\Http\JsonResponse;
use Stevebauman\Location\Facades\Location;

class DevelopController extends Controller
{

    public function DevelopIndex(Request $request, $category)
    {
        return inertia("Develop/Panel" []);
    }

}
