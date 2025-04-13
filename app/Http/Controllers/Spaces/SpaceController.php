<?php

namespace App\Http\Controllers\Spaces;

use App\Models\User;
use App\Models\Space;

use App\Models\Status;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SpacePurchase;
use Illuminate\Support\Facades\Auth;
use App\Models\Level;
use App\Models\SpaceMembers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use App\Models\SpaceRanks;

class SpaceController extends Controller
{
    public function SpacesIndex(Request $request)
    {
        $search = $request->input('search', '');

        $spaces = Space::where([

            ['thumbnail_pending', '!=', false],
            ['name', 'like', "%$search%"]

        ])->orderBy('created_at', 'desc')->paginate(12)->through(function ($space) {
            $memberQuery = SpaceMembers::where([['space_id', $space->id], ['user_id', Auth::id()]])
                ->with('user')
                ->paginate(10);

            $member_count = count($memberQuery);
            return [
                'name' => $space->name,
                'id' => $space->id,
                'slug' => $space->slug(),
                'name' => $space->name,
                'creator' => $space->creator->display_name,
                'timecreated' => $space->created_at,
                'description' => $space->description,
                'DateHum' => $space->DateHum,
                'member_count' => $member_count,
                'thumbnail' => $space->thumbnail(),
            ];
        });



        return inertia('Spaces/Index', [
            'spaces' => $spaces,
            'search' => $search,
        ]);
    }

    public function SpaceCreate()
    {
        return inertia('Spaces/Create', []);
    }

    public function SpacesView(Request $request, int $id, string $slug)
    {

        $space = Space::where([['id', '=', $id]])->first();

        if (!$space) {
            abort(404);
        }
        $memberQuery = SpaceMembers::where([['space_id', $id], ['user_id', Auth::id()]])
            ->with('user')
            ->paginate(10);
        $members = $memberQuery->map(function ($member) {
            return [
                'id' => $member->user->id, // Direct access, efficient
                'username' => $member->user->username,
                'headshot' => $member->user->headshot(),
            ];
        })->toArray();

        if ($slug != $space->slug()) {
            echo ("Did you mean: " . $space->slug());
            abort(404);
        }

        return inertia('Spaces/View', [
            'isInSpace' =>  Auth::check() ? Auth::user()->isInSpace($space) : null,
            'space' => [
                'id' => $space->id,
                'name' => $space->name,
                'slug' => $space->slug(),
                'name' => $space->name,
                'members' => [
                    'list' => $members,
                    'count' => count($members),
                ],
                'creator' => [
                    'id' => $space->creator->id,
                    'username' => $space->creator->username,
                    'display_name' => $space->creator->display_name
                ],
                'timecreated' => $space->created_at,
                'description' => $space->description,
                'DateHum' => $space->DateHum,
                'member_count' => $space->member_count,
                'thumbnail' => $space->thumbnail(),
            ],
        ]);
    }

    public function SpaceMembership(Request $request)
    {
        $space = Space::findOrFail($request->id);

        if (Auth::user()->is($space->owner)) {
            abort(404);
        }

        $isInSpace = SpaceMembers::where('space_id', $space->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isInSpace) {
            // User is not in space, so join them
            $memberRank = SpaceRanks::where('space_id', $space->id)
                ->where('rank', 1)
                ->first();

            if ($memberRank) {
                SpaceMembers::create([
                    'space_id' => $space->id,
                    'user_id' => Auth::id(),
                    'rank' => $memberRank->id,
                ]);

                $message = 'You have joined this space.';
            } else {
                // Handle the case where the member rank is not found
                $message = 'Error: Member rank not found for this space. Pleaselet the creator know about this.';
                return back()->with('error', $message);
            }
        } else {
            // User is already in the space, so make them leave
            if (Auth::user()->primary_space_id === $space->id) {
                Auth::user()->update(['primary_space_id' => null]);
            }

            SpaceMembers::where('space_id', $space->id)
                ->where('user_id', Auth::id())
                ->delete();

            $message = 'You have left this space.';
        }

        return back()->with('success', $message);
    }

    public function SpaceVal(Request $request)
    {
        $this->validateRequest($request);
        $ThumbnailHashName = bin2hex(random_bytes(22));

        $info = getimagesize($request->file('image'));
        if ($info[2] == IMAGETYPE_JPEG) {
            return response()->json([
                'message' => 'This file needs to be a true PNG, none of that sneaky jpeg-in-disguise business.',
                'type' => 'danger'
            ], 502);
        }

        $this->uploadImage($request->file('image'), $ThumbnailHashName);

        $Space = Space::create([
            'owner_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'verified' => false,
            'private' => false,
            'alert' => false,
            'alert_message' => null,
            'vault_viewable' => true,
            'primary_color' => 'none',
            'locked' => false,
            'thumbnail' => $ThumbnailHashName,
            'thumbnail_pending' => true,
        ]);

        $ownerRank = SpaceRanks::create([
            'space_id' => $Space->id,
            'rank' => 100,
            'name' => 'Owners',
            'can_make_games' => true,
            'can_view_wall' => true,
            'can_kick_users' => true,
            'can_manage_members' => true,
            'can_edit_space' => true,
            'can_moderate_content' => true,
            'can_manage_ranks' => true,
            'can_invite_users' => true,
        ]);


        SpaceRanks::create([
            'space_id' => $Space->id,
            'rank' => 75,
            'name' => 'Admin',
            'can_view_wall' => true,
            'can_kick_users' => true,
            'can_make_games' => false,
            'can_manage_members' => true,
            'can_edit_space' => true,
            'can_moderate_content' => true,
            'can_manage_ranks' => true,
            'can_invite_users' => true,
        ]);

        SpaceRanks::create([
            'space_id' => $Space->id,
            'rank' => true,
            'name' => 'Member',
            'can_make_games' => false,
            'can_view_wall' => true,
            'can_kick_users' => false,
            'can_manage_members' => false,
            'can_edit_space' => false,
            'can_moderate_content' => false,
            'can_manage_ranks' => false,
            'can_invite_users' => false,
        ]);

        SpaceMembers::create([
            'space_id' => $Space->id,
            'user_id' => Auth::id(),
            'rank' => $ownerRank->id,
        ]);


        return redirect()->route('spaces.view', $Space->id, $Space->slug())->with([
            'message' => 'Space created successfully!',
            'type' => 'success'
        ], 201);
    }

    private function uploadImage(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('uploads', $file, "{$name}.png", 'public');
        return $path;
    }

    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:30',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:png',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages([$validator->errors()]);
        }
    }
}
