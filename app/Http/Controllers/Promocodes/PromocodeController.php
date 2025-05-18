<?php

namespace App\Http\Controllers\Promocodes;

use App\Models\User;
use App\Models\Item;
use App\Models\Promocode;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPromocodes;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PromocodeController extends Controller
{
    public function PromocodeIndex(Request $request)
    {
        return inertia('Promocodes/Index', [
            'items' => Promocode::where('code', $request->code)->first(),
        ]);
    }
    public function PromocodeVal(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $promocode = Promocode::where('code', $request->code)->first();

        if (!$promocode || $promocode->expires_at <= now()) {
            return back()->with('error', 'Invalid or expired promocode.');
        }

        // Check if the user has already used this promocode
        $user = User::where('id', '=', Auth::id())->first();

        if ($user->promocodes()->where('promocode_id', $promocode->id)->exists()) {
            return back()->with('error', 'You have already redeemed this promocode.');
        }

        // redeem code

        if ($promocode->type == 'item') {
            $user = User::where(['id' => $user->id])->first();

            $inventory = new Inventory;

            $inventory->create([
                'user_id' => $user->id,
                'item_id' => $promocode->reward,
            ]);
        } elseif ($promocode->type == 'stars') {
            $newCurrencyValue = $user->stars + $promocode->reward;

            $user->update([
                'stars' => $newCurrencyValue
            ]);
        } elseif ($promocode->type == 'sparkles') {
            $newCurrencyValue = $user->sparkles + $promocode->reward;

            $user->update([
                'sparkles' => $newCurrencyValue
            ]);
        }

        // logging
        $PromocodeRecord = new UserPromocodes;
        $PromocodeRecord->create([
            "user_id" => $user->id,
            "promocode_id" => $promocode->id,
        ]);

        return back()->with('success', 'Promocode redeemed successfully!');
    }
}
