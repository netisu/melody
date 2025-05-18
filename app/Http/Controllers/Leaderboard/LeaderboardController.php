<?php

namespace App\Http\Controllers\Leaderboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules;
use LevelUp\Experience\Services\LeaderboardService;
use App\Models\User;

class LeaderboardController extends Controller
{
    public function LeaderboardIndex()
    {

        $leaderboardService = new LeaderboardService();
        $leaderboard = $leaderboardService->generate()->take(10);
        $starsLeaderboard = User::orderBy('stars', 'desc')->take(10)->get();
        $starsData = [];

        foreach ($starsLeaderboard as $user) {
            $starsData[] = [
                'username' => $user->username,
                'stars' => $user->stars,
            ];
        }
        return inertia('Leaderboard/Index', [
            'XP' => $leaderboard,
            'Stars' => $starsData,
        ]);
    }
}
