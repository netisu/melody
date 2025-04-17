<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementChecker
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
        if (Auth::check()) {
            $user = Auth::user();
            $itemCount = Inventory::where('user_id', '=', $user->id)->count();

            // Administrator
            if ($user->isStaff() && !$user->AlreadyAchieved(1))
                $user->grantAchievement(1);
                
            else if (!$user->isStaff() && $user->AlreadyAchieved(1))
                $user->removeAchievement(1);

            // Level 20 Reward
            if ($user->getLevel() >= 20 && !$user->AlreadyAchieved(2))
                $user->grantAchievement(2);
            else if ($user->Level < 7 && $user->AlreadyAchieved(2))
                $user->removeAchievement(2);

            // Friendship
            if ($user->friends()->count() >= 20 && !$user->AlreadyAchieved(3))
                $user->grantAchievement(2);

            // Rich
            if ($user->coins >= 500 && !$user->AlreadyAchieved(5))
                $user->grantAchievement(5);

            // Stockpiler
            if ($itemCount >= 50 && !$user->AlreadyAchieved(6))
                $user->grantAchievement(6);

            // Membership
            if ($user->hasMembership() && !$user->AlreadyAchieved(9))
                $user->grantAchievement(9);
            else if (!$user->hasMembership() && $user->AlreadyAchieved(9))
                $user->removeAchievement(9);
        }

        return $next($request);
    }
}
