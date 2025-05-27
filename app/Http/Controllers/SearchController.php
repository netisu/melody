<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function showResults(Request $request)
    {
        $searchQuery = $request->input('search');

        return Inertia::render('App/Search/GlobalSearch', [
            'initialSearchQuery' => $searchQuery,
            'initialResults' => [], // Start with empty results, Axios will populate
        ]);
    }
}
