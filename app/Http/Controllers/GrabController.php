<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Space;
use App\Models\Followers;
use App\Models\Status;
use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Models\DiffUsername;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Endpoints\RenderController;
use App\Http\Middleware\SiteSettingChecker;
use App\Jobs\UserRenderer;
use App\Models\Item;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Services\BBCodeService;
use JBBCode\Parser;
use JBBCode\DefaultCodeDefinitionSet;
use PhpParser\Node\Stmt\Return_;
use App\Models\Inventory;
use App\Models\ItemReseller;
use App\Models\ItemPurchase;
use Illuminate\Support\Facades\Cache;
use DB;
use Inertia\Inertia;
use App\Models\Avatar;

class GrabController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function customizeIndex()
    {
        $availableColors = config('avatar_colors');
        $categories = config('SimpleCategories');

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $avatar = $user->avatar();
        if (!$avatar) {
            $avatar = Avatar::create([
                'user_id' => $user->id,
                'image' => 'default',
                'colors' => [
                    'head' => 'd3d3d3',
                    'torso' => '055e96',
                    'left_arm' => 'd3d3d3',
                    'right_arm' => 'd3d3d3',
                    'left_leg' => 'd3d3d3',
                    'right_leg' => 'd3d3d3',
                ],
            ]);
        }

        // Retrieve current body colors directly from the avatar model's JSON column
        $currentBodyColors = $avatar->colors ?? [
            'head' => 'd3d3d3',
            'torso' => '055e96',
            'left_arm' => 'd3d3d3',
            'right_arm' => 'd3d3d3',
            'left_leg' => 'd3d3d3',
            'right_leg' => 'd3d3d3',
        ];

        // Derive current_face_url directly from the 'face' JSON column for the initial prop
        $faceItemHash = 'none';
        $faceSlotData = $avatar->face;

        if (is_array($faceSlotData) && isset($faceSlotData['item_id']) && !is_null($faceSlotData['item_id'])) {
            $faceItem = Item::find($faceSlotData['item_id']);
            if ($faceItem) {
                $faceItemHash = $faceItem->hash;
            }
        }

        $currentFaceUrl = config('app.storage.url') . (
            $faceItemHash !== 'none'
            ? '/uploads/' . $faceItemHash . ".png"
            : '/assets/default.png'
        );

        return inertia('Customize/Index', [
            'avatar' => [
                'thumbnail' => $user->thumbnail(),
                'colors' => $currentBodyColors,
                'current_face_url' => $currentFaceUrl,
            ],
            'available_colors' => $availableColors,
            'categories' => $categories,
        ]);
    }


    public function regeneratewithID($id)
    {
        UserRenderer::dispatch($id)->onQueue('user-render');
    }

    public function regenerate()
    {
        UserRenderer::dispatch(Auth::id())->onQueue('user-render');
    }

    public function UpdateAvatar(Request $request)
    {
        \Log::info('UpdateAvatar request received for user ID: ' . Auth::id() . ', action: ' . $request->action . ', request data: ' . json_encode($request->all()));
        /** @var \App\Models\User $user **/
        $avatar = Auth::user()->avatar();

        switch ($request->action) {
            case 'reset':
                $avatar->resetAvatar();
                $this->regenerate($request);
                return Auth::user()->thumbnail();
            case 'color':
                $validBodyParts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
                $bodyPart = $request->body_part;
                $newColor = $request->color;

                if (!in_array($bodyPart, $validBodyParts)) {
                    return response()->json(['error' => 'Invalid body part.'], 400); // 400 Bad Request
                }

                $currentColors = $avatar->colors ?? [];


                Log::info('Current color of ' . $bodyPart . ': ' . ($currentColors[$bodyPart] ?? 'N/A'));
                Log::info('Requested color: ' . $newColor);

                // Check if the avatar's current color for the specified body part matches the requested color
                if (($currentColors[$bodyPart] ?? null) === $newColor) {
                    Log::info('Color already matches, skipping regeneration for user ID: ' . Auth::id());
                    return Auth::user()->thumbnail();
                }
                Log::info('Saving Color for:' . $avatar->user_id);

                $currentColors[$bodyPart] = $newColor;

                $avatar->colors = $currentColors;

                // Update the avatar's color for the specified body part
                $avatar->save();

                Log::info('About to call $this->regenerate() for user ID: ' . $avatar->user_id);
                $this->regeneratewithID($avatar->user_id);
                \Log::info('$this->regenerate() called for user ID: ' . $avatar->user_id);

                return Auth::user()->thumbnail();


            default:
                return response()->json(['error' => 'Invalid action.']);
        }
    }

    public function ForumIndex($id)
    {
        // Define the sections
        $sectionFilters = [
            1 => ['section_id' => 1],
            2 => ['section_id' => 2],
            3 => ['section_id' => 3],
        ];

        // Apply staff-only view filters if the user is a staff member
        $isStaff = Auth::check() && Auth::user()->isStaff() == 1;
        if ($isStaff) {
            $sectionFilters[1]['is_staff_only_viewing'] = false;
            $sectionFilters[2]['hidden'] = false;
            $sectionFilters[3]['is_staff_only_viewing'] = false;
        } else {
            $sectionFilters[1]['is_staff_only_viewing'] = true;
            $sectionFilters[2]['hidden'] = true;
            $sectionFilters[3]['is_staff_only_viewing'] = true;
        }

        // Use caching to store the forum topics for each section
        $sectionOneTopics = Cache::remember('section_one_topics', now()->addMinutes(30), function () use ($sectionFilters) {
            return ForumTopic::where($sectionFilters[1])->orderBy('id', 'asc')->get();
        });

        $sectionTwoTopics = Cache::remember('section_two_topics', now()->addMinutes(30), function () use ($sectionFilters) {
            return ForumTopic::where($sectionFilters[2])->orderBy('id', 'asc')->get();
        });

        $sectionThreeTopics = Cache::remember('section_three_topics', now()->addMinutes(30), function () use ($sectionFilters) {
            return ForumTopic::where($sectionFilters[3])->orderBy('id', 'asc')->get();
        });

        // Fetch the selected forum topic
        $topic = ForumTopic::where('id', '=', $id)->firstOrFail();

        // Check permissions and abort if necessary
        if (!$isStaff && $topic->is_staff_only_viewing) {
            abort(403);
        }

        // Use caching to store the posts related to the selected topic
        $posts = Cache::remember('topic_' . $id . '_posts', now()->addMinutes(15), function () use ($topic) {
            return $topic->threads()->through(function ($post) {
                return [
                    'id' => $post->id,
                    'topic_id' => $post->id,
                    'seo' => $post->slug(),
                    'name' => $post->title,
                    'username' => $post->creator->username,
                    'display_name' => $post->creator->display_name,
                    'message' => $post->body,
                    'pinned' => $post->is_pinned,
                    'locked' => $post->is_locked,
                    'deleted' => $post->is_deleted,
                    'DateHum' => $post->DateHum,
                ];
            });
        });

        return inertia('Forum/Index', [
            'topic' => $topic,
            'posts' => $posts,
            'section_one' => $sectionOneTopics,
            'section_two' => $sectionTwoTopics,
            'section_three' => $sectionThreeTopics
        ]);
    }

    public function ForumPost($id, $slug, BBCodeService $bbcodeService)
    {
        // Define a cache key for this forum post
        $cacheKey = 'forum_post_' . $id;

        // Attempt to retrieve the forum post from cache
        $post = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return ForumThread::findOrFail($id);
        });

        // Fetch the associated topic
        $topic = ForumTopic::findOrFail($post->in_topic_id);

        // Fetch replies based on staff status
        $replies = $post->replies();

        // Parse the post body using BBCodeService
        $post->body = $bbcodeService->parse($post->body);

        // Check conditions for displaying the post
        if ($slug != $post->slug() || $topic->is_staff_only_viewing || $post->is_deleted) {
            abort(404);
        }

        return inertia('Forum/Post', [
            'topic' => $topic,
            'post' => $post,
            'post.creator' => $post->creator,
            'replies' => $replies,
            'replies.creator' => $replies->creator,
        ]);
    }


    public function ForumCreate($id)
    {
        // Define a cache key for this forum create page
        $cacheKey = 'forum_create_' . $id;

        // Use caching to store the forum create page data
        $topic = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($id) {
            return ForumTopic::where('id', $id)->firstOrFail();
        });

        if (!Auth::check() || (!Auth::user()->isStaff() && $topic->is_staff_only_posting)) {
            abort(403);
        }

        return inertia('Forum/Create', [
            'topic' => $topic,
        ]);
    }

    public function ForumVal($id, Request $request)
    {
        $validatedData = $request->validate([
            'title' => ['required', 'max:100'],
            'body' => ['required', 'min:3', 'max:7500']
        ]);

        $title = $validatedData['title'];
        $body = $validatedData['body'];

        // Apply BBCode parsing
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new DefaultCodeDefinitionSet());
        $parsedBody = $parser->parse($body)->getAsHTML();


        $post = new ForumThread;
        $post->in_topic_id = $id;
        $post->creator_id = Auth::user()->id;
        $post->title = $title;
        $post->body = $parsedBody;
        $post->save();

        Auth::user()->addPoints(30);

        return Redirect::route('forum.post', $post->id, $post->slug())->with('message', 'Your post has been created.');
    }

    public function ForumReply($id, Request $request)
    {
        $validatedData = $request->validate([
            'body' => ['required', 'min:3', 'max:7500']
        ]);

        $body = $validatedData['body'];

        // Apply BBCode parsing
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new DefaultCodeDefinitionSet());
        $parsedBody = $parser->parse($body)->getAsHTML();


        $post = new ForumReply;
        $post->thread_id = $id;
        $post->creator_id = Auth::user()->id;
        $post->body = $parsedBody;
        $post->save();
        Auth::user()->addPoints(30);

        return redirect()->back()->with('message', 'Your post has been created.');
    }
    public function IndexingIndex()
    {
        //
    }
}
