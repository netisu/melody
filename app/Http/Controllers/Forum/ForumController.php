<?php

namespace App\Http\Controllers\Forum;

use JBBCode\Parser;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Item;
use App\Helpers\Event;

use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Services\MarkdownService;
use App\Services\BBCodeService;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use JBBCode\DefaultCodeDefinitionSet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Mews\Purifier\Facades\Purifier;

class ForumController extends Controller
{

    public function myPosts()
    {
        $userPosts = ForumThread::with('creator')
            ->where('creator_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $posts = Cache::remember('topic_' . Auth::id() . '_posts', now()->addMinutes(15), function () use ($userPosts) {
            return $userPosts->through(function ($post) {
                return [
                    'id' => $post->id,
                    'topic_id' => $post->id,
                    'seo' => $post->slug(),
                    'name' => $post->title,
                    'username' => $post->creator->username,
                    'headshot' => $post->creator->headshot(),
                    'display_name' => $post->creator->display_name,
                    'message' => $post->body,
                    'pinned' => $post->is_pinned,
                    'locked' => $post->is_locked,
                    'deleted' => $post->is_deleted,
                    'DateHum' => $post->DateHum,
                ];
            });
        });

        return inertia('Forum/MyPosts', ['posts' => $posts]);
    }

    public function ForumIndex($id)
    {
        // Define the sections
        $sectionCorresponds = [
            1 => ['section_id' => 1],
            2 => ['section_id' => 2],
            3 => ['section_id' => 3],
        ];

        // Use caching to store the forum topics for each section
        $sectionOneTopics = Cache::remember('section_one_topics', now()->addMinutes(30), function () use ($sectionCorresponds) {
            return ForumTopic::where([
                [$sectionCorresponds[1]],
                ['hidden', false],
                ['is_staff_only_viewing', false]
            ])
                ->orderBy('id', 'asc')
                ->get();
        });

        $sectionTwoTopics = Cache::remember('section_two_topics', now()->addMinutes(30), function () use ($sectionCorresponds) {
            return ForumTopic::where([
                [$sectionCorresponds[2]],
                ['hidden', false],
                ['is_staff_only_viewing', false]
            ])
                ->orderBy('id', 'asc')
                ->get();
        });

        $sectionThreeTopics = Cache::remember('section_three_topics', now()->addMinutes(30), function () use ($sectionCorresponds) {
            return ForumTopic::where([
                [$sectionCorresponds[3]],
                ['hidden', false],
                ['is_staff_only_viewing', false]
            ])
                ->orderBy('id', 'asc')
                ->get();
        });

        // Fetch the selected forum topic
        $topic = ForumTopic::where('id', '=', $id)->first();

        if (!$topic) {
            abort(404);
        }

        if ($topic->is_staff_only_viewing) {
            if (Auth::check()) {
                if (!Auth::user()->isStaff()) {
                    abort(403);
                }
            } else {
                abort(404);
            }
        }

        // Use caching to store the posts related to the selected topic
        $cacheKey = 'topic_' . $id . '_posts';
        $currentPage = request()->get('page', 1);

        $posts = Cache::remember($cacheKey . '-' . $currentPage, now()->addMinutes(5),  function () use ($topic) {
            return $topic->threads()->through(function ($post) {
                return [
                    'id' => $post->id,
                    'topic_id' => $post->id,
                    'seo' => $post->slug(),
                    'name' => $post->title,
                    'creator' => [
                        'username' => $post->creator->username,
                        'headshot' => $post->creator->headshot(),
                        'display_name' => $post->creator->display_name,
                    ],
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

    public function ForumPost($id, $slug, BBCodeService $bbcodeService, MarkdownService $MarkdownService)
    {
        // Find the forum post
        $post = Cache::remember('forum_post_' . $id, now()->addMinutes(5), fn() => ForumThread::find($id));

        // Handle missing post
        if (!$post->exists()) {
            abort(404);
        }

        if ($slug != $post->slug() || $post->is_staff_only_viewing || $post->is_deleted) {
            abort(404);
        }
        $cacheKey = 'forum_post_' . $id . '_replies';
        $currentPage = request()->get('page', 1);

        // Parse the post body
        $post->body = $MarkdownService->parse($post->body);
        $post->body = $bbcodeService->parse($post->body);
        $post->body = Purifier::clean($post->body);


        $replies = Cache::remember($cacheKey . '-' . $currentPage, now()->addMinutes(3), function () use ($post, $bbcodeService) {
            return $post->replies()->paginate(10)->through(function ($reply) use ($bbcodeService) {
                return [
                    'body' => $bbcodeService->parse(Purifier::clean($reply->body)),
                    'is_deleted' => $reply->is_deleted,
                    'DateHum' => $reply->created_at->diffForHumans(),
                    'creator' => [
                        'username' => $reply->creator->username,
                        'staff' => $reply->creator->isStaff(),
                        'position' => $reply->creator->CurrentPosition(),
                        'display_name' => $reply->creator->display_name,
                        'thumbnail' => $reply->creator->thumbnail(),
                        'settings' => [
                            'beta_tester' => $reply->creator->settings->beta_tester,
                        ],
                    ],
                ];
            });
        });

        return inertia('Forum/Post', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'is_pinned' => $post->is_pinned,
                'is_locked' => $post->is_locked,
                'is_deleted' => $post->is_deleted,
                'DateHum' => $post->created_at->diffForHumans(),

                'creator' => [
                    'username' => $post->creator->username,
                    'staff' => $post->creator->isStaff(),
                    'position' => $post->creator->CurrentPosition(),
                    'display_name' => $post->creator->display_name,
                    'headshot' => $post->creator->headshot(),
                    'thumbnail' => $post->creator->thumbnail(),

                    'settings' => [
                        'beta_tester' => $post->creator->settings->beta_tester,
                    ],
                ],
            ],
            'replies' => $replies
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

        if (!Auth::check() || (!Auth::user()->isStaff() && Auth::user()->CurrentPositionID() != $topic->role_required_to_post)) {
            abort(403);
        }

        return inertia('Forum/Create', [
            'topic' => $topic,
        ]);
    }

    public function ForumVal($id, Request $request)
    {
        $validatedData = $request->validate([
            'title' => ['required', 'max:100', 'profane:es,en'],
            'body' => ['required', 'min:3', 'max:7500', 'profane:es,en']
        ]);

        $title = $validatedData['title'];
        $body = $validatedData['body'];

        $cacheKey = 'forum_lockout_' . $id;
        $topic = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($id) {
            return ForumTopic::where('id', $id)->firstOrFail();
        });

        if (!Auth::user()->isStaff() && Auth::user()->CurrentPositionID() != $topic->role_required_to_post) {
            return response()->json([
                'message' => 'You don\'t have permission to post in ' . $topic->name,
                'type' => 'danger',
            ]);
        }

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

        Auth::user()->addPoints(10);

        return redirect()->to(route('forum.post', [$post->id, $post->slug()]))
            ->with('message', 'Your post has been created.');
    }

    public function ForumReply(int $id, Request $request)
    {
        $validatedData = $request->validate([
            'body' => ['required', 'min:3', 'max:7500']
        ]);

        $body = $validatedData['body'];

        // Apply BBCode parsing
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new DefaultCodeDefinitionSet());
        $parsedBody = $parser->parse($body)->getAsHTML();

        $thread = ForumThread::where('id', $id)->first();

        $post = ForumReply::create([
            'thread_id' => $thread->id,
            'creator_id' => Auth::user()->id,
            'body' => $parsedBody,
        ]);

        $post->save();



        Auth::user()->addPoints(5);

        $cacheKey = 'forum_post_' . $thread->id . '_replies';
        $currentPage = request()->get('page', 1);

        Cache::forget($cacheKey . '-' . $currentPage);

        if ($thread->id == 212 && config('Values.in_event') == true && !Auth::user()->ownsItem(212)) {
            $eventItem = Item::where('id', 212)->first();
            $event = new Event;
            $event->grantItem($eventItem, Auth::user(), 'invertedeggy212', false);
            return redirect()->to(route('store.item', 212));
        };

        return redirect()->to(route('forum.post', [$thread->id, $thread->slug()]))
        ->with('message', 'Your reply has been created.');
    }
}
