<?php

namespace App\Http\Controllers\Endpoints;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\SpaceRanks;
use App\Models\SpaceMembers;

class SpacesController extends Controller
{

    public function getSpaceMembersByRank(int $spaceId, int $rankId)
    {
        $space = Space::find($spaceId);
        $rank = SpaceRanks::where('space_id', $spaceId)->find($rankId);

        if (!$space) {
            return response()->json(['type' => 'danger', 'message' => 'Space not found.']);
        }
        if (!$rank) {
            return response()->json(['type' => 'danger', 'message' => 'Rank not found.']);
        }


        $memberQuery = SpaceMembers::where([
            ['space_id', $spaceId],
            ['rank', $rankId]
        ])
            ->with(['user'])
            ->paginate(10);

        $members = $memberQuery->map(function ($member) {
            return [
                'id' => $member->user->id,
                'username' => $member->user->username,
                'headshot' => $member->user->headshot(),
            ];
        })->toArray();

        return response()->json([
            'members' => $members,
            'pagination' => [
                'current_page' => $memberQuery->currentPage(),
                'last_page' => $memberQuery->lastPage(),
                'total' => $memberQuery->total(),
                'per_page' => $memberQuery->perPage(),
            ],
        ]);
    }
};
