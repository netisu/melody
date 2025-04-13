<?php

namespace App\Http\Middleware;

use App\Models\IpLog;
use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use App\Models\User;

class GiveDailyRewards
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // Check if the user agent contains known adblock keywords
       if (empty(Auth::user()->next_reward_payout)){
            $user = User::where(['id' => Auth::id()])->update([
                'next_reward_payout' => now(),
            ]);
       }

        if (Auth::check() && !empty(Auth::user()->next_reward_payout) && strtotime(Auth::user()->next_reward_payout) < time()) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();

            if ($user->lastIP() == null) {
                $ipLog = new IpLog;
                $ipLog->user_id = Auth::user()->id;
                $ipLog->ip = $request->ip();
                $ipLog->save();
            }


            $coinAmount = $user->settings->beta_tester ? 50 : 25;
            $buckAmount = $user->settings->beta_tester ? 10 : 5;
            $pointsAmount = $user->settings->beta_tester ? 100 : 50;

            $user->update([
                'coins' => $user->coins + $coinAmount,
                'bucks' => $user->bucks + $buckAmount,
                'next_reward_payout' => Carbon::now()->addHours(24)->toDateTimeString(),
            ]);
            $user->addPoints($pointsAmount);
        }
        return $next($request);
    }
}
