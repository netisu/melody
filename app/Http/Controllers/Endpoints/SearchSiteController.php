<?php

namespace App\Http\Controllers\Endpoints;

use App\Models\Item;
use App\Models\User;
use App\Models\Space;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use MeiliSearch\Client;

class SearchSiteController extends Controller
{
    public function all(Request $request)
    {
        $search = $request->input('search');

        $results = collect();

        if ($search) {
            // Fetch Users
            $users = User::search($search)->get()->map(function ($user) {
                return [
                    'name' => $user->username,
                    'image' => $user->headshot() ?? null,
                    'url' => route('user.profile', $user->username),
                    'type' => 'user',
                    'icon' => 'fas fa-user-circle',
                    'description' => $user->about_me,
                ];
            });
            $results = $results->merge($users);

            // Fetch Items
            $items = Item::search($search)->get()->map(function ($item) {
                return [
                    'name' => $item->name,
                    'image' => $item->thumbnail() ?? null,
                    'url' => route('store.item', $item->id),
                    'type' => 'item',
                    'icon' => 'fas fa-box',
                    'description' => Str::limit($item->description ?? '', 100),
                ];
            });
            $results = $results->merge($items);

            // Fetch Spaces
            $spaces = Space::search($search)->get()->map(function ($space) {
                return [
                    'name' => $space->name,
                    'image' => $space->thumbnail() ?? null,
                    'url' => route('spaces.view', [$space->id, $space->slug()]),
                    'type' => 'space',
                    'icon' => 'fas fa-map-marked-alt',
                    'description' => Str::limit($space->description ?? '', 100), // limit space description
                ];
            });
            $results = $results->merge($spaces);

            // Fetch Forum Posts
            $forumPosts = ForumThread::search($search)->get()->map(function ($post) {
                return [
                    'name' => $post->title,
                    'image' => $post->creator->headshot() ?? null,
                    'url' => route('forum.post', [$post->id, $post->slug()]),
                    'type' => 'forum_post',
                    'icon' => 'fas fa-newspaper',
                    'description' => Str::limit($post->content ?? '', 100),
                ];
            });
            $results = $results->merge($forumPosts);


            // Fetch Routes (from Meilisearch directly)
            $meilisearchHost = config('scout.meilisearch.host');
            $meilisearchKey = config('scout.meilisearch.key');
            $client = new Client($meilisearchHost, $meilisearchKey);

            try {
                $routeSearchResults = $client->getIndex('routes')->search($search)->getHits();
                $routes = collect($routeSearchResults)->map(function ($routeResult) {
                    return [
                        'id' => $routeResult['id'],
                        'name' => $routeResult['name'],
                        'image' => null,
                        'url' => $routeResult['url'],
                        'type' => 'route',
                        'description' => $routeResult['description'] ?? null,
                        'icon' => $routeResult['icon'] ?? null,
                    ];
                });
                $results = $results->merge($routes);
            } catch (\MeiliSearch\Exceptions\ApiException $e) {
                \Log::error("Meilisearch API Error searching routes for full page: " . $e->getMessage() . " Code: " . $e->getCode());
            } catch (\Exception $e) {
                \Log::error("General error searching routes for full page: " . $e->getMessage());
            }

            // Sort results
            $results = $results->sortBy(function ($item) {
                $order = ['route' => 1, 'user' => 2, 'forum_post' => 3, 'item' => 4, 'space' => 5];
                return $order[$item['type']] ?? 99;
            })->values(); // Re-index the collection

            return response()->json($results);
        }
    }
}
