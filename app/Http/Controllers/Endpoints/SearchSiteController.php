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

        if (Str::startsWith($search, '%')) {
            $search = '';
        }

        $users = User::search($search)->get();
        $items = Item::search($search)->get();
        $spaces = Space::search($search)->get();
        $forumPosts = ForumThread::search($search)->get();

        $results = $users->merge($items)->merge($spaces)->merge($forumPosts);

        $formattedResults = [];

        if ($search) {
            $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
            try {
                $routeSearchResults = $client->getIndex('routes')->search($search)->getHits();

                foreach ($routeSearchResults as $routeResult) {
                    $formattedResults[] = [
                        'name' => $routeResult['name'],
                        'image' => null,
                        'url' => $routeResult['url'],
                        'type' => 'route',
                        'description' => $routeResult['description'] ?? null,
                        'icon' => $routeResult['icon'] ?? null, // <-- Pass the icon class
                    ];
                }
            } catch (\MeiliSearch\Exceptions\ApiException $e) {
                \Log::error("Meilisearch API Error searching routes: " . $e->getMessage() . " Code: " . $e->getCode());
            } catch (\Exception $e) {
                \Log::error("General error searching routes: " . $e->getMessage());
            }
        }

        if ($results->count() === 0 && !$search && empty($formattedResults)) {
            return response()->json([]);
        }

        foreach ($results as $result) {
            switch ($result->getTable()) {
                case 'users':
                    $name = $result->username;
                    $image = $result->headshot();
                    $url = route('user.profile', $result->username);
                    $icon = 'fas fa-user-circle';
                    break;

                case 'items':
                    $name = $result->name;
                    $image = $result->thumbnail();
                    $url = route('store.item', $result->id);
                    $icon = 'fas fa-box';
                    break;

                case 'spaces':
                    $name = $result->name;
                    $image = $result->thumbnail();
                    $url = route('spaces.view', [$result->id, $result->slug()]);
                    $icon = 'fas fa-map-marked-alt';
                    break;

                case 'forum_posts':
                    $name = $result->title;
                    $image = $result->creator->headshot();
                    $url = route('forum.post', [$result->id, $result->slug()]);
                    $icon = 'fas fa-newspaper'; // Default icon for forum posts

                    break;

                default:
                    continue 2;
            }

            $formattedResults[] = [
                'name' => $name,
                'image' => $image,
                'url' => $url,
                'type' => $result->getTable(),
                'icon' => $icon,
            ];
        }

        usort($formattedResults, function ($a, $b) {
            $order = ['route' => 1, 'users' => 2, 'forum_posts' => 3, 'items' => 4, 'spaces' => 5];
            return ($order[$a['type']] ?? 99) <=> ($order[$b['type']] ?? 99);
        });

        return response()->json($formattedResults);
    }
}
