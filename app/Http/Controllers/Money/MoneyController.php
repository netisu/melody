<?php

namespace App\Http\Controllers\Money;

use App\Models\User;
use App\Models\Item;
use App\Models\Status;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ItemPurchase;
use Illuminate\Support\Facades\Auth;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class MoneyController extends Controller
{
    public function MoneyIndex()
    {
        $purchases = ItemPurchase::where('buyer_id', '=', Auth::id())->orderBy('created_at', 'DESC');

        return inertia('Money/Manage', [
            'purchases' =>  $purchases->paginate(10)->through(function ($purchase) {
                return [
                    'id' => $purchase->item->id,
                    'thumbnail' => $purchase->item->thumbnail(),
                    'name' => $purchase->item->name,
                    'currency_used' => $purchase->currency_used,
                    'DateHum' => $purchase->created_at->diffForHumans(),
                    'price' => $purchase->price,
                ];
            }),
        ]);
    }
}
