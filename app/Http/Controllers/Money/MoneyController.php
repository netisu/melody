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
        $purchases = ItemPurchase::where(column: 'buyer_id', operator: '=', value: Auth::id())->orderBy(column: 'created_at', direction: 'DESC');

        return inertia('Money/Manage', [
            'purchases' =>  $purchases->paginate(10)->through(callback: function ($purchase): array {
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
    public function convert(Request $request): JsonResponse
    {
        $request->validate(rules: [
            'amount' => 'required|integer|min:0',
            'type' => 'required|in:Stars,Sparkles',
        ]);

        $amount = abs($request->input('amount')); // Use the absolute value for conversion
        $type = $request->input(key: 'type');

        if ($type === 'Stars') {
            // Converting Stars to Sparkles
            if (is_int($amount)) {
            // Bucks should ALWAYS be whole numbers
                if (Auth::user()->stars >= $amount) {
                    $sparklesToAdd = $amount * 10;
                    Auth::user()->stars -= $amount;
                    Auth::user()->sparkles += $sparklesToAdd;
                    $message = "Successfully converted {$amount} Stars to {$sparklesToAdd} Sparkles.";
                } else {
                    return response()->json(data: [
                        'type' => 'error',
                        'message' => 'Insufficient Bucks to perform this conversion.',
                    ], status: 400);
                }
            } else {
                return response()->json(data: [
                    'type' => 'error',
                    'message' => 'Buck amount must be an integer.',
                ], status: 400);
            }
        } elseif ($type === 'Sparkles') {
            // Converting Sparkles to Stars
            if ($amount % 10 == 0) {
                $starsToAdd = $amount / 10;
                if (Auth::user()->sparkles >= $amount) {
                    Auth::user()->sparkles -= $amount;
                    Auth::user()->stars += $starsToAdd;
                    $message = "Successfully converted {$amount} Sparkles to {$starsToAdd} Stars.";
                } else {
                    return response()->json(data: [
                        'type' => 'error',
                        'message' => 'Insufficient Coins to perform this conversion.',
                    ], status: 400);
                }
            } else {
                return response()->json(data: [
                    'type' => 'error',
                    'message' => 'Coin amount must be divisible by 10.',
                ], status: 400);
            }
        } else {
            return response()->json(data: ['type' => 'error', 'message' => 'Invalid conversion type'], status: 400);
        }

        Auth::user()->save();

        return response()->json(data: [
            'type' => 'success',
            'message' => $message ?? 'Balance updated successfully.',
        ], status: 200);
    }
}
