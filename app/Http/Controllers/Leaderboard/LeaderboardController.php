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
    public function LeaderboardIndex(Request $request)
    {

        $leaderboardService = new LeaderboardService();
        $leaderboard = $leaderboardService->generate()->take(10);
        $bucksLeaderboard = User::orderBy('bucks', 'desc')->take(10)->get();
        $bucksData = [];

        foreach ($bucksLeaderboard as $user) {
            $bucksData[] = [
                'username' => $user->username,
                'bucks' => $user->bucks,
            ];
        }
        return inertia('Leaderboard/Index', [
            'XP' => $leaderboard,
            'Bucks' => $bucksData, // soon
        ]);
    }
}
