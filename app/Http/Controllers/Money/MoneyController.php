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
            'type' => 'required|in:bucks,coins',
        ]);

        $amount = abs($request->input('amount')); // Use the absolute value for conversion
        $type = $request->input(key: 'type');

        if ($type === 'bucks') {
            // Converting Bucks to Coins
            if ($amount % 10 == 0) {
            // Bucks should ALWAYS be whole numbers
                if (Auth::user()->bucks >= $amount) {
                    $coinsToAdd = $amount * 10;
                    Auth::user()->bucks -= $amount;
                    Auth::user()->coins += $coinsToAdd;
                    $message = "Successfully converted {$amount} Bucks to {$coinsToAdd} Coins.";
                } else {
                    return response()->json(data: [
                        'type' => 'error',
                        'message' => 'Insufficient Bucks to perform this conversion.',
                    ], status: 400);
                }
            } else {
                return response()->json(data: [
                    'type' => 'error',
                    'message' => 'Buck amount must must be a multiple of 10.',
                ], status: 400);
            }
        } elseif ($type === 'coins') {
            // Converting Coins to Bucks
            if ($amount % 10 == 0) {
                $bucksToAdd = $amount / 10;
                if (Auth::user()->coins >= $amount) {
                    Auth::user()->coins -= $amount;
                    Auth::user()->bucks += $bucksToAdd;
                    $message = "Successfully converted {$amount} Coins to {$bucksToAdd} Bucks.";
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
