<?php
namespace App\Http\Controllers\Endpoints;

use App\Models\Item;
use App\Models\User;
use App\Models\Space;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        $results = $users->merge($items)->merge($spaces);

        if ($results->count() === 0 || !$search) {
            return response()->json([]);
        }

        $json['results'] = [];

        foreach ($results as $result) {
            switch ($result->getTable()) {
                case 'users':
                    $name = $result->username;
                    $image = $result->headshot();
                    $url = route('user.profile', $result->username);
                    break;

                case 'items':
                    $name = $result->name;
                    $image = $result->thumbnail();
                    $url = route('store.item', $result->id);
                    break;

                case 'spaces':
                    $name = $result->name;
                    $image = $result->thumbnail();
                    $url = route('spaces.view', [$result->id, $result->slug()]);
                    break;

                default:
                    continue 2;
            }

            $json['results'][] = [
                'name' => $name,
                'image' => $image,
                'url' => $url
            ];
        }

        return response()->json($json);
    }
}
